<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Repayment;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\GlTransaction;
use App\Models\ChartAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LoanRepaymentService
{
    /**
     * Process loan repayment with different calculation methods
     */
    public function processRepayment($loanId, $amount, $paymentData, $calculationMethod = 'flat_rate')
    {
        DB::beginTransaction();

        try {
            $loan = Loan::with(['product', 'customer', 'schedule'])->findOrFail($loanId);
            $remainingAmount = $amount;
            $processedRepayments = [];
            $totalPaidAmount = 0;

            // Get unpaid schedules ordered by due date
            $unpaidSchedules = $this->getUnpaidSchedules($loan);

            if ($unpaidSchedules->count() === 0) {
                throw new \Exception('No unpaid schedules found for this loan.');
            }

            // Process each schedule until amount is exhausted
            foreach ($unpaidSchedules as $schedule) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $schedulePayment = $this->processSchedulePayment(
                    $loan,
                    $schedule,
                    $remainingAmount,
                    $paymentData
                );

                if ($schedulePayment['amount'] > 0) {
                    $remainingAmount -= $schedulePayment['amount'];
                    $totalPaidAmount += $schedulePayment['amount'];

                    // Create repayment record
                    $repayment = $this->createRepaymentRecord($loan, $schedule, $schedulePayment, $paymentData);

                    // Create receipt and GL transactions
                    $this->createReceiptAndGL($loan, $repayment, $schedulePayment, $paymentData);

                    $processedRepayments[] = $schedulePayment;
                }
            }

            // Check if loan is fully paid
            if ($this->isLoanFullyPaid($loan)) {
                $loan->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'paid_amount' => $totalPaidAmount,
                'balance' => $remainingAmount,
                'processed_repayments' => $processedRepayments,
                'loan_status' => $loan->status
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Loan repayment processing failed: ' . $e->getMessage(), [
                'loan_id' => $loanId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get unpaid schedules for a loan
     */
    private function getUnpaidSchedules($loan)
    {
        return $loan->schedule()
            ->whereRaw('(
                SELECT COALESCE(SUM(principal), 0) + COALESCE(SUM(interest), 0) + COALESCE(SUM(fee_amount), 0) + COALESCE(SUM(penalt_amount), 0)
                FROM repayments
                WHERE repayments.loan_schedule_id = loan_schedules.id
            ) < (loan_schedules.principal + loan_schedules.interest + loan_schedules.fee_amount + loan_schedules.penalty_amount)')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Process payment for a single schedule
     */
    private function processSchedulePayment($loan, $schedule, $remainingAmount, $paymentData)
    {
        // Get already paid amounts for this schedule
        $paidAmounts = $this->getPaidAmountsForSchedule($schedule);

        // Calculate remaining amounts
        $remainingAmounts = [
            'principal' => $schedule->principal - $paidAmounts['principal'],
            'interest' => $schedule->interest - $paidAmounts['interest'],
            'fee_amount' => $schedule->fee_amount - $paidAmounts['fee_amount'],
            'penalty_amount' => $schedule->penalty_amount - $paidAmounts['penalty_amount']
        ];

        // Get repayment order from loan product
        $repaymentOrder = $this->getRepaymentOrder($loan);

        $allocatedAmounts = [
            'principal' => 0,
            'interest' => 0,
            'fee_amount' => 0,
            'penalty_amount' => 0
        ];

        $currentAmount = $remainingAmount;

        // Allocate payment according to repayment order
        foreach ($repaymentOrder as $component) {
            if ($currentAmount <= 0)
                break;

            if (isset($remainingAmounts[$component]) && $remainingAmounts[$component] > 0) {
                $amountToPay = min($currentAmount, $remainingAmounts[$component]);
                $allocatedAmounts[$component] = $amountToPay;
                $currentAmount -= $amountToPay;
            }
        }

        return [
            'schedule_id' => $schedule->id,
            'amount' => $remainingAmount - $currentAmount,
            'principal' => $allocatedAmounts['principal'],
            'interest' => $allocatedAmounts['interest'],
            'fee_amount' => $allocatedAmounts['fee_amount'],
            'penalty_amount' => $allocatedAmounts['penalty_amount']
        ];
    }

    /**
     * Get repayment order from loan product
     */
    private function getRepaymentOrder($loan)
    {
        // Default order if not configured
        $defaultOrder = ['penalty_amount', 'fee_amount', 'interest', 'principal'];

        if ($loan->product && $loan->product->repayment_order) {
            // Parse the comma-separated string from the database
            $repaymentComponents = explode(',', $loan->product->repayment_order);
            $validComponents = [];

            // Map the components to the correct field names
            foreach ($repaymentComponents as $component) {
                $component = trim($component);
                switch ($component) {
                    case 'penalties':
                        $validComponents[] = 'penalty_amount';
                        break;
                    case 'fees':
                        $validComponents[] = 'fee_amount';
                        break;
                    case 'interest':
                        $validComponents[] = 'interest';
                        break;
                    case 'principal':
                        $validComponents[] = 'principal';
                        break;
                }
            }

            return !empty($validComponents) ? $validComponents : $defaultOrder;
        }

        return $defaultOrder;
    }

    /**
     * Get paid amounts for a schedule
     */
    private function getPaidAmountsForSchedule($schedule)
    {
        $repayments = $schedule->repayments;

        return [
            'principal' => $repayments->sum('principal'),
            'interest' => $repayments->sum('interest'),
            'fee_amount' => $repayments->sum('fee_amount'),
            'penalty_amount' => $repayments->sum('penalt_amount')
        ];
    }

    /**
     * Create repayment record
     */
    private function createRepaymentRecord($loan, $schedule, $schedulePayment, $paymentData)
    {
        return Repayment::create([
            'customer_id' => $loan->customer_id,
            'loan_id' => $loan->id,
            'loan_schedule_id' => $schedule->id,
            'bank_account_id' => $paymentData['bank_account_id'] ?? $loan->bank_account_id,
            'payment_date' => $paymentData['payment_date'] ?? now(),
            'due_date' => $schedule->due_date,
            'principal' => $schedulePayment['principal'],
            'interest' => $schedulePayment['interest'],
            'fee_amount' => $schedulePayment['fee_amount'],
            'penalt_amount' => $schedulePayment['penalty_amount'],
            'cash_deposit' => $schedulePayment['amount'],
        ]);
    }

    /**
     * Create receipt and GL transactions
     */
    private function createReceiptAndGL($loan, $repayment, $schedulePayment, $paymentData)
    {
        // Create receipt
        $receipt = Receipt::create([
            'reference' => 'LOAN-REPAY-' . $loan->id . '-' . time(),
            'reference_type' => 'loan_repayment',
            'reference_number' => $repayment->id,
            'amount' => $schedulePayment['amount'],
            'date' => $paymentData['payment_date'] ?? now(),
            'description' => "Loan repayment for {$loan->customer->name} - Loan #{$loan->id}",
            'user_id' => auth()->id(),
            'bank_account_id' => $paymentData['bank_account_id'] ?? $loan->bank_account_id,
            'payee_type' => 'customer',
            'payee_id' => $loan->customer_id,
            'payee_name' => $loan->customer->name,
            'branch_id' => auth()->user()->branch_id ?? 1,
            'approved' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Create receipt items and GL transactions
        $this->createReceiptItemsAndGL($receipt, $loan, $schedulePayment, $repayment);
    }

    /**
     * Create receipt items and corresponding GL transactions
     */
    private function createReceiptItemsAndGL($receipt, $loan, $schedulePayment, $repayment)
    {
        // Get chart accounts from loan product or use defaults
        $chartAccounts = $this->getChartAccounts($loan);

        // Create receipt items and GL transactions for each component
        $components = [
            'principal' => $schedulePayment['principal'],
            'interest' => $schedulePayment['interest'],
            'fee_amount' => $schedulePayment['fee_amount'],
            'penalty_amount' => $schedulePayment['penalty_amount']
        ];

        foreach ($components as $component => $amount) {
            if ($amount > 0) {
                $chartAccountId = $chartAccounts[$component] ?? null;

                if ($chartAccountId) {
                    // Create receipt item
                    ReceiptItem::create([
                        'receipt_id' => $receipt->id,
                        'chart_account_id' => $chartAccountId,
                        'amount' => $amount,
                        'description' => ucfirst($component) . " payment for loan #{$loan->id}"
                    ]);

                    // Create GL transaction
                    GlTransaction::create([
                        'chart_account_id' => $chartAccountId,
                        'customer_id' => $loan->customer_id,
                        'amount' => $amount,
                        'nature' => 'credit',
                        'transaction_id' => $receipt->id,
                        'transaction_type' => 'receipt',
                        'date' => $receipt->date,
                        'description' => "Loan {$component} payment - {$loan->customer->name}",
                        'branch_id' => $receipt->branch_id,
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        }

        // Create GL transaction for cash/bank account (debit)
        GlTransaction::create([
            'chart_account_id' => $receipt->bank_account_id,
            'customer_id' => $loan->customer_id,
            'amount' => $schedulePayment['amount'],
            'nature' => 'debit',
            'transaction_id' => $receipt->id,
            'transaction_type' => 'receipt',
            'date' => $receipt->date,
            'description' => "Loan repayment received - {$loan->customer->name}",
            'branch_id' => $receipt->branch_id,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Get chart accounts for loan components
     */
    private function getChartAccounts($loan)
    {
        // Use chart accounts from loan product
        $chartAccounts = [];

        if ($loan->product) {
            $chartAccounts = [
                'principal' => $loan->product->principal_receivable_account_id,
                'interest' => $loan->product->interest_receivable_account_id,
                'fee_amount' => $loan->product->fee->chart_account_id, // Use interest account for fees
                'penalty_amount' => $loan->product->penalty->penalty_receivables_account_id // Use interest account for penalties
            ];
        }

        return $chartAccounts;
    }

    /**
     * Check if loan is fully paid
     */
    private function isLoanFullyPaid($loan)
    {
        $totalDue = $loan->amount_total;
        $totalPaid = $loan->repayments()->sum(DB::raw('principal + interest + fee_amount + penalt_amount'));

        return $totalPaid >= $totalDue;
    }

    /**
     * Remove penalty from schedule (for pardon functionality)
     */
    public function removePenalty($scheduleId, $reason = null)
    {
        DB::beginTransaction();

        try {
            $schedule = LoanSchedule::findOrFail($scheduleId);

            // Update schedule to remove penalty
            $schedule->update([
                'penalty_amount' => 0,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Penalty removed successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate loan schedule using different methods
     */
    public function calculateSchedule($loan, $method = 'flat_rate')
    {
        switch ($method) {
            case 'flat_rate':
                return $this->calculateFlatRateSchedule($loan);
            case 'reducing_equal_installment':
                return $this->calculateReducingEqualInstallmentSchedule($loan);
            case 'reducing_equal_principal':
                return $this->calculateReducingEqualPrincipalSchedule($loan);
            default:
                throw new \Exception('Invalid calculation method');
        }
    }

    /**
     * Calculate flat rate schedule
     */
    private function calculateFlatRateSchedule($loan)
    {
        $principal = $loan->amount;
        $interestRate = $loan->interest / 100;
        $period = $loan->period;

        // Flat rate calculation
        $totalInterest = $principal * $interestRate * $period;
        $totalAmount = $principal + $totalInterest;
        $monthlyInstallment = $totalAmount / $period;
        $monthlyInterest = $totalInterest / $period;
        $monthlyPrincipal = $principal / $period;

        $schedules = [];
        $currentDate = Carbon::parse($loan->disbursed_on)->addMonth();

        for ($i = 1; $i <= $period; $i++) {
            $schedules[] = [
                'installment_no' => $i,
                'due_date' => $currentDate->format('Y-m-d'),
                'principal' => $monthlyPrincipal,
                'interest' => $monthlyInterest,
                'fee_amount' => 0,
                'penalty_amount' => 0,
                'total_installment' => $monthlyInstallment
            ];

            $currentDate->addMonth();
        }

        return $schedules;
    }

    /**
     * Calculate reducing balance with equal installments
     */
    private function calculateReducingEqualInstallmentSchedule($loan)
    {
        $principal = $loan->amount;
        $interestRate = $loan->interest / 100 / 12; // Monthly rate
        $period = $loan->period;

        // Calculate equal monthly installment
        $monthlyInstallment = $principal * ($interestRate * pow(1 + $interestRate, $period)) / (pow(1 + $interestRate, $period) - 1);

        $schedules = [];
        $currentDate = Carbon::parse($loan->disbursed_on)->addMonth();
        $remainingPrincipal = $principal;

        for ($i = 1; $i <= $period; $i++) {
            $monthlyInterest = $remainingPrincipal * $interestRate;
            $monthlyPrincipal = $monthlyInstallment - $monthlyInterest;

            // Adjust for last payment
            if ($i == $period) {
                $monthlyPrincipal = $remainingPrincipal;
                $monthlyInstallment = $monthlyPrincipal + $monthlyInterest;
            }

            $schedules[] = [
                'installment_no' => $i,
                'due_date' => $currentDate->format('Y-m-d'),
                'principal' => $monthlyPrincipal,
                'interest' => $monthlyInterest,
                'fee_amount' => 0,
                'penalty_amount' => 0,
                'total_installment' => $monthlyInstallment
            ];

            $remainingPrincipal -= $monthlyPrincipal;
            $currentDate->addMonth();
        }

        return $schedules;
    }

    /**
     * Calculate reducing balance with equal principal
     */
    private function calculateReducingEqualPrincipalSchedule($loan)
    {
        $principal = $loan->amount;
        $interestRate = $loan->interest / 100 / 12; // Monthly rate
        $period = $loan->period;

        $monthlyPrincipal = $principal / $period;

        $schedules = [];
        $currentDate = Carbon::parse($loan->disbursed_on)->addMonth();
        $remainingPrincipal = $principal;

        for ($i = 1; $i <= $period; $i++) {
            $monthlyInterest = $remainingPrincipal * $interestRate;
            $monthlyInstallment = $monthlyPrincipal + $monthlyInterest;

            $schedules[] = [
                'installment_no' => $i,
                'due_date' => $currentDate->format('Y-m-d'),
                'principal' => $monthlyPrincipal,
                'interest' => $monthlyInterest,
                'fee_amount' => 0,
                'penalty_amount' => 0,
                'total_installment' => $monthlyInstallment
            ];

            $remainingPrincipal -= $monthlyPrincipal;
            $currentDate->addMonth();
        }

        return $schedules;
    }
}
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
        $loan = Loan::with(['product', 'customer', 'schedule'])->findOrFail($loanId);
        $remainingAmount = $amount;
        $processedRepayments = [];
        $totalPaidAmount = 0;

        // Get unpaid schedules ordered by due date
        $unpaidSchedules = $this->getUnpaidSchedules($loan);
        Log::info('Unpaid schedules loaded', ['count' => $unpaidSchedules->count()]);

        if ($unpaidSchedules->count() === 0) {
            throw new \Exception('No unpaid schedules found for this loan.');
        }

        foreach ($unpaidSchedules as $schedule) {
            if ($remainingAmount <= 0) {
                Log::info('No remaining amount, breaking loop', ['loanId' => $loanId]);
                break;
            }

            $schedulePayment = $this->processSchedulePayment($loan, $schedule, $remainingAmount, $paymentData);

            if (empty($schedulePayment) || !isset($schedulePayment['amount']) || $schedulePayment['amount'] <= 0) {
                Log::warning('No payment allocated for schedule, breaking loop', ['schedule_id' => $schedule->id]);
                break;
            }

            $remainingAmount -= $schedulePayment['amount'];
            $totalPaidAmount += $schedulePayment['amount'];

            // Create repayment record
            $repayment = $this->createRepaymentRecord($loan, $schedule, $schedulePayment, $paymentData);
            if (!$repayment) {
                Log::error('Failed to create repayment', ['loanId' => $loanId, 'schedule_id' => $schedule->id]);
                throw new \Exception('Repayment not saved');
            }

            // Bank/cash or cash deposit logic
            if (isset($paymentData['bank_account_id']) && $paymentData['bank_account_id']) {
                Log::info('Processing bank/cash repayment', ['bank_account_id' => $paymentData['bank_account_id']]);
                $this->createReceiptAndGL($loan, $repayment, $schedulePayment, $paymentData);
            } elseif (isset($paymentData['cash_deposit_id']) && $paymentData['cash_deposit_id']) {
                Log::info('Processing cash deposit repayment', ['cash_deposit_id' => $paymentData['cash_deposit_id']]);
                $this->createJournalEntry($loan, $repayment, $schedulePayment, $paymentData);
            } else {
                Log::warning('No payment method provided', ['loanId' => $loanId]);
            }

            $processedRepayments[] = $schedulePayment;
        }

        // Check if loan is fully paid
        if ($this->isLoanFullyPaid($loan)) {
            $loan->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            Log::info('Loan marked as completed', ['loanId' => $loanId]);
        }

        Log::info('Repayment transaction committed', ['loanId' => $loanId]);
        DB::commit();

        return [
            'success' => true,
            'paid_amount' => $totalPaidAmount,
            'balance' => $remainingAmount,
            'processed_repayments' => $processedRepayments,
            'loan_status' => $loan->status
        ];
    }
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
        // Check if payment is made before or on due date and remove penalties if applicable
        $this->checkAndRemovePenaltyForOnTimePayment($schedule, $paymentData);

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
        $repaymentData = [
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
        ];

        Log::info('Creating repayment record', $repaymentData);

        try {
            $repayment = Repayment::create($repaymentData);
            Log::info('Repayment created successfully', ['id' => $repayment->id]);
            return $repayment;
        } catch (\Exception $e) {
            Log::error('Failed to create repayment record: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create receipt and GL transactions
     */
    private function createReceiptAndGL($loan, $repayment, $schedulePayment, $paymentData)
    {

        // check if the interest  receivable has been posted first, if not, do not create the interest receivable
        // credit interest income and debit interest receivable,
        // Log after receipt is created
        // Log::info('Starting createReceiptAndGL', [
        //     'loan_id' => $loan->id,
        //     'repayment_id' => $repayment->id,
        //     'schedulePayment' => $schedulePayment,
        //     'bank_account_id' => $receipt->bank_account_id,
        //     'receipt_id' => $receipt->id
        // ]);
        // Only create receipt if payment source is not cash deposit
        if (isset($paymentData['payment_source']) && $paymentData['payment_source'] === 'cash_deposit') {
            $this->createJournalEntry($loan, $repayment, $schedulePayment, $paymentData);
            return;
        }

        // Create receipt for bank payment
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
        // Logging is only allowed inside function blocks after variable definitions
        Log::info('Starting createReceiptAndGL', [
            'loan_id' => $loan->id,
            'repayment_id' => $repayment->id,
            'schedulePayment' => $schedulePayment,
            'bank_account_id' => $receipt->bank_account_id,
            'receipt_id' => $receipt->id
        ]);

        // Get chart accounts for components and log them
        $chartAccounts = [
            'principal' => $loan->product->principal_receivable_account_id ?? null,
            'interest' => $loan->product->interest_income_account_id ?? null,
            'fee_amount' => $loan->product->fee_income_account_id ?? null,
            'penalty_amount' => $loan->product->penalty_receivables_account_id ?? null
        ];
        Log::info('GL Chart Accounts for Receipt', $chartAccounts);

        $components = [
            'principal' => $schedulePayment['principal'],
            'interest' => $schedulePayment['interest'],
            'fee_amount' => $schedulePayment['fee_amount'],
            'penalty_amount' => $schedulePayment['penalty_amount']
        ];
        Log::info('GL Component Amounts for Receipt', $components);

        // Debit: Bank/cash account (total amount)
        Log::info('GL Debit Posting', [
            'chart_account_id' => $receipt->bank_account_id,
            'amount' => $schedulePayment['amount'],
            'customer_id' => $loan->customer_id,
            'receipt_id' => $receipt->id
        ]);
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

        // check if the interest receivable has been posted first, if not, do not create the interest receivable by debiting  and credit interest income
        $receivableId = $loan->product->interest_receivable_account_id;
        $incomeId = $loan->product->interest_revenue_account_id;

        if (!$receivableId) {
            Log::warning("Missing interest accounts for product {$loan->product->id}");
            return 0;
        }

        $exists = GlTransaction::where('chart_account_id', $receivableId)
            ->where('customer_id', $loan->customer_id)
            ->where('date', $repayment->due_date)
            ->where('amount', $schedulePayment['interest'])
            ->where('transaction_type', 'Mature Interest')
            ->exists();
        if (!$incomeId) {
            Log::warning("Missing interest income account for product {$loan->product->id}");
            return 0;
        }

        $incomeExists = GlTransaction::where('chart_account_id', $incomeId)
            ->where('customer_id', $loan->customer_id)
            ->where('date', $repayment->due_date)
            ->where('amount', $schedulePayment['interest'])
            ->where('transaction_type', 'Interest')
            ->exists();

        if ($exists && $incomeExists) {
            Log::info('Interest receivable and interest income have been posted ovewtite the array chartAccont interest to be receivable instead of icome');
            $chartAccounts['interest'] = $receivableId;
          
        }

        // Credit: Each component to its respective account
        foreach ($components as $component => $amount) {
            $accountId = $chartAccounts[$component] ?? null;
            if ($amount > 0 && $accountId) {
                Log::info('GL Credit Posting', [
                    'component' => $component,
                    'chart_account_id' => $accountId,
                    'amount' => $amount,
                    'customer_id' => $loan->customer_id,
                    'receipt_id' => $receipt->id
                ]);
                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'chart_account_id' => $accountId,
                    'amount' => $amount,
                    'description' => ucfirst($component) . " payment for loan #{$loan->id}"
                ]);
                GlTransaction::create([
                    'chart_account_id' => $accountId,
                    'customer_id' => $loan->customer_id,
                    'amount' => $amount,
                    'nature' => 'credit',
                    'transaction_id' => $receipt->id,
                    'transaction_type' => 'receipt',
                    'date' => $receipt->date,
                    'description' => ucfirst($component) . " payment for loan #{$loan->id}",
                    'branch_id' => $receipt->branch_id,
                    'user_id' => auth()->id(),
                ]);
            } else if ($amount > 0 && !$accountId) {
                Log::error('Missing chart account for GL component', [
                    'component' => $component,
                    'amount' => $amount,
                    'loan_id' => $loan->id,
                    'receipt_id' => $receipt->id
                ]);
            }
        }
    }

    /**
     * Create journal entry for cash deposit payments
     */
    private function createJournalEntry($loan, $repayment, $schedulePayment, $paymentData)
    {
        Log::info('createJournalEntry called', [
            'loan_id' => $loan->id,
            'repayment_id' => $repayment->id ?? null,
            'schedulePayment' => $schedulePayment,
            'cash_deposit_id' => $paymentData['cash_deposit_id'] ?? null,
            'cash_deposit_before' => $cashDeposit->amount,
        ]);
        // Get cash deposit account
        $cashDeposit = \App\Models\CashCollateral::findOrFail($paymentData['cash_deposit_id']);
        // Reduce cash deposit balance
        $cashDeposit->decrement('amount', $schedulePayment['amount']);
        Log::info('Cash collateral decremented', [
            'cash_deposit_id' => $cashDeposit->id,
            'cash_deposit_after' => $cashDeposit->amount,
        ]);

        // Create journal record for withdrawal from cash deposit
        $journal = \App\Models\Journal::create([
            'reference' => $repayment->id,
            'reference_type' => 'Withdrawal',
            'customer_id' => $loan->customer_id,
            'description' => "Loan repayment from cash deposit for {$loan->customer->name} - Loan #{$loan->id}",
            'branch_id' => auth()->user()->branch_id ?? 1,
            'user_id' => auth()->id(),
            'date' => $paymentData['payment_date'] ?? now(),
        ]);
        Log::info('Journal created', ['journal_id' => $journal->id]);

        // Debit: Cash collateral account (total amount)
        \App\Models\JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $cashDeposit->type->chart_account_id ?? 1,
            'amount' => $schedulePayment['amount'],
            'description' => "Loan repayment from cash deposit",
            'nature' => 'debit',
        ]);
        Log::info('JournalItem debit created', ['journal_id' => $journal->id, 'amount' => $schedulePayment['amount']]);

        // Always credit all components, not only principal
        $chartAccounts = [
            'principal' => $loan->product->principal_receivable_account_id ?? null,
            'interest' => $loan->product->interest_income_account_id ?? null,
            'fee_amount' => $loan->product->fee_income_account_id ?? null,
            'penalty_amount' => $loan->product->penalty_receivables_account_id ?? null
        ];

        $components = [
            'principal' => $schedulePayment['principal'],
            'interest' => $schedulePayment['interest'],
            'fee_amount' => $schedulePayment['fee_amount'],
            'penalty_amount' => $schedulePayment['penalty_amount']
        ];

        foreach ($components as $component => $amount) {
            if ($amount > 0 && !empty($chartAccounts[$component])) {
                \App\Models\JournalItem::create([
                    'journal_id' => $journal->id,
                    'chart_account_id' => $chartAccounts[$component],
                    'amount' => $amount,
                    'description' => ucfirst($component) . " repayment for loan #{$loan->id}",
                    'nature' => 'credit',
                ]);
                \App\Models\GlTransaction::create([
                    'chart_account_id' => $chartAccounts[$component],
                    'customer_id' => $loan->customer_id,
                    'amount' => $amount,
                    'nature' => 'credit',
                    'transaction_id' => $journal->id,
                    'transaction_type' => 'journal repayment',
                    'date' => $journal->date,
                    'description' => ucfirst($component) . " repayment from cash deposit - Loan #{$loan->id}",
                    'branch_id' => $journal->branch_id,
                    'user_id' => $journal->user_id,
                ]);
            }
        }

        // Debit: Cash collateral account (total amount)
        \App\Models\JournalItem::create([
            'journal_id' => $journal->id,
            'chart_account_id' => $cashDeposit->type->chart_account_id ?? 1,
            'amount' => $schedulePayment['amount'],
            'description' => "Loan repayment from cash deposit",
            'nature' => 'debit',
        ]);
        \App\Models\GlTransaction::create([
            'chart_account_id' => $cashDeposit->type->chart_account_id ?? 1,
            'customer_id' => $loan->customer_id,
            'amount' => $schedulePayment['amount'],
            'nature' => 'debit',
            'transaction_id' => $journal->id,
            'transaction_type' => 'journal repayment',
            'date' => $journal->date,
            'description' => "Loan repayment from cash deposit - Loan #{$loan->id}",
            'branch_id' => $journal->branch_id,
            'user_id' => $journal->user_id,
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
                'fee_amount' => $loan->product->fee ? $loan->product->fee->chart_account_id : null, // Use interest account for fees
                'penalty_amount' => $loan->product->penalty ? $loan->product->penalty->penalty_receivables_account_id : null // Use interest account for penalties
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

            // Get the current penalty amount before removing it
            $currentPenaltyAmount = $schedule->penalty_amount;

            Log::info("Removing penalty for schedule ID: {$scheduleId}, current penalty amount: {$currentPenaltyAmount}", [
                'schedule_id' => $scheduleId,
                'customer_id' => $schedule->customer_id,
                'penalty_amount' => $currentPenaltyAmount,
                'reason' => $reason
            ]);

            // Remove the penalty-related GL transactions
            // Using only transaction_id and transaction_type for reliable matching
            // (customer_id can sometimes be inconsistent due to data entry issues)
            $deletedCount = GlTransaction::where('transaction_id', $scheduleId)
                ->whereIn('transaction_type', ['Penalty', 'penalty', 'Loan Penalty'])
                ->delete();

            Log::info("Deleted {$deletedCount} penalty GL transactions for schedule ID: {$scheduleId}");

            // Update schedule to remove penalty (ensure it's 0)
            $schedule->update([
                'penalty_amount' => 0,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "Penalty removed successfully from schedule and {$deletedCount} GL transactions deleted"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to remove penalty for schedule ID: {$scheduleId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Check if payment is made before or on due date and remove penalties if applicable
     */
    private function checkAndRemovePenaltyForOnTimePayment($schedule, $paymentData)
    {
        try {
            // Get the payment date (use provided date or current date)
            $paymentDate = isset($paymentData['payment_date'])
                ? Carbon::parse($paymentData['payment_date'])
                : Carbon::today();

            // Get the schedule due date
            $dueDate = Carbon::parse($schedule->due_date);

            // Check if payment is made before or on the due date
            if ($paymentDate->lte($dueDate) && $schedule->penalty_amount > 0) {
                Log::info("Payment made on/before due date. Removing penalty for schedule {$schedule->id}", [
                    'schedule_id' => $schedule->id,
                    'payment_date' => $paymentDate->format('Y-m-d'),
                    'due_date' => $dueDate->format('Y-m-d'),
                    'penalty_amount' => $schedule->penalty_amount,
                    'customer_id' => $schedule->customer_id
                ]);

                // Remove penalty from schedule and GL transactions
                $this->removePenalty($schedule->id, "Automatic penalty removal - payment made on/before due date ({$paymentDate->format('Y-m-d')})");

                // Refresh the schedule model to get updated penalty_amount
                $schedule->refresh();

                Log::info("Penalty successfully removed for on-time payment on schedule {$schedule->id}");
            }

        } catch (\Exception $e) {
            // Log the error but don't stop the payment process
            Log::error("Failed to check/remove penalty for on-time payment on schedule {$schedule->id}", [
                'error' => $e->getMessage(),
                'schedule_id' => $schedule->id
            ]);
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

    /**
     * Create journal entries for cash deposit payments
     * DR: Cash Deposit Account (reducing balance)
     * CR: Principal/Interest/Penalty/Fee Accounts
     */
    private function createCashDepositJournalEntries($payment, $loan, $schedulePayment, $repayment, $cashDeposit)
    {
        // Get chart accounts from loan product or use defaults
        $principalAccount = ChartAccount::find($loan->product->principal_gl_account_id ?? 1);
        $interestAccount = ChartAccount::find($loan->product->interest_gl_account_id ?? 2);
        $penaltyAccount = ChartAccount::find($loan->product->penalty_gl_account_id ?? 3);
        $feeAccount = ChartAccount::find($loan->product->fee_gl_account_id ?? 4);
        $cashDepositAccount = ChartAccount::find($cashDeposit->type->chart_account_id ?? 5);

        $journalRef = 'LOAN-REPAY-CD-' . $loan->id . '-' . time();

        // Create payment items for tracking
        if ($schedulePayment['principal'] > 0) {
            \App\Models\PaymentItem::create([
                'payment_id' => $payment->id,
                'chart_account_id' => $principalAccount->id,
                'description' => 'Principal payment from cash deposit',
                'amount' => $schedulePayment['principal'],
            ]);

            // DR: Cash Deposit Account (reducing the deposit)
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $cashDepositAccount->id,
                'debit' => $schedulePayment['principal'],
                'credit' => 0,
                'description' => "Cash deposit withdrawal for principal payment - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);

            // CR: Principal Account (loan repayment)
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $principalAccount->id,
                'debit' => 0,
                'credit' => $schedulePayment['principal'],
                'description' => "Principal payment from cash deposit - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);
        }

        if ($schedulePayment['interest'] > 0) {
            \App\Models\PaymentItem::create([
                'payment_id' => $payment->id,
                'chart_account_id' => $interestAccount->id,
                'description' => 'Interest payment from cash deposit',
                'amount' => $schedulePayment['interest'],
            ]);

            // DR: Cash Deposit Account (reducing the deposit)
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $cashDepositAccount->id,
                'debit' => $schedulePayment['interest'],
                'credit' => 0,
                'description' => "Cash deposit withdrawal for interest payment - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);

            // CR: Interest Account (interest income)
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $interestAccount->id,
                'debit' => 0,
                'credit' => $schedulePayment['interest'],
                'description' => "Interest payment from cash deposit - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);
        }

        if ($schedulePayment['penalty'] > 0) {
            \App\Models\PaymentItem::create([
                'payment_id' => $payment->id,
                'chart_account_id' => $penaltyAccount->id,
                'description' => 'Penalty payment from cash deposit',
                'amount' => $schedulePayment['penalty'],
            ]);

            // DR: Cash Deposit Account (reducing the deposit)
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $cashDepositAccount->id,
                'debit' => $schedulePayment['penalty'],
                'credit' => 0,
                'description' => "Cash deposit withdrawal for penalty payment - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);

            // CR: Penalty Account (penalty income)
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $penaltyAccount->id,
                'debit' => 0,
                'credit' => $schedulePayment['penalty'],
                'description' => "Penalty payment from cash deposit - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);
        }

        if ($schedulePayment['fee'] > 0) {
            \App\Models\PaymentItem::create([
                'payment_id' => $payment->id,
                'chart_account_id' => $feeAccount->id,
                'description' => 'Fee payment from cash deposit',
                'amount' => $schedulePayment['fee'],
            ]);

            // DR: Cash Deposit Account (reducing the deposit)
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $cashDepositAccount->id,
                'debit' => $schedulePayment['fee'],
                'credit' => 0,
                'description' => "Cash deposit withdrawal for fee payment - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);

            // CR: Fee Account (fee income)
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $feeAccount->id,
                'debit' => 0,
                'credit' => $schedulePayment['fee'],
                'description' => "Fee payment from cash deposit - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);
        }

        if ($schedulePayment['interest'] > 0) {
            \App\Models\PaymentItem::create([
                'payment_id' => $payment->id,
                'chart_account_id' => $interestAccount->id,
                'description' => 'Interest payment from cash deposit',
                'amount' => $schedulePayment['interest'],
            ]);

            // DR: Interest Account, CR: Cash Deposit Account
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $interestAccount->id,
                'debit' => $schedulePayment['interest'],
                'credit' => 0,
                'description' => "Interest payment from cash deposit - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);

            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $cashDepositAccount->id,
                'debit' => 0,
                'credit' => $schedulePayment['interest'],
                'description' => "Interest payment from cash deposit - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);
        }

        if ($schedulePayment['penalty'] > 0) {
            \App\Models\PaymentItem::create([
                'payment_id' => $payment->id,
                'chart_account_id' => $penaltyAccount->id,
                'description' => 'Penalty payment from cash deposit',
                'amount' => $schedulePayment['penalty'],
            ]);

            // DR: Penalty Account, CR: Cash Deposit Account
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $penaltyAccount->id,
                'debit' => $schedulePayment['penalty'],
                'credit' => 0,
                'description' => "Penalty payment from cash deposit - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);

            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $cashDepositAccount->id,
                'debit' => 0,
                'credit' => $schedulePayment['penalty'],
                'description' => "Penalty payment from cash deposit - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);
        }

        if ($schedulePayment['fee'] > 0) {
            \App\Models\PaymentItem::create([
                'payment_id' => $payment->id,
                'chart_account_id' => $feeAccount->id,
                'description' => 'Fee payment from cash deposit',
                'amount' => $schedulePayment['fee'],
            ]);

            // DR: Fee Account, CR: Cash Deposit Account
            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $feeAccount->id,
                'debit' => $schedulePayment['fee'],
                'credit' => 0,
                'description' => "Fee payment from cash deposit - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);

            GlTransaction::create([
                'reference' => $journalRef,
                'reference_type' => 'loan_repayment',
                'chart_account_id' => $cashDepositAccount->id,
                'debit' => 0,
                'credit' => $schedulePayment['fee'],
                'description' => "Fee payment from cash deposit - Loan #{$loan->id}",
                'transaction_date' => $payment->date,
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ]);
        }
    }
}

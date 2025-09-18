<?php

namespace App\Jobs;

use App\Models\BankAccount;
use App\Models\CashCollateral;
use App\Models\Customer;
use App\Models\GlTransaction;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Models\PaymentItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkLoanCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvData;
    protected $validated;
    protected $userId;
    protected $chunkSize = 25;

    /**
     * Create a new job instance.
     */
    public function __construct($csvData, $validated, $userId)
    {
        $this->csvData = $csvData;
        $this->validated = $validated;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting bulk loan creation job', [
            'total_loans' => count($this->csvData),
            'user_id' => $this->userId
        ]);

        $product = LoanProduct::with('principalReceivableAccount')->findOrFail($this->validated['product_id']);
        $bankAccount = BankAccount::where('chart_account_id', $this->validated['chart_account_id'])->first();

        if (!$bankAccount) {
            Log::error('Bank account not found for chart account', ['chart_account_id' => $this->validated['chart_account_id']]);
            return;
        }

        $createdLoans = [];
        $failedLoans = [];
        $repaymentData = [];

        // Process loans in chunks
        $chunks = array_chunk($this->csvData, $this->chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            Log::info("Processing chunk {$chunkIndex}", ['chunk_size' => count($chunk)]);

            foreach ($chunk as $rowIndex => $row) {
                try {
                    $loanData = $this->processLoanRow($row, $product, $bankAccount);

                    if ($loanData) {
                        $loan = $this->createLoan($loanData, $product, $bankAccount);
                        if ($loan) {
                            $createdLoans[] = $loan;

                            // Collect repayment data if amount_paid > 0
                            if ($loanData['amount_paid'] > 0) {
                                $repaymentData[] = [
                                    'loan_id' => $loan->id,
                                    'amount' => $loanData['amount_paid'],
                                    'payment_date' => $loanData['date_applied']
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $failedLoans[] = [
                        'row' => $rowIndex + 1,
                        'customer_no' => $row[0] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                    Log::error('Failed to create loan', [
                        'row' => $rowIndex + 1,
                        'customer_no' => $row[0] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info('Bulk loan creation completed', [
            'created_loans' => count($createdLoans),
            'failed_loans' => count($failedLoans)
        ]);

        // Dispatch repayment job if there are repayments to process
        if (!empty($repaymentData)) {
            Log::info('Dispatching bulk repayment job', ['repayment_count' => count($repaymentData)]);
            BulkRepaymentJob::dispatch($repaymentData, $this->userId);
        }
    }

    /**
     * Process a single loan row
     */
    private function processLoanRow($row, $product, $bankAccount)
    {
        // Map CSV columns to data
        $data = [
            'customer_no' => $row[0] ?? '',
            'customer_name' => $row[1] ?? '',
            'group_id' => $row[2] ?? '',
            'group_name' => $row[3] ?? '',
            'amount' => floatval($row[4] ?? 0),
            'interest' => floatval($row[5] ?? 0),
            'period' => intval($row[6] ?? 0),
            'interest_cycle' => $product->interest_cycle ?? 'monthly', // Use product's interest cycle
            'date_applied' => $row[7] ?? date('Y-m-d'),
            'sector' => $row[8] ?? 'Business',
            'amount_paid' => floatval($row[9] ?? 0)
        ];

        // Validate required fields
        if (empty($data['customer_no']) || $data['amount'] <= 0 || $data['interest'] <= 0 || $data['period'] <= 0) {
            throw new \Exception('Invalid loan data: missing required fields or invalid values');
        }

        // Find customer by customer number
        $customer = Customer::where('customerNo', $data['customer_no'])->first();
        if (!$customer) {
            throw new \Exception("Customer not found: {$data['customer_no']} - {$data['customer_name']}");
        }

        // Validate product limits
        if (!$product->isAmountWithinLimits($data['amount'])) {
            throw new \Exception("Loan amount {$data['amount']} is outside product limits");
        }

        if (!$product->isPeriodWithinLimits($data['period'])) {
            throw new \Exception("Loan period {$data['period']} is outside product limits");
        }

        // Check if customer already has active loan for this product
        $existingLoan = Loan::where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->where('status', 'active')
            ->first();

        if ($existingLoan) {
            throw new \Exception("Customer already has active loan for this product");
        }

        // Check collateral if required
        if ($product->requiresCollateral()) {
            $requiredCollateral = $product->calculateRequiredCollateral($data['amount']);
            $availableCollateral = CashCollateral::getCashCollateralBalance($customer->id);

            if ($availableCollateral < $requiredCollateral) {
                throw new \Exception("Insufficient collateral. Required: {$requiredCollateral}, Available: {$availableCollateral}");
            }
        }

        return array_merge($data, [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'branch_id' => $this->validated['branch_id'],
            'bank_account_id' => $bankAccount->id
        ]);
    }

    /**
     * Create a single loan
     */
    private function createLoan($loanData, $product, $bankAccount)
    {
        return DB::transaction(function () use ($loanData, $product, $bankAccount) {
            // Create loan
            $loan = Loan::create([
                'product_id' => $loanData['product_id'],
                'period' => $loanData['period'],
                'interest' => $loanData['interest'],
                'amount' => $loanData['amount'],
                'customer_id' => $loanData['customer_id'],
                'group_id' => $loanData['group_id'] ?: null,
                'bank_account_id' => $loanData['bank_account_id'],
                'date_applied' => $loanData['date_applied'],
                'disbursed_on' => $loanData['date_applied'],
                'sector' => $loanData['sector'],
                'branch_id' => $loanData['branch_id'],
                'status' => 'active',
                'interest_cycle' => $loanData['interest_cycle'],
                'loan_officer_id' => $this->userId,
            ]);

            // Calculate interest and repayment dates
            $interestAmount = $loan->calculateInterestAmount($loanData['interest']);
            $repaymentDates = $loan->getRepaymentDates();

            // Update loan with totals and schedule
            $loan->update([
                'interest_amount' => $interestAmount,
                'amount_total' => $loan->amount + $interestAmount,
                'first_repayment_date' => $repaymentDates['first_repayment_date'],
                'last_repayment_date' => $repaymentDates['last_repayment_date'],
            ]);

            // Generate repayment schedule
            $loan->generateRepaymentSchedule($loanData['interest']);

            // Post matured interest for past loans
            $loan->postMaturedInterestForPastLoan();

            // Create payment record
            $notes = "Being disbursement for loan of {$product->name}, paid to {$loan->customer->name}, TSHS.{$loanData['amount']}";
            $principalReceivable = $product->principal_receivable_account_id;

            if (!$principalReceivable) {
                throw new \Exception('Principal receivable account not set for this loan product.');
            }

            // Calculate release fees
            $releaseFeeTotal = 0;
            if ($product && $product->fees_ids) {
                $feeIds = is_array($product->fees_ids) ? $product->fees_ids : json_decode($product->fees_ids, true);
                if (is_array($feeIds)) {
                    $releaseFees = \DB::table('fees')
                        ->whereIn('id', $feeIds)
                        ->where('deduction_criteria', 'charge_fee_on_release_date')
                        ->where('status', 'active')
                        ->get();

                    foreach ($releaseFees as $fee) {
                        $feeAmount = (float) $fee->amount;
                        $feeType = $fee->fee_type;
                        $calculatedFee = $feeType === 'percentage'
                            ? ((float) $loanData['amount'] * (float) $feeAmount / 100)
                            : (float) $feeAmount;
                        $releaseFeeTotal += $calculatedFee;
                    }
                }
            }

            $disbursementAmount = $loanData['amount'] - $releaseFeeTotal;

            $payment = Payment::create([
                'reference' => $loan->id,
                'reference_type' => 'Loan Payment',
                'reference_number' => null,
                'date' => $loanData['date_applied'],
                'amount' => $disbursementAmount,
                'description' => $notes,
                'user_id' => $this->userId,
                'payee_type' => 'customer',
                'customer_id' => $loanData['customer_id'],
                'bank_account_id' => $loanData['bank_account_id'],
                'branch_id' => $loanData['branch_id'],
                'approved' => true,
                'approved_by' => $this->userId,
                'approved_at' => now(),
            ]);

            PaymentItem::create([
                'payment_id' => $payment->id,
                'chart_account_id' => $principalReceivable,
                'amount' => $loanData['amount'],
                'description' => $notes,
            ]);

            // Create GL transactions
            GlTransaction::insert([
                [
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $loan->customer_id,
                    'amount' => $disbursementAmount,
                    'nature' => 'credit',
                    'transaction_id' => $loan->id,
                    'transaction_type' => 'Loan Disbursement',
                    'date' => $loanData['date_applied'],
                    'description' => $notes,
                    'branch_id' => $loanData['branch_id'],
                    'user_id' => $this->userId,
                ],
                [
                    'chart_account_id' => $principalReceivable,
                    'customer_id' => $loan->customer_id,
                    'amount' => $loanData['amount'],
                    'nature' => 'debit',
                    'transaction_id' => $loan->id,
                    'transaction_type' => 'Loan Disbursement',
                    'date' => $loanData['date_applied'],
                    'description' => $notes,
                    'branch_id' => $loanData['branch_id'],
                    'user_id' => $this->userId,
                ]
            ]);

            // Post penalty amount to GL if exists
            $penalty = $product->penalty;
            $penaltyAmount = LoanSchedule::where('loan_id', $loan->id)->sum('penalty_amount');

            if ($penaltyAmount > 0 && $penalty) {
                $receivableId = $penalty->penalty_receivables_account_id;
                $incomeId = $penalty->penalty_income_account_id;

                if ($receivableId && $incomeId) {
                    GlTransaction::insert([
                        [
                            'chart_account_id' => $receivableId,
                            'customer_id' => $loan->customer_id,
                            'amount' => $penaltyAmount,
                            'nature' => 'debit',
                            'transaction_id' => $loan->id,
                            'transaction_type' => 'Loan Penalty',
                            'date' => $loanData['date_applied'],
                            'description' => $notes,
                            'branch_id' => $loanData['branch_id'],
                            'user_id' => $this->userId,
                        ],
                        [
                            'chart_account_id' => $incomeId,
                            'customer_id' => $loan->customer_id,
                            'amount' => $penaltyAmount,
                            'nature' => 'credit',
                            'transaction_id' => $loan->id,
                            'transaction_type' => 'Loan Penalty',
                            'date' => $loanData['date_applied'],
                            'description' => $notes,
                            'branch_id' => $loanData['branch_id'],
                            'user_id' => $this->userId,
                        ]
                    ]);
                }
            }

            return $loan;
        });
    }
}

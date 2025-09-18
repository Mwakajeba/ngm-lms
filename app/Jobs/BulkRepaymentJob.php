<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Services\LoanRepaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkRepaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $repaymentData;
    protected $userId;
    protected $chunkSize = 25;

    /**
     * Create a new job instance.
     */
    public function __construct($repaymentData, $userId)
    {
        $this->repaymentData = $repaymentData;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting bulk repayment job', [
            'total_repayments' => count($this->repaymentData),
            'user_id' => $this->userId
        ]);

        $repaymentService = new LoanRepaymentService();
        $processedRepayments = [];
        $failedRepayments = [];

        // Process repayments in chunks
        $chunks = array_chunk($this->repaymentData, $this->chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            Log::info("Processing repayment chunk {$chunkIndex}", ['chunk_size' => count($chunk)]);

            foreach ($chunk as $repaymentIndex => $repaymentInfo) {
                try {
                    $loan = Loan::find($repaymentInfo['loan_id']);

                    if (!$loan) {
                        throw new \Exception("Loan not found: {$repaymentInfo['loan_id']}");
                    }

                    // Skip if amount is zero or negative
                    if ($repaymentInfo['amount'] <= 0) {
                        Log::info("Skipping repayment for loan {$repaymentInfo['loan_id']} - amount is zero or negative");
                        continue;
                    }

                    $paymentData = [
                        'payment_date' => $repaymentInfo['payment_date'],
                        'bank_account_id' => $loan->bank_account_id,
                        'payment_source' => 'bank'
                    ];

                    $result = $repaymentService->processRepayment(
                        $repaymentInfo['loan_id'],
                        $repaymentInfo['amount'],
                        $paymentData
                    );

                    if ($result['success']) {
                        $processedRepayments[] = [
                            'loan_id' => $repaymentInfo['loan_id'],
                            'amount' => $result['paid_amount'],
                            'balance' => $result['balance']
                        ];

                        Log::info("Repayment processed successfully", [
                            'loan_id' => $repaymentInfo['loan_id'],
                            'paid_amount' => $result['paid_amount'],
                            'balance' => $result['balance']
                        ]);
                    } else {
                        throw new \Exception("Repayment processing failed");
                    }

                } catch (\Exception $e) {
                    $failedRepayments[] = [
                        'loan_id' => $repaymentInfo['loan_id'],
                        'amount' => $repaymentInfo['amount'],
                        'error' => $e->getMessage()
                    ];

                    Log::error('Failed to process repayment', [
                        'loan_id' => $repaymentInfo['loan_id'],
                        'amount' => $repaymentInfo['amount'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info('Bulk repayment job completed', [
            'processed_repayments' => count($processedRepayments),
            'failed_repayments' => count($failedRepayments)
        ]);
    }
}

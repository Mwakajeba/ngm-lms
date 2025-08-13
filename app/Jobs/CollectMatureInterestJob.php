<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\GlTransaction;
use App\Models\ChartAccount;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CollectMatureInterestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('Starting mature interest collection job');

        try {
            DB::beginTransaction();

            // Get all active loans
            $activeLoans = Loan::where('status', 'active')
                ->with(['product', 'customer', 'branch'])
                ->get();

            $totalProcessed = 0;
            $totalInterestPosted = 0;

            foreach ($activeLoans as $loan) {
                $processed = $this->processLoanMatureInterest($loan);
                if ($processed) {
                    $totalProcessed++;
                }
            }

            DB::commit();

            Log::info("Mature interest collection completed. Processed: {$totalProcessed} loans, Total interest posted: {$totalInterestPosted}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in mature interest collection job: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process mature interest for a specific loan
     */
    private function processLoanMatureInterest(Loan $loan): bool
    {
        try {
            // Get matured schedules (due date <= today and not fully paid)
            $maturedSchedules = $loan->schedule()
                ->where('due_date', '<=', Carbon::today())
                ->where('interest', '>', 0)
                ->get();

            if ($maturedSchedules->isEmpty()) {
                return false;
            }

            $totalInterestPosted = 0;

            foreach ($maturedSchedules as $schedule) {
                $interestPosted = $this->processScheduleMatureInterest($loan, $schedule);
                $totalInterestPosted += $interestPosted;
            }

            if ($totalInterestPosted > 0) {
                Log::info("Posted mature interest for loan {$loan->loanNo}: TZS " . number_format($totalInterestPosted, 2));
            }

            return $totalInterestPosted > 0;

        } catch (\Exception $e) {
            Log::error("Error processing loan {$loan->loanNo}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process mature interest for a specific schedule
     */
    private function processScheduleMatureInterest(Loan $loan, LoanSchedule $schedule): float
    {
        // Calculate unpaid interest amount
        $totalInterest = $schedule->interest;
        $paidInterest = $schedule->repayments->sum('interest');
        $unpaidInterest = $totalInterest - $paidInterest;

        if ($unpaidInterest <= 0) {
            return 0;
        }

        // Get chart accounts from loan product
        $interestReceivableAccountId = $loan->product->interest_receivable_account_id;
        $interestRevenueAccountId = $loan->product->interest_revenue_account_id;

        if (!$interestReceivableAccountId || !$interestRevenueAccountId) {
            Log::warning("Missing chart accounts for loan product {$loan->product->id}");
            return 0;
        }

        // Check if interest has already been posted for this loan on this due date
        $existingPosting = GlTransaction::where('chart_account_id', $interestReceivableAccountId)
            ->where('customer_id', $loan->customer_id)
            ->where('date', $schedule->due_date)
            ->where('amount', $unpaidInterest)
            ->first();

        if ($existingPosting) {
            Log::info("Interest already posted for loan {$loan->loanNo} on due date {$schedule->due_date->format('Y-m-d')}");
            return 0;
        }

        // Create GL transactions
        $transactionDate = Carbon::today();
        $description = "Mature interest for loan {$loan->loanNo}, schedule due {$schedule->due_date->format('Y-m-d')}";

        // 1. Credit Interest Receivable (Asset - Credit increases)
        GlTransaction::create([
            'chart_account_id' => $interestReceivableAccountId,
            'customer_id' => $loan->customer_id,
            'amount' => $unpaidInterest,
            'nature' => 'credit',
            'transaction_id' => $schedule->id,
            'transaction_type' => 'mature_interest',
            'date' => $transactionDate,
            'description' => $description,
            'branch_id' => $loan->branch_id,
            'user_id' => null, // System generated
        ]);

        // 2. Debit Interest Revenue (Income - Debit increases)
        GlTransaction::create([
            'chart_account_id' => $interestRevenueAccountId,
            'customer_id' => $loan->customer_id,
            'amount' => $unpaidInterest,
            'nature' => 'debit',
            'transaction_id' => $schedule->id,
            'transaction_type' => 'mature_interest',
            'date' => $transactionDate,
            'description' => $description,
            'branch_id' => $loan->branch_id,
            'user_id' => null, // System generated
        ]);

        Log::info("Posted mature interest for schedule {$schedule->id}: TZS " . number_format($unpaidInterest, 2));

        return $unpaidInterest;
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Mature interest collection job failed: ' . $exception->getMessage());
    }
}
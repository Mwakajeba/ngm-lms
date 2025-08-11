<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Repayment;
use App\Services\LoanRepaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LoanRepaymentController extends Controller
{
    protected $repaymentService;

    public function __construct(LoanRepaymentService $repaymentService)
    {
        $this->repaymentService = $repaymentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'loan_id' => 'required|exists:loans,id',
                'schedule_id' => 'required|exists:loan_schedules,id',
                'payment_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'bank_account_id' => 'required|exists:bank_accounts,id',
            ]);

            // Prepare payment data
            $paymentData = [
                'payment_date' => $request->payment_date,
                'bank_account_id' => $request->bank_account_id,
            ];

            // Get calculation method from loan product
            $loan = Loan::with('product')->findOrFail($request->loan_id);
            $calculationMethod = $loan->product->interest_method ?? 'flat_rate';

            // Process repayment using service
            $result = $this->repaymentService->processRepayment(
                $request->loan_id,
                $request->amount,
                $paymentData,
                $calculationMethod
            );

            return redirect()->back()->with('success', 'Repayment recorded successfully!');

        } catch (\Exception $e) {
            Log::error('Loan repayment error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to record repayment: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get repayment history for a loan
     */
    public function getRepaymentHistory($loanId)
    {
        $repayments = Repayment::where('loan_id', $loanId)
            ->with(['schedule', 'bankAccount'])
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json($repayments);
    }

    /**
     * Get schedule details for repayment
     */
    public function getScheduleDetails($scheduleId)
    {
        $schedule = LoanSchedule::with(['loan'])->findOrFail($scheduleId);

        return response()->json([
            'schedule' => $schedule,
            'total_due' => $schedule->principal + $schedule->interest + $schedule->fee_amount + $schedule->penalty_amount,
        ]);
    }

    /**
     * Remove penalty from schedule
     */
    public function removePenalty(Request $request, $scheduleId)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:500',
            ]);

            $result = $this->repaymentService->removePenalty($scheduleId, $request->reason);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Penalty removal error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove penalty: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate loan schedule
     */
    public function calculateSchedule(Request $request, $loanId)
    {
        try {
            $request->validate([
                'method' => 'required|in:flat_rate,reducing_equal_installment,reducing_equal_principal',
            ]);

            $loan = Loan::findOrFail($loanId);
            $schedules = $this->repaymentService->calculateSchedule($loan, $request->method);

            return response()->json([
                'success' => true,
                'schedules' => $schedules
            ]);

        } catch (\Exception $e) {
            Log::error('Schedule calculation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk repayment processing
     */
    public function bulkRepayment(Request $request)
    {
        try {
            $request->validate([
                'repayments' => 'required|array|min:1',
                'repayments.*.loan_id' => 'required|exists:loans,id',
                'repayments.*.amount' => 'required|numeric|min:0.01',
                'repayments.*.payment_date' => 'required|date',
                'repayments.*.bank_account_id' => 'required|exists:bank_accounts,id',
            ]);

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($request->repayments as $repaymentData) {
                try {
                    $paymentData = [
                        'payment_date' => $repaymentData['payment_date'],
                        'bank_account_id' => $repaymentData['bank_account_id'],
                    ];

                    $loan = Loan::with('product')->findOrFail($repaymentData['loan_id']);
                    $calculationMethod = $loan->product->interest_method ?? 'flat_rate';

                    $result = $this->repaymentService->processRepayment(
                        $repaymentData['loan_id'],
                        $repaymentData['amount'],
                        $paymentData,
                        $calculationMethod
                    );

                    $results[] = [
                        'loan_id' => $repaymentData['loan_id'],
                        'success' => true,
                        'result' => $result
                    ];
                    $successCount++;

                } catch (\Exception $e) {
                    $results[] = [
                        'loan_id' => $repaymentData['loan_id'],
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                    $errorCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Processed {$successCount} repayments successfully, {$errorCount} failed",
                'results' => $results,
                'summary' => [
                    'total' => count($request->repayments),
                    'success' => $successCount,
                    'failed' => $errorCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk repayment error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk repayment: ' . $e->getMessage()
            ], 500);
        }
    }
}

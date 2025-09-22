<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Repayment;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\GlTransaction;
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
            // Add debugging
            Log::info('Repayment request received', $request->all());

            $request->validate([
                'loan_id' => 'required|exists:loans,id',
                'schedule_id' => 'required|exists:loan_schedules,id',
                'payment_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'payment_source' => 'required|in:bank,cash_deposit',
                'bank_account_id' => 'required_if:payment_source,bank|nullable|exists:chart_accounts,id',
                'cash_deposit_id' => 'required_if:payment_source,cash_deposit|nullable|exists:cash_collaterals,id',
            ]);

            Log::info('Validation passed');

            // Check cash deposit balance if using cash deposit
            if ($request->payment_source === 'cash_deposit') {
                $cashDeposit = \App\Models\CashCollateral::findOrFail($request->cash_deposit_id);

                if ($cashDeposit->amount < $request->amount) {
                    return redirect()->back()->with('error', 'Insufficient cash deposit balance. Available: TSHS ' . number_format($cashDeposit->amount, 2));
                }
            }

            // Prepare payment data based on source
            $paymentData = [
                'payment_date' => $request->payment_date,
                'payment_source' => $request->payment_source,
            ];

            if ($request->payment_source === 'bank') {
                $paymentData['bank_account_id'] = $request->bank_account_id;
            } else {
                $paymentData['cash_deposit_id'] = $request->cash_deposit_id;
            }

            // Get calculation method from loan product
            $loan = Loan::with('product')->findOrFail($request->loan_id);
            $calculationMethod = $loan->product->interest_method ?? 'flat_rate';

            Log::info('Processing repayment', [
                'loan_id' => $request->loan_id,
                'amount' => $request->amount,
                'calculation_method' => $calculationMethod,
                'payment_source' => $request->payment_source
            ]);

            // Process repayment using service
            $result = $this->repaymentService->processRepayment(
                $request->loan_id,
                $request->amount,
                $paymentData,
                $calculationMethod
            );

            Log::info('Repayment processing result', $result);

            return redirect()->back()->with('success', 'Repayment recorded successfully!');

        } catch (\Exception $e) {
            Log::error('Loan repayment error: ' . $e->getMessage());
            Log::error('Repayment error stack trace: ' . $e->getTraceAsString());

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
    public function edit($id)
    {
        $repayment = Repayment::with(['loan', 'schedule', 'bankAccount', 'customer'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'repayment' => $repayment
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'payment_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'bank_account_id' => 'required|exists:chart_accounts,id',
            ]);

            $repayment = Repayment::with(['loan', 'receipt', 'bankAccount'])->findOrFail($id);

            // Store the loan and schedule info before deletion
            $loanId = $repayment->loan_id;
            $scheduleId = $repayment->loan_schedule_id;
            $customerId = $repayment->customer_id;
            $dueDate = $repayment->due_date;

            // Delete the existing repayment (this will also delete receipt and GL transactions)
            $this->deleteRepaymentInternal($repayment);

            // Create new repayment with updated details
            $paymentData = [
                'payment_date' => $request->payment_date,
                'bank_account_id' => $request->bank_account_id,
            ];

            // Get calculation method from loan product
            $loan = Loan::with('product')->findOrFail($loanId);
            $calculationMethod = $loan->product->interest_method ?? 'flat_rate';

            // Process new repayment using service
            $result = $this->repaymentService->processRepayment(
                $loanId,
                $request->amount,
                $paymentData,
                $calculationMethod
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Repayment updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Repayment update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update repayment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Internal method to delete repayment and associated records
     */
    private function deleteRepaymentInternal($repayment)
    {
        // Delete associated receipt and GL transactions
        if ($repayment->receipt) {
            // Delete GL transactions
            GlTransaction::where('transaction_id', $repayment->receipt->id)
                ->where('transaction_type', 'receipt')
                ->delete();

            // Delete receipt items
            ReceiptItem::where('receipt_id', $repayment->receipt->id)->delete();

            // Delete receipt
            $repayment->receipt->delete();
        }

        // Also ensure the related loan is set back to active
        $loan = $repayment->loan; // uses relationship
        if ($loan) {
            $loan->status = 'active';
            $loan->save();
        }

        // Delete repayment
        $repayment->delete();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $repayment = Repayment::with(['loan', 'receipt'])->findOrFail($id);

            // Delete repayment and associated records
            $this->deleteRepaymentInternal($repayment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Repayment deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Repayment deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete repayment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete repayments
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:repayments,id',
        ]);

        DB::beginTransaction();
        try {
            $repayments = Repayment::with(['loan', 'receipt'])->whereIn('id', $validated['ids'])->get();
            $deletedCount = 0;

            foreach ($repayments as $repayment) {
                $this->deleteRepaymentInternal($repayment);
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Repayments deleted successfully.',
                'deleted' => $deletedCount,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Bulk repayment deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete repayments: ' . $e->getMessage(),
            ], 500);
        }
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
                'amount' => 'required|numeric|min:0',
                'loan_id' => 'required|exists:loans,id',
                'schedule_id' => 'required|exists:loan_schedules,id',
                'reason' => 'nullable|string|max:500',
            ]);

            $result = $this->repaymentService->removePenalty(
                $request->schedule_id,
                $request->reason,
                $request->amount,
                $request->loan_id
            );

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
                'repayments.*.bank_account_id' => 'required|exists:chart_accounts,id',
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

    /**
     * Print receipt for repayment
     */
    public function printReceipt($id)
    {
        try {
            $repayment = Repayment::with([
                'loan.customer',
                'schedule',
                'chartAccount',
                'receipt.receiptItems.chartAccount'
            ])->findOrFail($id);

            // Generate receipt data for thermal printer
            $receiptData = [
                'receipt_number' => $repayment->receipt->reference ?? 'N/A',
                'date' => $repayment->payment_date,
                'customer_name' => $repayment->customer->name,
                'loan_number' => $repayment->loan->loanNo,
                'amount_paid' => $repayment->amount_paid,
                'payment_breakdown' => [
                    'principal' => $repayment->principal,
                    'interest' => $repayment->interest,
                    'penalty' => $repayment->penalt_amount,
                    'fee' => $repayment->fee_amount,
                ],
                'bank_account' => $repayment->chartAccount()->name ?? 'N/A',
                'received_by' => auth()->user()->name,
                'branch' => auth()->user()->branch->name ?? 'N/A',
            ];

            return response()->json([
                'success' => true,
                'receipt_data' => $receiptData
            ]);

        } catch (\Exception $e) {
            Log::error('Receipt print error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate receipt: ' . $e->getMessage()
            ], 500);
        }
    }
}

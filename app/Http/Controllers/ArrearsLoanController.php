<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use PDF;

class ArrearsLoanController extends Controller
{
    // Show all loans in arrears for 30+ days (AJAX DataTable)
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $loans = DB::table('loans')
                    ->join('customers', 'loans.customer_id', '=', 'customers.id')
                    ->join('loan_schedules', 'loans.id', '=', 'loan_schedules.loan_id')
                    ->leftJoin('repayments', 'loans.id', '=', 'repayments.loan_id')
                    ->where('loans.status', 'active')
                    ->whereDate('loan_schedules.due_date', '<', now())
                    ->select(
                        'loans.id as loan_id',
                        'customers.name as customer_name',
                        'customers.customerNo as customer_no',
                        'loans.loanNo as loan_no',
                        DB::raw('SUM(loan_schedules.principal + loan_schedules.interest) as amount_in_arrears'),
                        DB::raw('(loans.amount - COALESCE(SUM(repayments.principal), 0)) as total_outstanding'),
                        DB::raw('MAX(DATEDIFF(CURDATE(), loan_schedules.due_date)) as days_in_arrears')
                    )
                    ->groupBy('loans.id', 'customers.name', 'customers.customerNo', 'loans.loanNo', 'loans.amount')
                    ->havingRaw('MAX(DATEDIFF(CURDATE(), loan_schedules.due_date)) BETWEEN 1 AND 30')
                    ->orderByDesc('days_in_arrears')
                    ->get();
                return response()->json(['data' => $loans]);
            } catch (\Exception $e) {
                \Log::error('ArrearsLoanController AJAX error: ' . $e->getMessage());
                return response()->json(['data' => [], 'error' => $e->getMessage()], 500);
            }
        }
        return view('arrears_loans.list');
    }

    // Export all loans in arrears for 30+ days to PDF
    public function exportPdf(Request $request)
    {
        $loans = DB::table('loan_schedules')
            ->join('customers', 'loan_schedules.customer_id', '=', 'customers.id')
            ->where('loan_schedules.days_in_arrears', '>=', 30)
            ->select(
                'customers.name as customer_name',
                DB::raw('(loan_schedules.principal + loan_schedules.interest) as amount_in_arrears'),
                'loan_schedules.days_in_arrears'
            )
            ->orderByDesc('loan_schedules.days_in_arrears')
            ->get();
        $pdf = PDF::loadView('arrears_loans.pdf', compact('loans'));
        return $pdf->download('arrears_loans_30plus_days.pdf');
    }
}

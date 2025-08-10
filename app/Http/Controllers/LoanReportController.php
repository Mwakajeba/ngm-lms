<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\DisbursementsExport; 
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class LoanReportController extends Controller
{
    public function loanDisbursementReport(Request $request)
    {
        // Pata data ya kuchuja kutoka kwenye request, ukiweka default values
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $branchId = $request->input('branch_id');
        $companyId = $request->input('company_id');

        info('start date: ' . $startDate);
        info('end date: ' . $endDate);
        info('branch: ' . $branchId);

        // Unda query ya loans na uweke filters
        $loansQuery = Loan::with(['customer', 'product', 'branch'])
            ->where('status', 'active')
            ->whereBetween('disbursed_on', [$startDate, $endDate]);

        // Weka filter ya branch
        if ($branchId) {
            $loansQuery->where('branch_id', $branchId);
        }

        // Weka filter ya company
        if ($companyId) {
            $loansQuery->whereHas('product', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }

        $disbursements = $loansQuery->get();

        // Kokotoa muhtasari wa ripoti
        $summary = [
            'total_disbursed' => $disbursements->sum('amount'),
            'loan_count' => $disbursements->count(),
            'average_disbursed' => $disbursements->count() > 0 ? $disbursements->sum('amount') / $disbursements->count() : 0,
        ];

        // Pata list ya branches na companies kwa ajili ya dropdown
        $branches = Branch::all();
        $companies = Company::all();

        // Rudi na view ya ripoti
        return view('loans.reports.disbursed', compact('disbursements', 'summary', 'branches', 'companies'));
    }



    public function exportLoanDisbursement(Request $request)
    {
        // 1. Pata filters kutoka kwenye request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');
        $companyId = $request->input('company_id');
        $exportType = $request->input('export_type');
        $exportAction = $request->input('export_action', 'download'); // 'download' ni default

        // 2. Unda query ya loans na uweke filters kama ilivyo kwenye method ya report
        $loansQuery = Loan::with(['customer', 'product', 'branch'])
            ->where('status', 'active')
            ->whereBetween('disbursed_on', [$startDate, $endDate]);

        if ($branchId) {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($companyId) {
            $loansQuery->whereHas('product', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }

        $disbursements = $loansQuery->get();

        // 3. Tekeleza mantiki ya export kulingana na aina ya faili
        if ($exportType === 'pdf') {
            $pdf = PDF::loadView('loans.reports.pdf', compact('disbursements', 'startDate', 'endDate'));
            
            if ($exportAction === 'view') {
                return $pdf->stream('loan_disbursement_report.pdf'); // Hii itaonyesha PDF kwenye browser
            }
            
            return $pdf->download('loan_disbursement_report.pdf'); // Hii itapakua (default
        } elseif ($exportType === 'excel') {
            // Hapa tunatumia Maatwebsite/Excel
            return Excel::download(new DisbursementsExport($disbursements), 'loan_disbursement_report.xlsx');
        }

        // Rudi na ujumbe wa kosa ikiwa aina ya export haijatambuliwa
        return response()->json(['message' => 'Invalid export type.'], 400);
    }
}

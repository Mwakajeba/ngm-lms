<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoanReportController extends Controller
{
    public function loanDisbursementReport(Request $request)
    {
        // Pata data ya kuchuja (filters) kutoka kwenye request
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $branchId = $request->input('branch_id');
        $companyId = $request->input('company_id');

        // Unda query ya loans na uweke filters
        $loans = Loan::with(['customer', 'product', 'branch', 'company'])
            ->where('status', 'active') // Filter loans with 'active' status as requested
            ->whereBetween('disbursed_on', [$startDate, $endDate]);

        // Weka filter ya branch kama ipo kwenye request
        if ($branchId) {
            $loans->where('branch_id', $branchId);
        }

        // Weka filter ya company kama ipo kwenye request
        if ($companyId) {
            $loans->where('company_id', $companyId);
        }

        $disbursements = $loans->get();

        // Kokotoa muhtasari wa ripoti
        $summary = [
            'total_disbursed' => $disbursements->sum('amount'),
            'loan_count' => $disbursements->count(),
            'average_disbursed' => $disbursements->count() > 0 ? $disbursements->sum('amount') / $disbursements->count() : 0,
        ];
        
        // Pata list ya branches na companies kwa ajili ya dropdown za kuchuja
        $branches = Branch::all();
        $companies = Company::all();

        // Rudi na view ya ripoti, ukiwa na data zote muhimu
        return view('loans.reports.disbursed', compact('disbursements', 'summary', 'branches', 'companies'));
    }

 
    public function exportLoanDisbursement(Request $request)
    {
        // Pata filters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');
        $companyId = $request->input('company_id');
        $exportType = $request->input('export_type');

        // Pata data kama ilivyo kwenye method ya juu
        $loans = Loan::with(['customer', 'product', 'branch', 'company'])
            ->where('status', 'active')
            ->whereBetween('disbursed_on', [$startDate, $endDate]);

        if ($branchId) {
            $loans->where('branch_id', $branchId);
        }

        if ($companyId) {
            $loans->where('company_id', $companyId);
        }

        $disbursements = $loans->get();

        if ($exportType === 'pdf') {
       
        } elseif ($exportType === 'excel') {
            
        }

        // Kwa sasa, rudi na data ya json au ujumbe wa error
        return response()->json(['message' => 'Export functionality is not yet implemented. Please install a library for PDF or Excel exports.'], 400);
    }
}

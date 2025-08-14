<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\DisbursementsExport;
use App\Exports\RepaymentExport;
use App\Models\Repayment;
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
        $loansQuery = Loan::with(['customer', 'product', 'branch', 'loanOfficer'])
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
            'total_interest_expected' => $disbursements->sum('interest_amount'),
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
        $loansQuery = Loan::with(['customer', 'product', 'branch', 'loanOfficer'])
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
        $branch = $branchId ? Branch::findOrFail($branchId) : (object)['name' => 'All Branches'];


        // 3. Tekeleza mantiki ya export kulingana na aina ya faili
        if ($exportType === 'pdf') {
            $pdf = PDF::loadView('loans.reports.pdf', compact('disbursements', 'startDate', 'endDate', 'branch'))
                ->setPaper('a3', 'landscape');

            if ($exportAction === 'view') {
                return $pdf->stream('loan_disbursement_report.pdf');  // Hii itaonyesha PDF kwenye browser
            }

            return $pdf->download('loan_disbursement_report.pdf'); // Hii itapakua (default
        } elseif ($exportType === 'excel') {
            // Hapa tunatumia Maatwebsite/Excel
            return Excel::download(new DisbursementsExport($disbursements), 'loan_disbursement_report.xlsx');
        }

        // Rudi na ujumbe wa kosa ikiwa aina ya export haijatambuliwa
        return response()->json(['message' => 'Invalid export type.'], 400);
    }


    //////////REPAYMENT FUNCTION REPORT////

    public function getRepaymentReport(Request $request)
    {
        // 1. Pata filters kutoka kwenye request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');
        $exportType = $request->input('export_type');
        $exportAction = $request->input('export_action', 'download');

        // 2. Unda query ya malipo
        $repaymentsQuery = Repayment::with(['loan.customer', 'loan.branch', 'loan.product', 'loan.loanOfficer'])
            ->whereBetween('payment_date', [$startDate, $endDate]);

        if ($branchId) {
            $repaymentsQuery->whereHas('loan', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        }

        $repayments = $repaymentsQuery->get();

        // Calculate summary values correctly
        $summary['total_principal'] = $repayments->sum('principal');
        $summary['total_interest'] = $repayments->sum('interest');
        $summary['total_fees'] = $repayments->sum('fee_amount');
        $summary['total_penalty'] = $repayments->sum('penalt_amount');
        $summary['total_paid'] = $repayments->sum(function ($repayment) {
            return ($repayment->principal ?? 0) + ($repayment->interest ?? 0) + ($repayment->fee_amount ?? 0) + ($repayment->penalt_amount ?? 0);
        });
        $summary['repayment_count'] = $repayments->count();
        $summary['average_paid'] = $repayments->count() > 0 ? $summary['total_paid'] / $repayments->count() : 0;

        // 4. Pata data ya branch
        $branches = Branch::all();

        return view('loans.reports.repayments.repayment', compact('repayments', 'summary', 'startDate', 'endDate', 'branches'));
    }


    public function exportLoanRepayment(Request $request)
    {
        // 1. Pata filters kutoka kwenye request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');
        $exportType = $request->input('export_type');
        $exportAction = $request->input('export_action', 'download');

        // 2. Unda query ya malipo
        $repaymentsQuery = Repayment::with(['loan.customer', 'loan.branch', 'loan.product', 'loan.loanOfficer'])
            ->whereBetween('payment_date', [$startDate, $endDate]);

        if ($branchId) {
            $repaymentsQuery->whereHas('loan', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        }

        $repayments = $repaymentsQuery->get();
        $summary['total_paid'] = $repayments->sum(function ($repayment) {
            return $repayment->sum('principal') + $repayment->sum('interest') + $repayment->sum('fee_amount') + $repayment->sum('penalt_amount');
        });

        $branch = $branchId ? Branch::findOrFail($branchId) : (object)['name' => 'All Branches'];
        if ($exportType === 'pdf') {
            $branch = $branchId ? Branch::findOrFail($branchId) : (object)['name' => 'All Branches'];
            $pdf = PDF::loadView('loans.reports.repayments.pdf', compact('repayments', 'summary', 'startDate', 'endDate', 'branch'))
                ->setPaper('a3', 'landscape');

            if ($exportAction === 'view') {
                return $pdf->stream('loan_repayment_report.pdf');
            }

            return $pdf->download('loan_repayment_report.pdf');
        } elseif ($exportType === 'excel') {
            // Hapa tunatumia Maatwebsite/Excel
            return Excel::download(new RepaymentExport($repayments), 'loan_disbursement_report.xlsx');
        }
        // ... kwa excel, utahitaji kuongeza mantiki hapa
        return response()->json(['message' => 'Invalid export type.'], 400);
    }
    /**
     * Display the Loan Aging Report view and data.
     */
    public function loanAgingReport(Request $request)
    {
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));
        $branchId = $request->input('branch_id');

        // Get all branches for filter dropdown
        $branches = Branch::all();

        $agingData = [];
        $loansQuery = Loan::with(['customer', 'branch'])
            ->where('status', 'active');
        if ($branchId) {
            $loansQuery->where('branch_id', $branchId);
        }
        $loans = $loansQuery->get();

        foreach ($loans as $loan) {
            // Calculate overdue buckets for each loan
            $current = $bucket_1_30 = $bucket_31_60 = $bucket_61_90 = $bucket_91_plus = $total_overdue = 0;
            // Get schedules if available
            $schedules = $loan->schedules ?? [];
            if (method_exists($loan, 'schedules')) {
                $schedules = $loan->schedules()->get();
            }

            // Calculate total principal paid
            $totalPrincipalPaid = 0;
            if (method_exists($loan, 'repayments')) {
                $totalPrincipalPaid = $loan->repayments()->sum('principal');
            }
            $outstandingBalance = ($loan->amount ?? 0) - $totalPrincipalPaid;

            if (count($schedules) > 0) {
                foreach ($schedules as $schedule) {
                    $due = $schedule->due_date;
                    $dueAmount = $schedule->due_amount ?? ($schedule->principal_due + $schedule->interest_due + $schedule->fee_due + $schedule->penalty_due);
                    $paid = $schedule->paid_amount ?? 0;
                    $outstanding = max(0, $dueAmount - $paid);
                    if ($outstanding <= 0) continue;
                    $days = \Carbon\Carbon::parse($due)->diffInDays($asOfDate, false);
                    if ($days < 0) {
                        $current += $outstanding;
                    } elseif ($days <= 30) {
                        $bucket_1_30 += $outstanding;
                    } elseif ($days <= 60) {
                        $bucket_31_60 += $outstanding;
                    } elseif ($days <= 90) {
                        $bucket_61_90 += $outstanding;
                    } else {
                        $bucket_91_plus += $outstanding;
                    }
                    if ($days > 0) {
                        $total_overdue += $outstanding;
                    }
                }
            } else {
                // No schedules: bucket by days since disbursement if unpaid
                if ($outstandingBalance > 0 && !empty($loan->disbursed_on)) {
                    $days = \Carbon\Carbon::parse($loan->disbursed_on)->diffInDays($asOfDate, false);
                    if ($days < 0) {
                        $current = $outstandingBalance;
                    } elseif ($days <= 30) {
                        $bucket_1_30 = $outstandingBalance;
                        $total_overdue = $outstandingBalance;
                    } elseif ($days <= 60) {
                        $bucket_31_60 = $outstandingBalance;
                        $total_overdue = $outstandingBalance;
                    } elseif ($days <= 90) {
                        $bucket_61_90 = $outstandingBalance;
                        $total_overdue = $outstandingBalance;
                    } else {
                        $bucket_91_plus = $outstandingBalance;
                        $total_overdue = $outstandingBalance;
                    }
                }
            }
            $agingData[] = [
                'customer' => $loan->customer->name ?? 'N/A',
                'customer_no' => $loan->customer->customerNo ?? 'N/A',
                'phone' => $loan->customer->phone1 ?? 'N/A',
                'loan_no' => $loan->loanNo ?? 'N/A',
                'amount' => $loan->amount ?? 'N/A',
                'outstanding_balance' => $outstandingBalance,
                'disbursed_no' => $loan->disbursed_on ?? 'N/A',
                'expiry' => $loan->last_repayment_date ?? 'N/A',
                'branch' => $loan->branch->name ?? 'N/A',
                'current' => $current,
                'bucket_1_30' => $bucket_1_30,
                'bucket_31_60' => $bucket_31_60,
                'bucket_61_90' => $bucket_61_90,
                'bucket_91_plus' => $bucket_91_plus,
                'total_overdue' => $total_overdue,
            ];
        }

        // Only show data if filter applied
        $showData = $request->has('as_of_date') || $request->has('branch_id');
        return view('loans.reports.loan_aging', [
            'branches' => $branches,
            'agingData' => $showData ? $agingData : null,
        ]);
    }

        /**
     * Display the Loan Outstanding Balance Report view and data.
     */
    public function loanOutstandingReport(Request $request)
    {
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));
        $branchId = $request->input('branch_id');
        $loanOfficerId = $request->input('loan_officer_id');

        // Get all branches and loan officers for filter dropdowns
        $branches = \App\Models\Branch::all();
        $loanOfficers = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'Loan Officer');
        })->get();

        $loansQuery = \App\Models\Loan::with(['customer', 'branch', 'loanOfficer'])
            ->where('status', 'active');
        if ($branchId) {
            $loansQuery->where('branch_id', $branchId);
        }
        if ($loanOfficerId) {
            $loansQuery->where('loan_officer_id', $loanOfficerId);
        }
        $loans = $loansQuery->get();

        $outstandingData = [];
        $totalPrincipalDisbursed = 0;
        $totalExpectedInterest = 0;
        $totalPaidInterest = 0;
        $totalPrincipalPaid = 0;
        foreach ($loans as $loan) {
            // Calculate repayments breakdown
            $principalPaid = $interestPaid = $feesPaid = $penaltyPaid = 0;
            if (method_exists($loan, 'repayments')) {
                $principalPaid = $loan->repayments()->sum('principal');
                $interestPaid = $loan->repayments()->sum('interest');
                $feesPaid = $loan->repayments()->sum('fee_amount');
                $penaltyPaid = $loan->repayments()->sum('penalt_amount');
            }
            $outstandingBalance = ($loan->amount ?? 0) - $principalPaid;

            $outstandingData[] = [
                'customer' => $loan->customer->name ?? 'N/A',
                'customer_no' => $loan->customer->customerNo ?? 'N/A',
                'phone' => $loan->customer->phone1 ?? 'N/A',
                'loan_no' => $loan->loanNo ?? 'N/A',
                'amount' => $loan->amount ?? 0,
                'interest' => $loan->interest_amount ?? 0,
                'outstanding_balance' => $outstandingBalance,
                'disbursed_no' => $loan->disbursed_on ?? 'N/A',
                'expiry' => $loan->last_repayment_date ?? 'N/A',
                'branch' => $loan->branch->name ?? 'N/A',
                'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                'principal_paid' => $principalPaid,
                'interest_paid' => $interestPaid,
                'fees_paid' => $feesPaid,
                'penalty_paid' => $penaltyPaid,
            ];
            $totalPrincipalDisbursed += ($loan->amount ?? 0);
            $totalExpectedInterest += ($loan->interest_amount ?? 0);
            $totalPaidInterest += $interestPaid;
            $totalPrincipalPaid += $principalPaid;
        }

        $summary = [
            'total_principal_disbursed' => $totalPrincipalDisbursed,
            'total_expected_interest' => $totalExpectedInterest,
            'total_paid_interest' => $totalPaidInterest,
            'total_principal_paid' => $totalPrincipalPaid,
        ];

        // Only show data if filter applied
        $showData = $request->has('as_of_date') || $request->has('branch_id') || $request->has('loan_officer_id');
        return view('loans.reports.loan_outstanding', [
            'branches' => $branches,
            'loanOfficers' => $loanOfficers,
            'outstandingData' => $showData ? $outstandingData : null,
        ]);
    }
}

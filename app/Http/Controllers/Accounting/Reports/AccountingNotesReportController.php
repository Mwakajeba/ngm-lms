<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class AccountingNotesReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', 'all');
        $levelOfDetail = $request->get('level_of_detail', 'summary');

        // Get branches for filter
        $branches = $company->branches;

        // Get accounting notes data
        $accountingNotesData = $this->getAccountingNotesData($asOfDate, $reportingType, $branchId, $levelOfDetail);

        return view('accounting.reports.accounting-notes.index', compact(
            'accountingNotesData',
            'asOfDate',
            'reportingType',
            'branchId',
            'levelOfDetail',
            'branches',
            'user'
        ));
    }

    private function getAccountingNotesData($asOfDate, $reportingType, $branchId, $levelOfDetail)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get significant accounting policies and notes
        $accountingPolicies = [
            'Basis of Preparation' => [
                'description' => 'The financial statements have been prepared on a ' . ucfirst($reportingType) . ' basis.',
                'details' => [
                    'The financial statements are prepared in accordance with applicable accounting standards.',
                    'All amounts are stated in the local currency (TZS).',
                    'The reporting entity is a going concern.'
                ]
            ],
            'Revenue Recognition' => [
                'description' => 'Revenue is recognized when it is probable that economic benefits will flow to the entity.',
                'details' => [
                    'Service revenue is recognized when services are rendered.',
                    'Interest income is recognized on a time-proportion basis.',
                    'Other income is recognized when received.'
                ]
            ],
            'Expense Recognition' => [
                'description' => 'Expenses are recognized when they are incurred.',
                'details' => [
                    'Operating expenses are recognized in the period in which they are incurred.',
                    'Depreciation is calculated using the straight-line method.',
                    'Prepaid expenses are amortized over their useful life.'
                ]
            ],
            'Cash and Cash Equivalents' => [
                'description' => 'Cash and cash equivalents include cash on hand and deposits with banks.',
                'details' => [
                    'Cash equivalents are short-term, highly liquid investments.',
                    'Bank overdrafts are included in cash and cash equivalents.',
                    'All cash and cash equivalents are held in local currency.'
                ]
            ],
            'Accounts Receivable' => [
                'description' => 'Accounts receivable are stated at their nominal value less provision for doubtful debts.',
                'details' => [
                    'Provision for doubtful debts is based on management assessment.',
                    'Bad debts are written off when identified.',
                    'Interest is charged on overdue accounts.'
                ]
            ],
            'Fixed Assets' => [
                'description' => 'Fixed assets are stated at cost less accumulated depreciation.',
                'details' => [
                    'Depreciation is calculated using the straight-line method.',
                    'Useful lives are reviewed annually.',
                    'Assets are reviewed for impairment annually.'
                ]
            ],
            'Accounts Payable' => [
                'description' => 'Accounts payable are stated at their nominal value.',
                'details' => [
                    'Trade payables are recognized when goods or services are received.',
                    'Accrued expenses are recognized when incurred.',
                    'All payables are expected to be settled within one year.'
                ]
            ]
        ];

        // Get significant transactions and events
        $significantTransactions = $this->getSignificantTransactions($asOfDate, $reportingType, $branchId);

        // Get contingent liabilities
        $contingentLiabilities = $this->getContingentLiabilities($asOfDate, $branchId);

        // Get related party transactions
        $relatedPartyTransactions = $this->getRelatedPartyTransactions($asOfDate, $branchId);

        // Get post-balance sheet events
        $postBalanceSheetEvents = $this->getPostBalanceSheetEvents($asOfDate, $branchId);

        return [
            'accounting_policies' => $accountingPolicies,
            'significant_transactions' => $significantTransactions,
            'contingent_liabilities' => $contingentLiabilities,
            'related_party_transactions' => $relatedPartyTransactions,
            'post_balance_sheet_events' => $postBalanceSheetEvents,
            'as_of_date' => $asOfDate,
            'reporting_type' => $reportingType,
            'branch_id' => $branchId,
            'level_of_detail' => $levelOfDetail
        ];
    }

    private function getSignificantTransactions($asOfDate, $reportingType, $branchId)
    {
        $user = Auth::user();
        $company = $user->company;

        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<=', $asOfDate)
            ->where('gl_transactions.amount', '>=', 1000000); // Transactions >= 1M

        if ($branchId && $branchId != 'all') {
            $query->where('gl_transactions.branch_id', $branchId);
        }

        if ($reportingType === 'cash') {
            $query->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('gl_transactions as gl2')
                    ->whereColumn('gl2.transaction_id', 'gl_transactions.transaction_id')
                    ->whereColumn('gl2.transaction_type', 'gl_transactions.transaction_type')
                    ->whereIn('gl2.chart_account_id', function($bankSubquery) {
                        $bankSubquery->select('chart_account_id')
                            ->from('bank_accounts');
                    });
            });
        }

        return $query->select(
            'gl_transactions.*',
            'chart_accounts.account_name',
            'account_class_groups.name as group_name'
        )
        ->orderBy('gl_transactions.amount', 'desc')
        ->limit(10)
        ->get();
    }

    private function getContingentLiabilities($asOfDate, $branchId)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get actual contingent liabilities from GL transactions
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<=', $asOfDate)
            ->where('gl_transactions.amount', '>=', 1000000); // Significant amounts >= 1M

        if ($branchId && $branchId != 'all') {
            $query->where('gl_transactions.branch_id', $branchId);
        }

        $significantTransactions = $query->select(
            'gl_transactions.*',
            'chart_accounts.account_name',
            'account_class_groups.name as group_name'
        )
        ->orderBy('gl_transactions.amount', 'desc')
        ->limit(5)
        ->get();

        // Calculate total contingent liabilities
        $totalContingentLiabilities = $significantTransactions->sum('amount');

        if ($totalContingentLiabilities > 0) {
            return [
                [
                    'description' => 'Significant Transactions',
                    'amount' => $totalContingentLiabilities,
                    'probability' => 'High',
                    'notes' => 'Significant transactions identified as of the reporting date.'
                ],
                [
                    'description' => 'Legal proceedings',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant legal proceedings as of the reporting date.'
                ],
                [
                    'description' => 'Guarantees provided',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant guarantees provided to third parties.'
                ],
                [
                    'description' => 'Tax contingencies',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant tax contingencies identified.'
                ]
            ];
        } else {
            return [
                [
                    'description' => 'Legal proceedings',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant legal proceedings as of the reporting date.'
                ],
                [
                    'description' => 'Guarantees provided',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant guarantees provided to third parties.'
                ],
                [
                    'description' => 'Tax contingencies',
                    'amount' => 0,
                    'probability' => 'Low',
                    'notes' => 'No significant tax contingencies identified.'
                ]
            ];
        }
    }

    private function getRelatedPartyTransactions($asOfDate, $branchId)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get actual related party transactions from GL transactions
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<=', $asOfDate)
            ->where('gl_transactions.amount', '>=', 500000); // Related party transactions >= 500K

        if ($branchId && $branchId != 'all') {
            $query->where('gl_transactions.branch_id', $branchId);
        }

        $relatedPartyTransactions = $query->select(
            'gl_transactions.*',
            'chart_accounts.account_name',
            'account_class_groups.name as group_name'
        )
        ->orderBy('gl_transactions.amount', 'desc')
        ->limit(3)
        ->get();

        // Calculate totals
        $totalAmount = $relatedPartyTransactions->sum('amount');
        $totalBalance = $relatedPartyTransactions->where('nature', 'debit')->sum('amount') - 
                       $relatedPartyTransactions->where('nature', 'credit')->sum('amount');

        if ($totalAmount > 0) {
            return [
                [
                    'party_name' => 'Related Parties',
                    'transaction_type' => 'Transactions',
                    'amount' => $totalAmount,
                    'balance' => $totalBalance,
                    'notes' => 'Significant related party transactions identified during the period.'
                ],
                [
                    'party_name' => 'Directors',
                    'transaction_type' => 'Remuneration',
                    'amount' => 0,
                    'balance' => 0,
                    'notes' => 'No significant director remuneration during the period.'
                ],
                [
                    'party_name' => 'Subsidiaries',
                    'transaction_type' => 'Intercompany',
                    'amount' => 0,
                    'balance' => 0,
                    'notes' => 'No intercompany transactions during the period.'
                ]
            ];
        } else {
            return [
                [
                    'party_name' => 'Directors',
                    'transaction_type' => 'Remuneration',
                    'amount' => 0,
                    'balance' => 0,
                    'notes' => 'No significant related party transactions during the period.'
                ],
                [
                    'party_name' => 'Subsidiaries',
                    'transaction_type' => 'Intercompany',
                    'amount' => 0,
                    'balance' => 0,
                    'notes' => 'No intercompany transactions during the period.'
                ]
            ];
        }
    }

    private function getPostBalanceSheetEvents($asOfDate, $branchId)
    {
        // This would typically come from a separate table
        // For now, return sample data
        return [
            [
                'event_description' => 'No significant events',
                'date' => null,
                'impact' => 'None',
                'notes' => 'No significant events have occurred between the reporting date and the date of authorization of these financial statements.'
            ]
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', 'all');
        $levelOfDetail = $request->get('level_of_detail', 'summary');
        $exportType = $request->get('export_type', 'pdf');

        // Get accounting notes data
        $accountingNotesData = $this->getAccountingNotesData($asOfDate, $reportingType, $branchId, $levelOfDetail);

        if ($exportType === 'pdf') {
            return $this->exportPdf($accountingNotesData, $company, $asOfDate, $reportingType);
        } else {
            return $this->exportExcel($accountingNotesData, $company, $asOfDate, $reportingType);
        }
    }

    private function exportPdf($accountingNotesData, $company, $asOfDate, $reportingType)
    {
        $pdf = Pdf::loadView('accounting.reports.accounting-notes.pdf', [
            'accountingNotesData' => $accountingNotesData,
            'company' => $company,
            'asOfDate' => $asOfDate,
            'reportingType' => $reportingType
        ]);

        $filename = 'accounting_notes_' . $asOfDate . '_' . $reportingType . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($accountingNotesData, $company, $asOfDate, $reportingType)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', $company->name ?? 'SmartFinance');
        $sheet->setCellValue('A2', 'ACCOUNTING NOTES');
        $sheet->setCellValue('A3', 'As at: ' . Carbon::parse($asOfDate)->format('M d, Y'));
        $sheet->setCellValue('A4', 'Basis: ' . ucfirst($reportingType));

        $row = 6;

        // Accounting Policies
        $sheet->setCellValue('A' . $row, '1. SIGNIFICANT ACCOUNTING POLICIES');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        foreach ($accountingNotesData['accounting_policies'] as $policy => $details) {
            $sheet->setCellValue('A' . $row, $policy);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue('A' . $row, $details['description']);
            $row++;

            foreach ($details['details'] as $detail) {
                $sheet->setCellValue('B' . $row, '• ' . $detail);
                $row++;
            }
            $row++;
        }

        // Significant Transactions
        $sheet->setCellValue('A' . $row, '2. SIGNIFICANT TRANSACTIONS');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        if (count($accountingNotesData['significant_transactions']) > 0) {
            foreach ($accountingNotesData['significant_transactions'] as $transaction) {
                $sheet->setCellValue('A' . $row, Carbon::parse($transaction->date)->format('d/m/Y'));
                $sheet->setCellValue('B' . $row, $transaction->account_name);
                $sheet->setCellValue('C' . $row, number_format($transaction->amount, 2));
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No significant transactions during the period.');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'accounting_notes_' . $asOfDate . '_' . $reportingType . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'accounting_notes');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }
}

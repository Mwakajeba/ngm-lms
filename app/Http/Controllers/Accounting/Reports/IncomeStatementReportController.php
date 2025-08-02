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

class IncomeStatementReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', 'all');
        $layout = $request->get('layout', 'standard');

        // Get branches for filter
        $branches = $company->branches;

        // Get income statement data
        $incomeStatementData = $this->getIncomeStatementData($startDate, $endDate, $reportingType, $branchId, $layout);

        return view('accounting.reports.income-statement.index', compact(
            'incomeStatementData',
            'startDate',
            'endDate',
            'reportingType',
            'branchId',
            'layout',
            'branches',
            'user'
        ));
    }

    private function getIncomeStatementData($startDate, $endDate, $reportingType, $branchId, $layout)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get current year data
        $currentYearData = $this->getYearData($startDate, $endDate, $reportingType, $branchId);
        
        // Get previous year data for comparison
        $previousYearStart = Carbon::parse($startDate)->subYear()->format('Y-m-d');
        $previousYearEnd = Carbon::parse($endDate)->subYear()->format('Y-m-d');
        $previousYearData = $this->getYearData($previousYearStart, $previousYearEnd, $reportingType, $branchId);

        return [
            'data' => [
                'revenues' => $currentYearData['revenues'],
                'expenses' => $currentYearData['expenses'],
                'revenues_previous' => $previousYearData['revenues'],
                'expenses_previous' => $previousYearData['expenses'],
                'total_revenue' => $currentYearData['total_revenue'],
                'total_expenses' => $currentYearData['total_expenses'],
                'total_revenue_previous' => $previousYearData['total_revenue'],
                'total_expenses_previous' => $previousYearData['total_expenses'],
                'profit_loss' => $currentYearData['total_revenue'] - $currentYearData['total_expenses'],
                'profit_loss_previous' => $previousYearData['total_revenue'] - $previousYearData['total_expenses']
            ],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reporting_type' => $reportingType,
            'branch_id' => $branchId,
            'layout' => $layout
        ];
    }

    private function getYearData($startDate, $endDate, $reportingType, $branchId)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build the base query for income accounts
        $incomeQuery = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereBetween('gl_transactions.date', [$startDate, $endDate])
            ->whereIn('account_class.name', ['income', 'revenue']);

        // Build the base query for expense accounts
        $expenseQuery = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereBetween('gl_transactions.date', [$startDate, $endDate])
            ->whereIn('account_class.name', ['expenses', 'expense']);

        // Add branch filter if specified
        if ($branchId && $branchId != 'all') {
            $incomeQuery->where('gl_transactions.branch_id', $branchId);
            $expenseQuery->where('gl_transactions.branch_id', $branchId);
        }

        // Add reporting type filter (cash vs accrual)
        if ($reportingType === 'cash') {
            // For cash basis, select all GL transactions that are part of the same transaction when any bank account is involved
            $incomeQuery->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('gl_transactions as gl2')
                    ->whereColumn('gl2.transaction_id', 'gl_transactions.transaction_id')
                    ->whereColumn('gl2.transaction_type', 'gl_transactions.transaction_type')
                    ->whereIn('gl2.chart_account_id', function($bankSubquery) {
                        $bankSubquery->select('chart_account_id')
                            ->from('bank_accounts');
                    });
            });

            $expenseQuery->whereExists(function ($subquery) {
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

        // Select fields for income
        $incomeQuery->select(
            'chart_accounts.id as account_id',
            'chart_accounts.account_name',
            'chart_accounts.account_code',
            'account_class.name as class_name',
            'account_class_groups.name as group_name',
            DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total'),
            DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total')
        )
        ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code', 'account_class.name', 'account_class_groups.name');

        // Select fields for expenses
        $expenseQuery->select(
            'chart_accounts.id as account_id',
            'chart_accounts.account_name',
            'chart_accounts.account_code',
            'account_class.name as class_name',
            'account_class_groups.name as group_name',
            DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
            DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
        )
        ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code', 'account_class.name', 'account_class_groups.name');

        $incomeData = $incomeQuery->get();
        $expenseData = $expenseQuery->get();

        // Process income data
        $incomeData = $incomeData->map(function ($item) {
            $item->balance = $item->credit_total - $item->debit_total; // Income: credit increases, debit decreases
            return $item;
        });

        // Process expense data
        $expenseData = $expenseData->map(function ($item) {
            $item->balance = $item->debit_total - $item->credit_total; // Expenses: debit increases, credit decreases
            return $item;
        });

        // Group by account class groups
        $chartAccountsRevenues = [];
        $chartAccountsExpenses = [];

        foreach ($incomeData as $account) {
            if ($account->balance != 0) {
                $chartAccountsRevenues[$account->group_name][] = [
                    'account_id' => $account->account_id,
                    'account' => $account->account_name,
                    'account_code' => $account->account_code,
                    'sum' => $account->balance
                ];
            }
        }

        foreach ($expenseData as $account) {
            if ($account->balance != 0) {
                $chartAccountsExpenses[$account->group_name][] = [
                    'account_id' => $account->account_id,
                    'account' => $account->account_name,
                    'account_code' => $account->account_code,
                    'sum' => $account->balance
                ];
            }
        }

        // Calculate totals
        $totalRevenue = collect($chartAccountsRevenues)->flatten(1)->sum('sum');
        $totalExpenses = collect($chartAccountsExpenses)->flatten(1)->sum('sum');

        return [
            'revenues' => $chartAccountsRevenues,
            'expenses' => $chartAccountsExpenses,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', 'all');
        $layout = $request->get('layout', 'standard');
        $exportType = $request->get('export_type', 'pdf');

        // Get income statement data
        $incomeStatementData = $this->getIncomeStatementData($startDate, $endDate, $reportingType, $branchId, $layout);

        if ($exportType === 'pdf') {
            return $this->exportPdf($incomeStatementData, $company, $startDate, $endDate, $reportingType);
        } else {
            return $this->exportExcel($incomeStatementData, $company, $startDate, $endDate, $reportingType);
        }
    }

    private function exportPdf($incomeStatementData, $company, $startDate, $endDate, $reportingType)
    {
        $pdf = Pdf::loadView('accounting.reports.income-statement.pdf', [
            'incomeStatementData' => $incomeStatementData,
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportingType' => $reportingType
        ]);

        $filename = 'income_statement_' . $startDate . '_to_' . $endDate . '_' . $reportingType . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($incomeStatementData, $company, $startDate, $endDate, $reportingType)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', $company->name ?? 'SmartFinance');
        $sheet->setCellValue('A2', 'INCOME STATEMENT');
        $sheet->setCellValue('A3', 'Period: ' . Carbon::parse($startDate)->format('M d, Y') . ' to ' . Carbon::parse($endDate)->format('M d, Y'));
        $sheet->setCellValue('A4', 'Basis: ' . ucfirst($reportingType));

        $row = 6;

        // Revenue Section
        $sheet->setCellValue('A' . $row, 'INCOME');
        $row++;

        foreach ($incomeStatementData['data']['revenues'] as $groupName => $accounts) {
            $sheet->setCellValue('A' . $row, $groupName);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;

            foreach ($accounts as $account) {
                $sheet->setCellValue('A' . $row, $account['account']);
                $sheet->setCellValue('B' . $row, number_format($account['sum'], 2));
                $row++;
            }
        }

        $sheet->setCellValue('A' . $row, 'TOTAL INCOME');
        $sheet->setCellValue('B' . $row, number_format($incomeStatementData['data']['total_revenue'], 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row += 2;

        // Expense Section
        $sheet->setCellValue('A' . $row, 'LESS EXPENSES');
        $row++;

        foreach ($incomeStatementData['data']['expenses'] as $groupName => $accounts) {
            $sheet->setCellValue('A' . $row, $groupName);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;

            foreach ($accounts as $account) {
                $sheet->setCellValue('A' . $row, $account['account']);
                $sheet->setCellValue('B' . $row, number_format(abs($account['sum']), 2));
                $row++;
            }
        }

        $sheet->setCellValue('A' . $row, 'TOTAL EXPENSES');
        $sheet->setCellValue('B' . $row, number_format(abs($incomeStatementData['data']['total_expenses']), 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'PROFIT / LOSS');
        $sheet->setCellValue('B' . $row, number_format($incomeStatementData['data']['profit_loss'], 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);

        // Auto-size columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);

        $filename = 'income_statement_' . $startDate . '_to_' . $endDate . '_' . $reportingType . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'income_statement');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }
}

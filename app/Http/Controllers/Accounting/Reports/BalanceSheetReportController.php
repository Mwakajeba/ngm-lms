<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BalanceSheetReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;
        
        // Get branches visible to the user: only assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // Set default values
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        
        // Determine selected branch: allow 'all' only if user has >1 assigned branches
        $branchParam = $request->get('branch_id');
        if ($branches->count() > 1 && $branchParam === 'all') {
            $branchId = 'all';
        } else {
            $branchId = $branchParam ?: ($branches->first()->id ?? null);
        }
        $levelOfDetail = $request->get('level_of_detail', 'summary');

        // Get comparative columns from request
        $comparativeColumns = $request->get('comparative_columns', []);

        // Get balance sheet data
        $balanceSheetData = $this->getBalanceSheetData($asOfDate, $reportingType, $branchId, $levelOfDetail, $comparativeColumns);

        return view('accounting.reports.balance-sheet.index', compact(
            'balanceSheetData',
            'branches',
            'asOfDate',
            'reportingType',
            'branchId',
            'levelOfDetail',
            'comparativeColumns',
            'user'
        ));
    }

    private function getBalanceSheetData($asOfDate, $reportingType, $branchId, $levelOfDetail, $comparativeColumns = [])
    {
        $user = Auth::user();
        $company = $user->company;

        // Get current period data using the comprehensive function
        $currentData = $this->getFinancialReportData($asOfDate, $reportingType, $branchId, $levelOfDetail);

        // Get comparative period data
        $comparativeData = [];
        foreach ($comparativeColumns as $column) {
            if (!empty($column['date']) && !empty($column['name'])) {
                $comparativeData[$column['name']] = $this->getFinancialReportData($column['date'], $reportingType, $branchId, $levelOfDetail);
            }
        }

        // Organize current data by account class
        $organizedCurrentData = $this->organizeFinancialDataByClass($currentData);

        // Organize comparative data by account class
        $organizedComparativeData = [];
        foreach ($comparativeData as $columnName => $data) {
            $organizedComparativeData[$columnName] = $this->organizeFinancialDataByClass($data);
        }

        // Log the data being passed to view for debugging
        \Log::info('Balance Sheet Data for View', [
            'profit_loss' => $currentData['profitLoss'],
            'current_assets_count' => $organizedCurrentData['assets']->count(),
            'current_liabilities_count' => $organizedCurrentData['liabilities']->count(),
            'current_equity_count' => $organizedCurrentData['equity']->count()
        ]);

        return [
            'current' => $organizedCurrentData,
            'comparative' => $organizedComparativeData,
            'profit_loss' => $currentData['profitLoss'],
            'filters' => [
                'as_of_date' => $asOfDate,
                'reporting_type' => $reportingType,
                'branch_id' => $branchId,
                'level_of_detail' => $levelOfDetail
            ]
        ];
    }

    private function getFinancialReportData($asOfDate, $reportingType, $branchId, $levelOfDetail)
    {
        $company = auth()->user()->company;
        
        // Build the base query with all necessary joins
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<=', $asOfDate);

        // Add branch filter for assigned branches / all assigned
        $user = auth()->user();
        $assignedBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($branchId === 'all') {
            // limit to assigned branches only
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        } elseif ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        } else {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        }

        // Add reporting type filter (cash vs accrual)
        if ($reportingType === 'cash') {
            // For cash basis, select all GL transactions that are part of the same transaction when any bank account is involved
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
        
        // Get all chart accounts with their balances grouped by account class
        $chartAccountsData = $query->select(
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'chart_accounts.account_code as account_code',
                'account_class.name as class_name',
                'account_class_groups.name as group_name',
                'account_class_groups.id as group_id',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code', 'account_class.name', 'account_class_groups.name', 'account_class_groups.id')
            ->get();
            
        // Group by account class and calculate balances
        $chartAccountsAssets = [];
        $chartAccountsLiabilities = [];
        $chartAccountsEquitys = [];
        $chartAccountsRevenues = [];
        $chartAccountsExpense = [];
        
        foreach ($chartAccountsData as $account) {
            // Calculate balance based on account class
            $balance = 0;
            
            // Categorize based on account class
            switch (strtolower($account->class_name)) {
                case 'assets':
                    $balance = $account->debit_total - $account->credit_total; // Assets: debit increases
                    $chartAccountsAssets[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code,
                        'group_id' => $account->group_id,
                        'group_name' => $account->group_name,
                        'debit_total' => $account->debit_total,
                        'credit_total' => $account->credit_total,
                        'sum' => $balance
                    ];
                    break;
                case 'liabilities':
                    $balance = $account->credit_total - $account->debit_total; // Liabilities: credit increases
                    $chartAccountsLiabilities[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code,
                        'group_id' => $account->group_id,
                        'group_name' => $account->group_name,
                        'debit_total' => $account->debit_total,
                        'credit_total' => $account->credit_total,
                        'sum' => $balance
                    ];
                    break;
                case 'equity':
                    $balance = $account->credit_total - $account->debit_total; // Equity: credit increases
                    $chartAccountsEquitys[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code,
                        'group_id' => $account->group_id,
                        'group_name' => $account->group_name,
                        'debit_total' => $account->debit_total,
                        'credit_total' => $account->credit_total,
                        'sum' => $balance
                    ];
                    break;
                case 'income':
                case 'revenue':
                    $balance = $account->credit_total - $account->debit_total; // Revenue: credit increases
                    $chartAccountsRevenues[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code,
                        'group_id' => $account->group_id,
                        'group_name' => $account->group_name,
                        'debit_total' => $account->debit_total,
                        'credit_total' => $account->credit_total,
                        'sum' => $balance
                    ];
                    break;
                case 'expenses':
                case 'expense':
                    $balance = $account->debit_total - $account->credit_total; // Expenses: debit increases
                    $chartAccountsExpense[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'account_code' => $account->account_code,
                        'group_id' => $account->group_id,
                        'group_name' => $account->group_name,
                        'debit_total' => $account->debit_total,
                        'credit_total' => $account->credit_total,
                        'sum' => $balance
                    ];
                    break;
            }
        }
        
        // Calculate profit/loss
        $sumRevenue = collect($chartAccountsRevenues)->flatten(1)->sum('sum');
        $sumExpense = collect($chartAccountsExpense)->flatten(1)->sum('sum');
        $profitLoss = $sumRevenue - $sumExpense;
        
        return [
            'chartAccountsAssets' => $chartAccountsAssets,
            'chartAccountsLiabilities' => $chartAccountsLiabilities,
            'chartAccountsEquitys' => $chartAccountsEquitys,
            'chartAccountsRevenues' => $chartAccountsRevenues,
            'chartAccountsExpense' => $chartAccountsExpense,
            'profitLoss' => $profitLoss
        ];
    }

    private function organizeFinancialDataByClass($financialData)
    {
        $organized = [
            'assets' => collect(),
            'liabilities' => collect(),
            'equity' => collect()
        ];

        // Organize Assets
        foreach ($financialData['chartAccountsAssets'] as $groupName => $accounts) {
            foreach ($accounts as $account) {
                $organized['assets']->push((object)[
                    'account_id' => $account['account_id'],
                    'account_name' => $account['account'],
                    'account_code' => $account['account_code'],
                    'group_name' => $account['group_name'],
                    'group_id' => $account['group_id'],
                    'debit_total' => $account['debit_total'],
                    'credit_total' => $account['credit_total'],
                    'class_name' => 'assets'
                ]);
            }
        }

        // Organize Liabilities
        foreach ($financialData['chartAccountsLiabilities'] as $groupName => $accounts) {
            foreach ($accounts as $account) {
                $organized['liabilities']->push((object)[
                    'account_id' => $account['account_id'],
                    'account_name' => $account['account'],
                    'account_code' => $account['account_code'],
                    'group_name' => $account['group_name'],
                    'group_id' => $account['group_id'],
                    'debit_total' => $account['debit_total'],
                    'credit_total' => $account['credit_total'],
                    'class_name' => 'liabilities'
                ]);
            }
        }

        // Organize Equity
        foreach ($financialData['chartAccountsEquitys'] as $groupName => $accounts) {
            foreach ($accounts as $account) {
                $organized['equity']->push((object)[
                    'account_id' => $account['account_id'],
                    'account_name' => $account['account'],
                    'account_code' => $account['account_code'],
                    'group_name' => $account['group_name'],
                    'group_id' => $account['group_id'],
                    'debit_total' => $account['debit_total'],
                    'credit_total' => $account['credit_total'],
                    'class_name' => 'equity'
                ]);
            }
        }

        return $organized;
    }

    private function getPeriodData($asOfDate, $reportingType, $branchId, $levelOfDetail)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build the base query
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<=', $asOfDate);

        // Add branch filter for assigned branches / all assigned
        $user = auth()->user();
        $assignedBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($branchId === 'all') {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        } elseif ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        } else {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        }

        // Add reporting type filter (cash vs accrual)
        if ($reportingType === 'cash') {
            // For cash basis, select all GL transactions that are part of the same transaction when any bank account is involved
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

        // Select fields based on level of detail
        if ($levelOfDetail === 'detailed') {
            $query->select(
                'chart_accounts.id as account_id',
                'chart_accounts.account_name',
                'chart_accounts.account_code',
                'account_class.name as class_name',
                'account_class_groups.name as group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code', 'account_class.name', 'account_class_groups.name');
        } else {
            // Summary level - group by account class groups
            $query->select(
                'account_class_groups.id as group_id',
                'account_class_groups.name as group_name',
                'account_class.name as class_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('account_class_groups.id', 'account_class_groups.name', 'account_class.name');
        }

        return $query->get();
    }

    private function organizeDataByClass($data)
    {
        $organized = [
            'assets' => collect(),
            'liabilities' => collect(),
            'equity' => collect()
        ];

        foreach ($data as $item) {
            $class_name = strtolower($item->class_name);
            
            switch ($class_name) {
                case 'assets':
                case 'asset':
                    $organized['assets']->push($item);
                    break;
                case 'liabilities':
                case 'liability':
                    $organized['liabilities']->push($item);
                    break;
                case 'equity':
                    $organized['equity']->push($item);
                    break;
            }
        }

        return $organized;
    }

    private function calculateProfitLoss($asOfDate, $reportingType, $branchId)
    {
        $user = Auth::user();
        $company = $user->company;

        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<=', $asOfDate)
            ->whereIn('account_class.name', ['income', 'revenue', 'expenses', 'expense']);

        $user = Auth::user();
        $assignedBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($branchId === 'all') {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        } elseif ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        } else {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
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

        $revenueExpenseData = $query->select(
                'account_class.name as class_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total')
            )
            ->groupBy('account_class.name')
            ->get();

        $totalRevenue = 0;
        $totalExpenses = 0;

        foreach ($revenueExpenseData as $item) {
            $class_name = strtolower($item->class_name);
            
            // For income/revenue accounts: credit increases revenue, debit decreases
            if (in_array($class_name, ['income', 'revenue'])) {
                $totalRevenue += ($item->credit_total - $item->debit_total);
            }
            // For expense accounts: debit increases expenses, credit decreases
            elseif (in_array($class_name, ['expenses', 'expense'])) {
                $totalExpenses += ($item->debit_total - $item->credit_total);
            }
        }

        $profitLoss = $totalRevenue - $totalExpenses;
        
        // Log for debugging
        \Log::info('P&L Calculation', [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'profit_loss' => $profitLoss,
            'revenue_expense_data' => $revenueExpenseData->toArray()
        ]);

        return $profitLoss;
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', $user->branch_id);
        $levelOfDetail = $request->get('level_of_detail', 'summary');
        $exportType = $request->get('export_type', 'pdf');

        // Get comparative columns from request
        $comparativeColumns = $request->get('comparative_columns', []);

        // Get balance sheet data
        $balanceSheetData = $this->getBalanceSheetData($asOfDate, $reportingType, $branchId, $levelOfDetail, $comparativeColumns);

        if ($exportType === 'excel') {
            return $this->exportExcel($balanceSheetData, $company, $asOfDate, $reportingType);
        } else {
            return $this->exportPdf($balanceSheetData, $company, $asOfDate, $reportingType);
        }
    }

    private function exportPdf($balanceSheetData, $company, $asOfDate, $reportingType)
    {
        $user = Auth::user();
        
        // Get branches for header
        $branches = [];
        if ($user->hasRole('admin')) {
            $branches = DB::table('branches')
                ->where('company_id', $company->id)
                ->select('id', 'name')
                ->get();
        }
        
        // Generate PDF
        $pdf = \PDF::loadView('accounting.reports.balance-sheet.pdf', compact(
            'balanceSheetData', 
            'company', 
            'branches',
            'asOfDate',
            'reportingType'
        ));
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'balance_sheet_' . $asOfDate . '_' . $reportingType . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($balanceSheetData, $company, $asOfDate, $reportingType)
    {
        $spreadsheet = new Spreadsheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator($company->name ?? 'SmartFinance')
            ->setLastModifiedBy($company->name ?? 'SmartFinance')
            ->setTitle('Balance Sheet Report')
            ->setSubject('Balance Sheet as of ' . Carbon::parse($asOfDate)->format('F d, Y'))
            ->setDescription('Balance Sheet Report generated on ' . now()->format('F d, Y \a\t g:i A'));

        // Create worksheet
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle('Balance Sheet');

        // Set headers
        $worksheet->setCellValue('A1', $company->name ?? 'SmartFinance');
        $worksheet->setCellValue('A2', 'BALANCE SHEET');
        $worksheet->setCellValue('A3', 'As of ' . Carbon::parse($asOfDate)->format('F d, Y'));
        $worksheet->setCellValue('A4', 'Reporting Type: ' . ucfirst($reportingType) . ' Basis');
        $worksheet->setCellValue('A5', 'Generated: ' . now()->format('F d, Y \a\t g:i A'));

        // Set column headers
        $col = 'A';
        $row = 7;
        $worksheet->setCellValue($col . $row, 'Account/Group');
        $col++;
        $worksheet->setCellValue($col . $row, 'Current Period');
        
        // Add comparative columns if they exist
        if (isset($balanceSheetData['comparative']) && !empty($balanceSheetData['comparative'])) {
            foreach ($balanceSheetData['comparative'] as $columnName => $data) {
                $col++;
                $worksheet->setCellValue($col . $row, $columnName);
            }
        }

        // Style headers
        $worksheet->getStyle('A7:' . $col . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A7:' . $col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');

        $row++;

        // Add Assets section
        $worksheet->setCellValue('A' . $row, 'ASSETS');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('28A745');
        $worksheet->getStyle('A' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $row++;

        $totalAssets = 0;
        foreach ($balanceSheetData['current']['assets'] as $asset) {
            $currentAmount = $asset->debit_total - $asset->credit_total;
            $totalAssets += $currentAmount;

            $col = 'A';
            $worksheet->setCellValue($col . $row, $balanceSheetData['filters']['level_of_detail'] === 'detailed' ? $asset->account_name : $asset->group_name);
            $col++;
            $worksheet->setCellValue($col . $row, $currentAmount);
            $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // Add comparative data if available
            if (isset($balanceSheetData['comparative']) && !empty($balanceSheetData['comparative'])) {
                foreach ($balanceSheetData['comparative'] as $columnName => $comparativeData) {
                    $col++;
                    $comparativeAsset = collect($comparativeData['assets'] ?? [])->first(function($item) use ($asset) {
                        return $balanceSheetData['filters']['level_of_detail'] === 'detailed' 
                            ? $item->account_id == $asset->account_id
                            : $item->group_id == $asset->group_id;
                    });
                    $comparativeAmount = $comparativeAsset ? ($comparativeAsset->debit_total - $comparativeAsset->credit_total) : 0;
                    $worksheet->setCellValue($col . $row, $comparativeAmount);
                    $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                }
            }
            $row++;
        }

        // Add total assets
        $col = 'A';
        $worksheet->setCellValue($col . $row, 'TOTAL ASSETS');
        $worksheet->getStyle($col . $row)->getFont()->setBold(true);
        $col++;
        $worksheet->setCellValue($col . $row, $totalAssets);
        $worksheet->getStyle($col . $row)->getFont()->setBold(true);
        $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        // Add Liabilities section
        $worksheet->setCellValue('A' . $row, 'LIABILITIES');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFC107');
        $row++;

        $totalLiabilities = 0;
        foreach ($balanceSheetData['current']['liabilities'] as $liability) {
            $currentAmount = $liability->credit_total - $liability->debit_total;
            $totalLiabilities += $currentAmount;

            $col = 'A';
            $worksheet->setCellValue($col . $row, $balanceSheetData['filters']['level_of_detail'] === 'detailed' ? $liability->account_name : $liability->group_name);
            $col++;
            $worksheet->setCellValue($col . $row, $currentAmount);
            $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // Add comparative data if available
            if (isset($balanceSheetData['comparative']) && !empty($balanceSheetData['comparative'])) {
                foreach ($balanceSheetData['comparative'] as $columnName => $comparativeData) {
                    $col++;
                    $comparativeLiability = collect($comparativeData['liabilities'] ?? [])->first(function($item) use ($liability) {
                        return $balanceSheetData['filters']['level_of_detail'] === 'detailed' 
                            ? $item->account_id == $liability->account_id
                            : $item->group_id == $liability->group_id;
                    });
                    $comparativeAmount = $comparativeLiability ? ($comparativeLiability->credit_total - $comparativeLiability->debit_total) : 0;
                    $worksheet->setCellValue($col . $row, $comparativeAmount);
                    $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                }
            }
            $row++;
        }

        // Add total liabilities
        $col = 'A';
        $worksheet->setCellValue($col . $row, 'TOTAL LIABILITIES');
        $worksheet->getStyle($col . $row)->getFont()->setBold(true);
        $col++;
        $worksheet->setCellValue($col . $row, $totalLiabilities);
        $worksheet->getStyle($col . $row)->getFont()->setBold(true);
        $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        // Add Equity section
        $row++;
        $worksheet->setCellValue('A' . $row, 'EQUITY');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        
        // Add headers
        $worksheet->setCellValue('A' . $row, 'Account');
        if ($balanceSheetData['filters']['level_of_detail'] === 'detailed') {
            $worksheet->setCellValue('B' . $row, 'Code');
            $worksheet->setCellValue('C' . $row, 'Current Period');
            $col = 'D';
        } else {
            $worksheet->setCellValue('B' . $row, 'Current Period');
            $col = 'C';
        }
        
        // Add comparative columns if they exist
        if (isset($balanceSheetData['comparative']) && !empty($balanceSheetData['comparative'])) {
            foreach ($balanceSheetData['comparative'] as $columnName => $data) {
                $worksheet->setCellValue($col . $row, $columnName);
                $col++;
            }
        }
        $worksheet->getStyle('A' . $row . ':' . $col . $row)->getFont()->setBold(true);
        $row++;
        
        // Add equity accounts
        foreach ($balanceSheetData['current']['equity'] as $item) {
            $currentBalance = $item->credit_total - $item->debit_total;
            
            $worksheet->setCellValue('A' . $row, $balanceSheetData['filters']['level_of_detail'] === 'detailed' ? $item->account_name : $item->group_name);
            
            if ($balanceSheetData['filters']['level_of_detail'] === 'detailed') {
                $worksheet->setCellValue('B' . $row, $item->account_code);
                $worksheet->setCellValue('C' . $row, $currentBalance);
                $col = 'D';
            } else {
                $worksheet->setCellValue('B' . $row, $currentBalance);
                $col = 'C';
            }
            
            // Add comparative data if available
            if (isset($balanceSheetData['comparative']) && !empty($balanceSheetData['comparative'])) {
                foreach ($balanceSheetData['comparative'] as $columnName => $comparativeData) {
                    $compData = collect($comparativeData['equity'] ?? [])->first(function($comp) use ($item) {
                        return $balanceSheetData['filters']['level_of_detail'] === 'detailed' 
                            ? $comp->account_id == $item->account_id
                            : $comp->group_id == $item->group_id;
                    });
                    $compBalance = $compData ? ($compData->credit_total - $compData->debit_total) : 0;
                    $worksheet->setCellValue($col . $row, $compBalance);
                    $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                    $col++;
                }
            }
            $row++;
        }
        
        // Add Profit & Loss row
        $worksheet->setCellValue('A' . $row, 'Profit & Loss');
        if ($balanceSheetData['filters']['level_of_detail'] === 'detailed') {
            $worksheet->setCellValue('C' . $row, $balanceSheetData['profit_loss']);
            $col = 'D';
        } else {
            $worksheet->setCellValue('B' . $row, $balanceSheetData['profit_loss']);
            $col = 'C';
        }
        
        // Add comparative P&L data
        // Add comparative P&L data if available
        if (isset($balanceSheetData['comparative']) && !empty($balanceSheetData['comparative'])) {
            foreach ($balanceSheetData['comparative'] as $columnName => $comparativeData) {
                $compIncome = collect($comparativeData['revenues'] ?? [])->flatten(1)->sum('sum') + 
                              collect($comparativeData['expenses'] ?? [])->flatten(1)->sum(function($item) {
                                  return $item['sum'] * -1; // Expenses are negative
                              });
                
                $compPnL = $compIncome; // This is already the P&L calculation
                $worksheet->setCellValue($col . $row, $compPnL);
                $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $col++;
            }
        }
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        
        // Add Total Equity
        $totalEquity = $balanceSheetData['current']['equity']->sum(function($item) {
            return $item->credit_total - $item->debit_total;
        }) + $balanceSheetData['profit_loss'];
        
        $worksheet->setCellValue('A' . $row, 'Total Equity');
        if ($balanceSheetData['filters']['level_of_detail'] === 'detailed') {
            $worksheet->setCellValue('C' . $row, $totalEquity);
            $col = 'D';
        } else {
            $worksheet->setCellValue('B' . $row, $totalEquity);
            $col = 'C';
        }
        
        // Add comparative total equity
        // Add comparative total equity if available
        if (isset($balanceSheetData['comparative']) && !empty($balanceSheetData['comparative'])) {
            foreach ($balanceSheetData['comparative'] as $columnName => $comparativeData) {
                $compEquity = collect($comparativeData['equity'] ?? [])->flatten(1)->sum('sum');
                $compIncome = collect($comparativeData['revenues'] ?? [])->flatten(1)->sum('sum');
                $compExpenses = collect($comparativeData['expenses'] ?? [])->flatten(1)->sum('sum');
                $compTotalEquity = $compEquity + ($compIncome - $compExpenses);
                $worksheet->setCellValue($col . $row, $compTotalEquity);
                $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $col++;
            }
        }
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        // Add Liabilities + Equity Summary Section
        $row += 2;
        $worksheet->setCellValue('A' . $row, 'TOTAL LIABILITIES + EQUITY BREAKDOWN');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('17A2B8');
        $worksheet->getStyle('A' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $row++;

        // Calculate totals for summary
        $totalAssets = $balanceSheetData['current']['assets']->sum(function($item) {
            return $item->debit_total - $item->credit_total;
        });
        $totalLiabilities = $balanceSheetData['current']['liabilities']->sum(function($item) {
            return $item->credit_total - $item->debit_total;
        });
        $baseEquity = $balanceSheetData['current']['equity']->sum(function($item) {
            return $item->credit_total - $item->debit_total;
        });
        $totalPnL = $balanceSheetData['profit_loss'];
        $totalEquity = $baseEquity + $totalPnL;
        $totalLiabilitiesPlusEquity = $totalLiabilities + $totalEquity;

        // Add summary table headers
        $worksheet->setCellValue('A' . $row, 'Component');
        $worksheet->setCellValue('B' . $row, 'Amount');
        $worksheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row++;

        // Add Total Liabilities row
        $worksheet->setCellValue('A' . $row, 'Total Liabilities');
        $worksheet->setCellValue('B' . $row, $totalLiabilities);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        // Add Total Equity row
        $worksheet->setCellValue('A' . $row, 'Total Equity (including P&L)');
        $worksheet->setCellValue('B' . $row, $totalEquity);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        // Add Total Liabilities + Equity row
        $worksheet->setCellValue('A' . $row, 'TOTAL LIABILITIES + EQUITY');
        $worksheet->setCellValue('B' . $row, $totalLiabilitiesPlusEquity);
        $worksheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        // Add Balance Sheet Summary
        $row += 2;
        $worksheet->setCellValue('A' . $row, 'BALANCE SHEET SUMMARY');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('6F42C1');
        $worksheet->getStyle('A' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $row++;

        // Add summary comparison
        $worksheet->setCellValue('A' . $row, 'Total Assets:');
        $worksheet->setCellValue('B' . $row, $totalAssets);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        $worksheet->setCellValue('A' . $row, 'Total Liabilities + Equity:');
        $worksheet->setCellValue('B' . $row, $totalLiabilitiesPlusEquity);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        // Add balance check
        $row++;
        $worksheet->setCellValue('A' . $row, 'BALANCE CHECK:');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        
        $difference = $totalAssets - $totalLiabilitiesPlusEquity;
        $isBalanced = abs($difference) < 0.01;
        
        if ($isBalanced) {
            $worksheet->setCellValue('A' . $row, '✅ Balance sheet is balanced');
            $worksheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('28A745');
        } else {
            $worksheet->setCellValue('A' . $row, '⚠️ Balance sheet is not balanced. Difference: ' . number_format($difference, 2));
            $worksheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('DC3545');
        }

        // Auto-size columns
        foreach (range('A', $worksheet->getHighestColumn()) as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'balance_sheet_' . $asOfDate . '_' . $reportingType . '.xlsx';
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

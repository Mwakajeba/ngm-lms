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
        
        // Get branches for admin users
        $branches = [];
        if ($user->hasRole('admin')) {
            $branches = DB::table('branches')
                ->where('company_id', $company->id)
                ->select('id', 'name')
                ->get();
        }

        // Set default values
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', $user->branch_id);
        $comparativeYears = $request->get('comparative_years', 1);
        $levelOfDetail = $request->get('level_of_detail', 'summary');

        // Get balance sheet data
        $balanceSheetData = $this->getBalanceSheetData($asOfDate, $reportingType, $branchId, $comparativeYears, $levelOfDetail);

        return view('accounting.reports.balance-sheet.index', compact(
            'balanceSheetData',
            'branches',
            'asOfDate',
            'reportingType',
            'branchId',
            'comparativeYears',
            'levelOfDetail',
            'user'
        ));
    }

    private function getBalanceSheetData($asOfDate, $reportingType, $branchId, $comparativeYears, $levelOfDetail)
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

        // Add branch filter if specified
        if ($branchId && $branchId != 'all') {
            $query->where('gl_transactions.branch_id', $branchId);
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

        $currentData = $query->get();

        // Get comparative data for previous years
        $comparativeData = [];
        for ($i = 1; $i <= $comparativeYears; $i++) {
            $comparativeDate = Carbon::parse($asOfDate)->subYears($i)->format('Y-m-d');
            
            $comparativeQuery = DB::table('gl_transactions')
                ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
                ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
                ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
                ->where('account_class_groups.company_id', $company->id)
                ->where('gl_transactions.date', '<=', $comparativeDate);

            if ($branchId && $branchId != 'all') {
                $comparativeQuery->where('gl_transactions.branch_id', $branchId);
            }

            if ($reportingType === 'cash') {
                // For cash basis, select all GL transactions that are part of the same transaction when any bank account is involved
                $comparativeQuery->whereExists(function ($subquery) {
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

            if ($levelOfDetail === 'detailed') {
                $comparativeQuery->select(
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
                $comparativeQuery->select(
                    'account_class_groups.id as group_id',
                    'account_class_groups.name as group_name',
                    'account_class.name as class_name',
                    DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                    DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
                )
                ->groupBy('account_class_groups.id', 'account_class_groups.name', 'account_class.name');
            }

            $comparativeData[$i] = $comparativeQuery->get();
        }

        // Categorize data into Assets, Liabilities, and Equity
        $assets = $currentData->filter(function ($item) {
            return in_array(strtolower($item->class_name), ['assets', 'asset']);
        });

        $liabilities = $currentData->filter(function ($item) {
            return in_array(strtolower($item->class_name), ['liabilities', 'liability']);
        });

        $equity = $currentData->filter(function ($item) {
            return in_array(strtolower($item->class_name), ['equity', 'capital']);
        });

        // Calculate Profit & Loss (Income - Expenses)
        $income = $currentData->filter(function ($item) {
            return in_array(strtolower($item->class_name), ['income', 'revenue']);
        });

        $expenses = $currentData->filter(function ($item) {
            return in_array(strtolower($item->class_name), ['expenses', 'expense']);
        });

        $totalIncome = $income->sum(function ($item) {
            return $item->credit_total - $item->debit_total;
        });

        $totalExpenses = $expenses->sum(function ($item) {
            return $item->debit_total - $item->credit_total;
        });

        $profitLoss = $totalIncome - $totalExpenses;

        return [
            'current' => [
                'assets' => $assets,
                'liabilities' => $liabilities,
                'equity' => $equity,
                'income' => $income,
                'expenses' => $expenses,
            ],
            'comparative' => $comparativeData,
            'as_of_date' => $asOfDate,
            'reporting_type' => $reportingType,
            'branch_id' => $branchId,
            'comparative_years' => $comparativeYears,
            'level_of_detail' => $levelOfDetail,
            'balance_check_type' => 'with_pnl', // Always use with P&L
            'profit_loss' => $profitLoss,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', $user->branch_id);
        $comparativeYears = $request->get('comparative_years', 1);
        $levelOfDetail = $request->get('level_of_detail', 'summary');
        $exportType = $request->get('export_type', 'pdf');

        // Get balance sheet data
        $balanceSheetData = $this->getBalanceSheetData($asOfDate, $reportingType, $branchId, $comparativeYears, $levelOfDetail);

        if ($exportType === 'excel') {
            return $this->exportExcel($balanceSheetData, $company, $asOfDate, $reportingType);
        } else {
            return $this->exportPdf($balanceSheetData, $company, $asOfDate, $reportingType);
        }
    }

    private function exportPdf($balanceSheetData, $company, $asOfDate, $reportingType)
    {
        // Generate PDF
        $pdf = \PDF::loadView('accounting.reports.balance-sheet.pdf', compact('balanceSheetData', 'company'));
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
        
        for ($i = 1; $i <= $balanceSheetData['comparative_years']; $i++) {
            $col++;
            $worksheet->setCellValue($col . $row, $i . ' Year' . ($i > 1 ? 's' : '') . ' Ago');
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
            $worksheet->setCellValue($col . $row, $balanceSheetData['level_of_detail'] === 'detailed' ? $asset->account_name : $asset->group_name);
            $col++;
            $worksheet->setCellValue($col . $row, $currentAmount);
            $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            for ($i = 1; $i <= $balanceSheetData['comparative_years']; $i++) {
                $col++;
                $comparativeAsset = $balanceSheetData['comparative'][$i]->first(function($item) use ($asset) {
                    return $balanceSheetData['level_of_detail'] === 'detailed' 
                        ? $item->account_id == $asset->account_id
                        : $item->group_id == $asset->group_id;
                });
                $comparativeAmount = $comparativeAsset ? ($comparativeAsset->debit_total - $comparativeAsset->credit_total) : 0;
                $worksheet->setCellValue($col . $row, $comparativeAmount);
                $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
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
            $worksheet->setCellValue($col . $row, $balanceSheetData['level_of_detail'] === 'detailed' ? $liability->account_name : $liability->group_name);
            $col++;
            $worksheet->setCellValue($col . $row, $currentAmount);
            $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            for ($i = 1; $i <= $balanceSheetData['comparative_years']; $i++) {
                $col++;
                $comparativeLiability = $balanceSheetData['comparative'][$i]->first(function($item) use ($liability) {
                    return $balanceSheetData['level_of_detail'] === 'detailed' 
                        ? $item->account_id == $liability->account_id
                        : $item->group_id == $liability->group_id;
                });
                $comparativeAmount = $comparativeLiability ? ($comparativeLiability->credit_total - $comparativeLiability->debit_total) : 0;
                $worksheet->setCellValue($col . $row, $comparativeAmount);
                $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
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
        if ($balanceSheetData['level_of_detail'] === 'detailed') {
            $worksheet->setCellValue('B' . $row, 'Code');
            $worksheet->setCellValue('C' . $row, 'Current Period');
            $col = 'D';
        } else {
            $worksheet->setCellValue('B' . $row, 'Current Period');
            $col = 'C';
        }
        
        for ($i = 1; $i <= $balanceSheetData['comparative_years']; $i++) {
            $worksheet->setCellValue($col . $row, $i . ' Year' . ($i > 1 ? 's' : '') . ' Ago');
            $col++;
        }
        $worksheet->getStyle('A' . $row . ':' . $col . $row)->getFont()->setBold(true);
        $row++;
        
        // Add equity accounts
        foreach ($balanceSheetData['current']['equity'] as $item) {
            $currentBalance = $item->credit_total - $item->debit_total;
            
            $worksheet->setCellValue('A' . $row, $balanceSheetData['level_of_detail'] === 'detailed' ? $item->account_name : $item->group_name);
            
            if ($balanceSheetData['level_of_detail'] === 'detailed') {
                $worksheet->setCellValue('B' . $row, $item->account_code);
                $worksheet->setCellValue('C' . $row, $currentBalance);
                $col = 'D';
            } else {
                $worksheet->setCellValue('B' . $row, $currentBalance);
                $col = 'C';
            }
            
            // Add comparative data
            for ($i = 1; $i <= $balanceSheetData['comparative_years']; $i++) {
                $compData = collect($balanceSheetData['comparative'][$i] ?? [])->first(function($comp) use ($item) {
                    return $balanceSheetData['level_of_detail'] === 'detailed' 
                        ? $comp->account_id == $item->account_id
                        : $comp->group_id == $item->group_id;
                });
                $compBalance = $compData ? ($compData->credit_total - $compData->debit_total) : 0;
                $worksheet->setCellValue($col . $row, $compBalance);
                $col++;
            }
            $row++;
        }
        
        // Add Profit & Loss row
        $worksheet->setCellValue('A' . $row, 'Profit & Loss');
        if ($balanceSheetData['level_of_detail'] === 'detailed') {
            $worksheet->setCellValue('C' . $row, $balanceSheetData['profit_loss']);
            $col = 'D';
        } else {
            $worksheet->setCellValue('B' . $row, $balanceSheetData['profit_loss']);
            $col = 'C';
        }
        
        // Add comparative P&L data
        for ($i = 1; $i <= $balanceSheetData['comparative_years']; $i++) {
            $compIncome = collect($balanceSheetData['comparative'][$i] ?? [])->filter(function($item) {
                return in_array(strtolower($item->class_name), ['income', 'revenue']);
            })->sum(function($item) {
                return $item->credit_total - $item->debit_total;
            });
            
            $compExpenses = collect($balanceSheetData['comparative'][$i] ?? [])->filter(function($item) {
                return in_array(strtolower($item->class_name), ['expenses', 'expense']);
            })->sum(function($item) {
                return $item->debit_total - $item->credit_total;
            });
            
            $compPnL = $compIncome - $compExpenses;
            $worksheet->setCellValue($col . $row, $compPnL);
            $col++;
        }
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        
        // Add Total Equity
        $totalEquity = $balanceSheetData['current']['equity']->sum(function($item) {
            return $item->credit_total - $item->debit_total;
        }) + $balanceSheetData['profit_loss'];
        
        $worksheet->setCellValue('A' . $row, 'Total Equity');
        if ($balanceSheetData['level_of_detail'] === 'detailed') {
            $worksheet->setCellValue('C' . $row, $totalEquity);
            $col = 'D';
        } else {
            $worksheet->setCellValue('B' . $row, $totalEquity);
            $col = 'C';
        }
        
        // Add comparative total equity
        for ($i = 1; $i <= $balanceSheetData['comparative_years']; $i++) {
            $compEquity = collect($balanceSheetData['comparative'][$i] ?? [])->filter(function($item) {
                return in_array(strtolower($item->class_name), ['equity', 'capital']);
            })->sum(function($item) {
                return $item->credit_total - $item->debit_total;
            });
            
            $compIncome = collect($balanceSheetData['comparative'][$i] ?? [])->filter(function($item) {
                return in_array(strtolower($item->class_name), ['income', 'revenue']);
            })->sum(function($item) {
                return $item->credit_total - $item->debit_total;
            });
            
            $compExpenses = collect($balanceSheetData['comparative'][$i] ?? [])->filter(function($item) {
                return in_array(strtolower($item->class_name), ['expenses', 'expense']);
            })->sum(function($item) {
                return $item->debit_total - $item->credit_total;
            });
            
            $compTotalEquity = $compEquity + ($compIncome - $compExpenses);
            $worksheet->setCellValue($col . $row, $compTotalEquity);
            $col++;
        }
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        // Add balance check
        $row++;
        $worksheet->setCellValue('A' . $row, 'BALANCE CHECK:');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        
        // Calculate totals for balance check
        $totalAssets = $balanceSheetData['current']['assets']->sum(function($item) {
            return $item->debit_total - $item->credit_total;
        });
        $totalLiabilities = $balanceSheetData['current']['liabilities']->sum(function($item) {
            return $item->credit_total - $item->debit_total;
        });
        $baseEquity = $balanceSheetData['current']['equity']->sum(function($item) {
            return $item->credit_total - $item->debit_total;
        });
        
        // Always use with P&L logic
        $totalPnL = $balanceSheetData['profit_loss'];
        $totalEquity = $baseEquity + $totalPnL;
        $rightSide = $totalLiabilities + $totalEquity;
        $balanceFormula = 'Assets (' . number_format($totalAssets, 2) . ') = Liabilities (' . number_format($totalLiabilities, 2) . ') + Equity (' . number_format($totalEquity, 2) . ') where Equity includes P&L (' . number_format($totalPnL, 2) . ') = ' . number_format($rightSide, 2);
        
        $worksheet->setCellValue('A' . $row, $balanceFormula);
        
        if ($totalAssets == $rightSide) {
            $worksheet->setCellValue('A' . ($row + 1), '✅ Balance sheet is balanced');
        } else {
            $worksheet->setCellValue('A' . ($row + 1), '⚠️ Balance sheet is not balanced');
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

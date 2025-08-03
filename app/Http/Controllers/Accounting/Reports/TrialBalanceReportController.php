<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TrialBalanceReportController extends Controller
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
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', $user->branch_id);
        $layout = $request->get('layout', 'single');
        $levelOfDetail = $request->get('level_of_detail', 'detailed');

        // Get comparative columns from request
        $comparativeColumns = $request->get('comparative_columns', []);

        // Get trial balance data
        $trialBalanceData = $this->getTrialBalanceData($startDate, $endDate, $reportingType, $branchId, $layout, $levelOfDetail, $comparativeColumns);

        return view('accounting.reports.trial-balance.index', compact(
            'trialBalanceData',
            'branches',
            'startDate',
            'endDate',
            'reportingType',
            'branchId',
            'layout',
            'levelOfDetail',
            'comparativeColumns',
            'user'
        ));
    }

    private function getTrialBalanceData($startDate, $endDate, $reportingType, $branchId, $layout, $levelOfDetail, $comparativeColumns = [])
    {
        $user = Auth::user();
        $company = $user->company;

        // Get current period data
        $currentData = $this->getPeriodData($startDate, $endDate, $reportingType, $branchId, $levelOfDetail);

        // Get comparative period data
        $comparativeData = [];
        foreach ($comparativeColumns as $index => $column) {
            if (!empty($column['start_date']) && !empty($column['end_date'])) {
                $comparativeData['Comparative ' . ($index + 1)] = $this->getPeriodData($column['start_date'], $column['end_date'], $reportingType, $branchId, $levelOfDetail);
            }
        }

        return [
            'data' => $currentData,
            'comparative' => $comparativeData,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reporting_type' => $reportingType,
            'branch_id' => $branchId,
            'layout' => $layout,
            'level_of_detail' => $levelOfDetail
        ];
    }

    private function getPeriodData($startDate, $endDate, $reportingType, $branchId, $levelOfDetail)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build the base query
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereBetween('gl_transactions.date', [$startDate, $endDate]);

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

        $data = $query->get();

        // Calculate running balances
        $data = $data->map(function ($item) {
            $item->balance = $item->debit_total - $item->credit_total;
            return $item;
        });

        // Sort by account code or group name
        if ($levelOfDetail === 'detailed') {
            $data = $data->sortBy('account_code');
        } else {
            $data = $data->sortBy('group_name');
        }

        return $data;
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', $user->branch_id);
        $layout = $request->get('layout', 'single');
        $levelOfDetail = $request->get('level_of_detail', 'detailed');
        $exportType = $request->get('export_type', 'pdf');

        // Get comparative columns from request
        $comparativeColumns = $request->get('comparative_columns', []);

        // Get trial balance data
        $trialBalanceData = $this->getTrialBalanceData($startDate, $endDate, $reportingType, $branchId, $layout, $levelOfDetail, $comparativeColumns);

        if ($exportType === 'excel') {
            return $this->exportExcel($trialBalanceData, $company, $startDate, $endDate, $reportingType, $levelOfDetail);
        } else {
            return $this->exportPdf($trialBalanceData, $company, $startDate, $endDate, $reportingType, $levelOfDetail);
        }
    }

    private function exportPdf($trialBalanceData, $company, $startDate, $endDate, $reportingType, $levelOfDetail)
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
        
        // Get layout and branchId from trial balance data
        $layout = $trialBalanceData['layout'] ?? 'single';
        $branchId = $trialBalanceData['branch_id'] ?? 'all';
        
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf->setOptions($options);

        $html = view('accounting.reports.trial-balance.pdf', compact(
            'trialBalanceData', 
            'company', 
            'startDate', 
            'endDate', 
            'reportingType',
            'branches',
            'layout',
            'branchId',
            'levelOfDetail'
        ))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'trial_balance_' . $startDate . '_to_' . $endDate . '_' . $reportingType . '.pdf';
        
        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function exportExcel($trialBalanceData, $company, $startDate, $endDate, $reportingType, $levelOfDetail)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        // Set title
        $worksheet->setCellValue('A1', 'TRIAL BALANCE REPORT');
        $worksheet->mergeCells('A1:F1');
        $worksheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $worksheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Set company info
        $worksheet->setCellValue('A2', $company->name ?? 'SmartFinance');
        $worksheet->mergeCells('A2:F2');
        $worksheet->getStyle('A2')->getFont()->setBold(true);
        $worksheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Set period info
        $worksheet->setCellValue('A3', 'Period: ' . Carbon::parse($startDate)->format('M d, Y') . ' to ' . Carbon::parse($endDate)->format('M d, Y'));
        $worksheet->mergeCells('A3:F3');
        $worksheet->getStyle('A3')->getAlignment()->setHorizontal('center');

        // Set reporting type and level of detail
        $worksheet->setCellValue('A4', 'Basis: ' . ucfirst($reportingType) . ' | Level: ' . ucfirst($levelOfDetail));
        $worksheet->mergeCells('A4:F4');
        $worksheet->getStyle('A4')->getAlignment()->setHorizontal('center');

        $row = 6;

        // Calculate total columns including comparative
        $totalColumns = 6; // Base columns: Account Code, Account Name, Class, Debits, Credits, Balance
        $comparativeColumns = [];
        if (isset($trialBalanceData['comparative']) && count($trialBalanceData['comparative']) > 0) {
            $comparativeColumns = array_keys($trialBalanceData['comparative']);
            $totalColumns += count($comparativeColumns) * 2; // Each comparative column adds 2 columns (Debit, Credit)
        }

        // Add headers based on level of detail
        if ($levelOfDetail === 'detailed') {
            $worksheet->setCellValue('A' . $row, 'Account Code');
            $worksheet->setCellValue('B' . $row, 'Account Name');
            $worksheet->setCellValue('C' . $row, 'Class');
            $worksheet->setCellValue('D' . $row, 'Debits');
            $worksheet->setCellValue('E' . $row, 'Credits');
            $worksheet->setCellValue('F' . $row, 'Balance');
            
            // Add comparative column headers
            $col = 'G';
            foreach ($comparativeColumns as $index => $columnName) {
                $worksheet->setCellValue($col . $row, 'Comparative ' . ($index + 1) . ' Debit');
                $col++;
                $worksheet->setCellValue($col . $row, 'Comparative ' . ($index + 1) . ' Credit');
                $col++;
            }
        } else {
            $worksheet->setCellValue('A' . $row, 'Group Name');
            $worksheet->setCellValue('B' . $row, 'Class');
            $worksheet->setCellValue('C' . $row, 'Debits');
            $worksheet->setCellValue('D' . $row, 'Credits');
            $worksheet->setCellValue('E' . $row, 'Balance');
            
            // Add comparative column headers
            $col = 'F';
            foreach ($comparativeColumns as $index => $columnName) {
                $worksheet->setCellValue($col . $row, 'Comparative ' . ($index + 1) . ' Debit');
                $col++;
                $worksheet->setCellValue($col . $row, 'Comparative ' . ($index + 1) . ' Credit');
                $col++;
            }
        }
        
        $worksheet->getStyle('A' . $row . ':' . $worksheet->getHighestColumn() . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row . ':' . $worksheet->getHighestColumn() . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');
        $row++;

        // Add data
        foreach ($trialBalanceData['data'] as $item) {
            if ($levelOfDetail === 'detailed') {
                $worksheet->setCellValue('A' . $row, $item->account_code ?? '');
                $worksheet->setCellValue('B' . $row, $item->account_name ?? '');
                $worksheet->setCellValue('C' . $row, $item->class_name ?? '');
                $worksheet->setCellValue('D' . $row, $item->debit_total ?? 0);
                $worksheet->setCellValue('E' . $row, $item->credit_total ?? 0);
                $worksheet->setCellValue('F' . $row, $item->balance ?? 0);
            } else {
                $worksheet->setCellValue('A' . $row, $item->group_name ?? '');
                $worksheet->setCellValue('B' . $row, $item->class_name ?? '');
                $worksheet->setCellValue('C' . $row, $item->debit_total ?? 0);
                $worksheet->setCellValue('D' . $row, $item->credit_total ?? 0);
                $worksheet->setCellValue('E' . $row, $item->balance ?? 0);
            }
            
            // Add comparative data
            $col = $levelOfDetail === 'detailed' ? 'G' : 'F';
            foreach ($comparativeColumns as $columnName) {
                $comparativeData = $trialBalanceData['comparative'][$columnName] ?? [];
                $comparativeItem = collect($comparativeData)->first(function($comp) use ($item, $levelOfDetail) {
                    if (!$comp) return false;
                    return $levelOfDetail === 'detailed' 
                        ? (isset($comp->account_id) && isset($item->account_id) && $comp->account_id == $item->account_id)
                        : (isset($comp->group_id) && isset($item->group_id) && $comp->group_id == $item->group_id);
                });
                
                $comparativeDebit = $comparativeItem ? $comparativeItem->debit_total : 0;
                $comparativeCredit = $comparativeItem ? $comparativeItem->credit_total : 0;
                
                $worksheet->setCellValue($col . $row, $comparativeDebit);
                $col++;
                $worksheet->setCellValue($col . $row, $comparativeCredit);
                $col++;
            }
            
            // Format numbers
            if ($levelOfDetail === 'detailed') {
                $worksheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            } else {
                $worksheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            }
            
            // Format comparative numbers
            $col = $levelOfDetail === 'detailed' ? 'G' : 'F';
            foreach ($comparativeColumns as $columnName) {
                $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $col++;
                $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $col++;
            }
            
            $row++;
        }

        // Add totals row
        $worksheet->setCellValue('A' . $row, 'TOTAL');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        // Calculate and add current period totals
        $totalDebit = collect($trialBalanceData['data'])->sum('debit_total');
        $totalCredit = collect($trialBalanceData['data'])->sum('credit_total');
        
        if ($levelOfDetail === 'detailed') {
            $worksheet->setCellValue('D' . $row, $totalDebit);
            $worksheet->setCellValue('E' . $row, $totalCredit);
            $worksheet->setCellValue('F' . $row, $totalDebit - $totalCredit);
        } else {
            $worksheet->setCellValue('C' . $row, $totalDebit);
            $worksheet->setCellValue('D' . $row, $totalCredit);
            $worksheet->setCellValue('E' . $row, $totalDebit - $totalCredit);
        }
        
        // Add comparative totals
        $col = $levelOfDetail === 'detailed' ? 'G' : 'F';
        foreach ($comparativeColumns as $columnName) {
            $comparativeData = $trialBalanceData['comparative'][$columnName] ?? [];
            $comparativeTotalDebit = collect($comparativeData)->sum('debit_total');
            $comparativeTotalCredit = collect($comparativeData)->sum('credit_total');
            
            $worksheet->setCellValue($col . $row, $comparativeTotalDebit);
            $col++;
            $worksheet->setCellValue($col . $row, $comparativeTotalCredit);
            $col++;
        }
        
        // Format totals
        $worksheet->getStyle('A' . $row . ':' . $worksheet->getHighestColumn() . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row . ':' . $worksheet->getHighestColumn() . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');

        // Auto-size columns
        foreach (range('A', $worksheet->getHighestColumn()) as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'trial_balance_' . $startDate . '_to_' . $endDate . '_' . $reportingType . '.xlsx';
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}

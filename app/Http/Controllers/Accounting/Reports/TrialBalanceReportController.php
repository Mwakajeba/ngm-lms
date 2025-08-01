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

        // Get trial balance data
        $trialBalanceData = $this->getTrialBalanceData($startDate, $endDate, $reportingType, $branchId, $layout);

        return view('accounting.reports.trial-balance.index', compact(
            'trialBalanceData',
            'branches',
            'startDate',
            'endDate',
            'reportingType',
            'branchId',
            'layout',
            'user'
        ));
    }

    private function getTrialBalanceData($startDate, $endDate, $reportingType, $branchId, $layout)
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
            // For cash basis, only include cash and cash equivalents accounts
            $query->where('account_class_groups.name', 'Cash and Cash Equivalents');
        }

        // Select fields based on layout
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

        $data = $query->get();

        // Calculate running balances
        $data = $data->map(function ($item) {
            $item->balance = $item->debit_total - $item->credit_total;
            return $item;
        });

        // Sort by account code
        $data = $data->sortBy('account_code');

        return [
            'data' => $data,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reporting_type' => $reportingType,
            'branch_id' => $branchId,
            'layout' => $layout,
            'total_debits' => $data->sum('debit_total'),
            'total_credits' => $data->sum('credit_total'),
            'total_balance' => $data->sum('balance'),
        ];
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
        $exportType = $request->get('export_type', 'pdf');

        // Get trial balance data
        $trialBalanceData = $this->getTrialBalanceData($startDate, $endDate, $reportingType, $branchId, $layout);

        if ($exportType === 'excel') {
            return $this->exportExcel($trialBalanceData, $company, $startDate, $endDate, $reportingType);
        } else {
            return $this->exportPdf($trialBalanceData, $company, $startDate, $endDate, $reportingType);
        }
    }

    private function exportPdf($trialBalanceData, $company, $startDate, $endDate, $reportingType)
    {
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf->setOptions($options);

        $html = view('accounting.reports.trial-balance.pdf', compact('trialBalanceData', 'company', 'startDate', 'endDate', 'reportingType'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'trial_balance_' . $startDate . '_to_' . $endDate . '_' . $reportingType . '.pdf';
        
        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function exportExcel($trialBalanceData, $company, $startDate, $endDate, $reportingType)
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

        // Set reporting type
        $worksheet->setCellValue('A4', 'Basis: ' . ucfirst($reportingType));
        $worksheet->mergeCells('A4:F4');
        $worksheet->getStyle('A4')->getAlignment()->setHorizontal('center');

        $row = 6;

        // Add headers
        $worksheet->setCellValue('A' . $row, 'Account Code');
        $worksheet->setCellValue('B' . $row, 'Account Name');
        $worksheet->setCellValue('C' . $row, 'Class');
        $worksheet->setCellValue('D' . $row, 'Debits');
        $worksheet->setCellValue('E' . $row, 'Credits');
        $worksheet->setCellValue('F' . $row, 'Balance');
        $worksheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');
        $row++;

        // Add data
        foreach ($trialBalanceData['data'] as $item) {
            $worksheet->setCellValue('A' . $row, $item->account_code);
            $worksheet->setCellValue('B' . $row, $item->account_name);
            $worksheet->setCellValue('C' . $row, $item->class_name);
            $worksheet->setCellValue('D' . $row, $item->debit_total);
            $worksheet->setCellValue('E' . $row, $item->credit_total);
            $worksheet->setCellValue('F' . $row, $item->balance);
            
            // Format numbers
            $worksheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $worksheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $worksheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            
            $row++;
        }

        // Add totals
        $worksheet->setCellValue('A' . $row, 'TOTALS');
        $worksheet->setCellValue('D' . $row, $trialBalanceData['total_debits']);
        $worksheet->setCellValue('E' . $row, $trialBalanceData['total_credits']);
        $worksheet->setCellValue('F' . $row, $trialBalanceData['total_balance']);
        $worksheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $worksheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $worksheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'trial_balance_' . $startDate . '_to_' . $endDate . '_' . $reportingType . '.xlsx';
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

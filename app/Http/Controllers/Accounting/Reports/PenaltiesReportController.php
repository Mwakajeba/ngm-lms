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

class PenaltiesReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $penaltyId = $request->get('penalty_id', 'all');
        $penaltyType = $request->get('penalty_type', 'all'); // 'income' or 'receivables'

        // Get user's assigned branches
        $branches = $user->branches()->where('company_id', $company->id)->get();
        
        // Get all penalties from penalties table (filtered by company through branch)
        $penalties = \App\Models\Penalty::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->where('status', 'active')->get();

        // Get penalties data
        $penaltiesData = $this->getPenaltiesData($startDate, $endDate, $branchId, $penaltyId, $penaltyType);

        return view('accounting.reports.penalties.index', compact(
            'penaltiesData',
            'startDate',
            'endDate',
            'branchId',
            'penaltyId',
            'penaltyType',
            'branches',
            'penalties',
            'user'
        ));
    }

    private function getPenaltiesData($startDate, $endDate, $branchId, $penaltyId, $penaltyType)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get chart account IDs from penalties table based on penalty type
        $penaltyQuery = \App\Models\Penalty::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->where('status', 'active');

        // Apply penalty filter
        if ($penaltyId !== 'all') {
            $penaltyQuery->where('id', $penaltyId);
        }

        $penalties = $penaltyQuery->get();
        $chartAccountIds = [];

        // Determine which chart account IDs to use based on penalty type
        foreach ($penalties as $penalty) {
            if ($penaltyType === 'all') {
                $chartAccountIds[] = $penalty->penalty_income_account_id;
                $chartAccountIds[] = $penalty->penalty_receivables_account_id;
            } elseif ($penaltyType === 'income') {
                $chartAccountIds[] = $penalty->penalty_income_account_id;
            } elseif ($penaltyType === 'receivables') {
                $chartAccountIds[] = $penalty->penalty_receivables_account_id;
            }
        }

        $chartAccountIds = array_unique($chartAccountIds);

        // If no chart account IDs found, return empty result
        if (empty($chartAccountIds)) {
            return [
                'data' => collect([]),
                'summary' => [
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'total_transactions' => 0,
                    'unique_penalties' => 0,
                    'unique_customers' => 0,
                    'balance' => 0
                ]
            ];
        }

        // Build query for GL transactions using chart account IDs from penalties
        $query = DB::table('gl_transactions as gl')
            ->join('chart_accounts as ca', 'gl.chart_account_id', '=', 'ca.id')
            ->leftJoin('customers as c', 'gl.customer_id', '=', 'c.id')
            ->leftJoin('branches as b', 'gl.branch_id', '=', 'b.id')
            ->whereIn('gl.chart_account_id', $chartAccountIds)
            ->whereBetween('gl.date', [$startDate, $endDate]);

        // Apply branch filter
        if ($branchId !== 'all') {
            $query->where('gl.branch_id', $branchId);
        }

        $query->select(
            'gl.id as transaction_id',
            'gl.date',
            'gl.amount',
            'gl.nature',
            'gl.description',
            'gl.transaction_id as reference_id',
            'gl.transaction_type',
            'gl.chart_account_id',
            'ca.account_name as chart_account_name',
            'ca.account_code',
            'c.name as customer_name',
            'b.name as branch_name'
        );

        $results = $query->orderBy('gl.date', 'desc')->get();

        // Get penalty information for each chart account that actually has transactions
        $chartAccountIdsWithTransactions = $results->pluck('chart_account_id')->unique()->toArray();
        
        // Create mapping of chart account IDs to penalties
        $penaltiesByChartAccount = [];
        foreach ($penalties as $penalty) {
            if (in_array($penalty->penalty_income_account_id, $chartAccountIdsWithTransactions)) {
                $penaltiesByChartAccount[$penalty->penalty_income_account_id][] = [
                    'id' => $penalty->id,
                    'name' => $penalty->name,
                    'type' => 'income'
                ];
            }
            if (in_array($penalty->penalty_receivables_account_id, $chartAccountIdsWithTransactions)) {
                $penaltiesByChartAccount[$penalty->penalty_receivables_account_id][] = [
                    'id' => $penalty->id,
                    'name' => $penalty->name,
                    'type' => 'receivables'
                ];
            }
        }

        // Add penalty information to results
        $results = $results->map(function ($item) use ($penaltiesByChartAccount, $penaltyType) {
            $chartAccountId = $item->chart_account_id;
            $penaltyInfo = $penaltiesByChartAccount[$chartAccountId] ?? [];
            
            if (empty($penaltyInfo)) {
                $item->penalty_name = 'Unknown Penalty';
                $item->penalty_type = 'unknown';
            } else {
                // If specific penalty type is selected, filter accordingly
                if ($penaltyType !== 'all') {
                    $penaltyInfo = array_filter($penaltyInfo, function($p) use ($penaltyType) {
                        return $p['type'] === $penaltyType;
                    });
                }
                
                if (!empty($penaltyInfo)) {
                    $penaltyNames = array_column($penaltyInfo, 'name');
                    $penaltyTypes = array_column($penaltyInfo, 'type');
                    $item->penalty_name = implode(', ', array_unique($penaltyNames));
                    $item->penalty_type = implode(', ', array_unique($penaltyTypes));
                } else {
                    $item->penalty_name = 'No matching penalty type';
                    $item->penalty_type = 'none';
                }
            }
            
            return $item;
        });

        // Calculate summary totals
        $summary = [
            'total_debit' => $results->where('nature', 'debit')->sum('amount'),
            'total_credit' => $results->where('nature', 'credit')->sum('amount'),
            'total_transactions' => $results->count(),
            'unique_penalties' => $results->pluck('penalty_name')->unique()->count(),
            'unique_customers' => $results->pluck('customer_name')->filter()->unique()->count()
        ];

        // Calculate balance
        $balance = $summary['total_credit'] - $summary['total_debit'];
        $summary['balance'] = $balance;

        return [
            'data' => $results,
            'summary' => $summary
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $penaltyId = $request->get('penalty_id', 'all');
        $penaltyType = $request->get('penalty_type', 'all');

        // Get penalties data
        $penaltiesData = $this->getPenaltiesData($startDate, $endDate, $branchId, $penaltyId, $penaltyType);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Penalties Report - GL Transactions');
        $sheet->setCellValue('A2', 'Company: ' . $company->name);
        $sheet->setCellValue('A3', 'Period: ' . $startDate . ' to ' . $endDate);
        $sheet->setCellValue('A4', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Set column headers
        $headers = ['#', 'Date', 'Penalty Name', 'Penalty Type', 'Chart Account', 'Account Code', 'Customer', 'Branch', 'Nature', 'Amount', 'Description', 'Reference ID', 'Transaction Type'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '6', $header);
            $col++;
        }

        // Add data
        $row = 7;
        foreach ($penaltiesData['data'] as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->date);
            $sheet->setCellValue('C' . $row, $item->penalty_name);
            $sheet->setCellValue('D' . $row, ucfirst($item->penalty_type));
            $sheet->setCellValue('E' . $row, $item->chart_account_name);
            $sheet->setCellValue('F' . $row, $item->account_code);
            $sheet->setCellValue('G' . $row, $item->customer_name);
            $sheet->setCellValue('H' . $row, $item->branch_name);
            $sheet->setCellValue('I' . $row, ucfirst($item->nature));
            $sheet->setCellValue('J' . $row, number_format($item->amount, 2));
            $sheet->setCellValue('K' . $row, $item->description);
            $sheet->setCellValue('L' . $row, $item->reference_id);
            $sheet->setCellValue('M' . $row, $item->transaction_type);
            $row++;
        }

        // Add summary
        $row += 2;
        $sheet->setCellValue('A' . $row, 'SUMMARY');
        $sheet->setCellValue('B' . $row, 'Total Debit: ' . number_format($penaltiesData['summary']['total_debit'], 2));
        $row++;
        $sheet->setCellValue('B' . $row, 'Total Credit: ' . number_format($penaltiesData['summary']['total_credit'], 2));
        $row++;
        $sheet->setCellValue('B' . $row, 'Total Transactions: ' . $penaltiesData['summary']['total_transactions']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Unique Penalties: ' . $penaltiesData['summary']['unique_penalties']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Unique Customers: ' . $penaltiesData['summary']['unique_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Balance: ' . number_format($penaltiesData['summary']['balance'], 2));

        // Auto-size columns
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'penalties_report_' . $startDate . '_to_' . $endDate . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'penalties_report');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $penaltyId = $request->get('penalty_id', 'all');
        $penaltyType = $request->get('penalty_type', 'all');

        // Get penalties data
        $penaltiesData = $this->getPenaltiesData($startDate, $endDate, $branchId, $penaltyId, $penaltyType);

        // Get filter labels for display
        $branchName = 'All Branches';
        if ($branchId !== 'all') {
            $branch = \App\Models\Branch::find($branchId);
            $branchName = $branch ? $branch->name : 'Unknown Branch';
        }

        $penaltyName = 'All Penalties';
        if ($penaltyId !== 'all') {
            $penalty = \App\Models\Penalty::find($penaltyId);
            $penaltyName = $penalty ? $penalty->name : 'Unknown Penalty';
        }

        $penaltyTypeName = ucfirst($penaltyType);

        $pdf = Pdf::loadView('accounting.reports.penalties.pdf', [
            'penaltiesData' => $penaltiesData,
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchName' => $branchName,
            'penaltyName' => $penaltyName,
            'penaltyTypeName' => $penaltyTypeName,
            'user' => $user
        ]);

        $filename = 'penalties_report_' . $startDate . '_to_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }
}

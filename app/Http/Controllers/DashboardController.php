<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ChartAccount;
use App\Models\AccountClassGroup;
use App\Models\GlTransaction;
use App\Models\BankReconciliation;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\Receipt;
use App\Services\LoanPenaltyService;

class DashboardController extends Controller
{
    public function index()
    {
        $company = auth()->user()->company;
        
        // Get balance sheet data
        $balanceSheetData = $this->getBalanceSheetData();
        
        // Get comprehensive financial report data
        $financialReportData = $this->getFinancialReportData();
        
        // Get current month
        $currentMonth = now()->format('Y-m');

        // Get recent activities - filter by company through branch and current month
        $recentJournals = Journal::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
        ->with(['user', 'branch'])
        ->latest()
        ->take(5)
        ->get();
        
        $recentPayments = Payment::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
        ->with(['user', 'branch'])
        ->latest()
        ->get();
        
        $recentReceipts = Receipt::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
        ->with(['user', 'branch', 'customer'])
        ->latest()
        ->get();
            
        // Get bank reconciliation stats
        $bankReconciliationStats = BankReconciliation::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft
        ')
        ->first();

        $penaltyBalance = LoanPenaltyService::getTotalPenaltyBalance();
        info('penaltyBalance'.$penaltyBalance);
        
        // Get previous year comparative data
        $previousYearData = $this->getPreviousYearData();
            
        return view('dashboard', compact(
            'balanceSheetData',
            'financialReportData',
            'recentJournals',
            'recentPayments', 
            'recentReceipts',
            'bankReconciliationStats',
            'penaltyBalance',
            'previousYearData'
        ));
    }
    
    private function getBalanceSheetData()
    {
        $company = auth()->user()->company;
        
        // Get balance sheet data directly from gl_transactions
        $balanceSheetData = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->select(
                'account_class.name as class_name',
                'account_class_groups.group_code as class_code',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit'),
                DB::raw('COUNT(DISTINCT chart_accounts.id) as account_count')
            )
            ->groupBy('account_class.id', 'account_class.name', 'account_class_groups.group_code')
            ->get()
            ->map(function ($item) {
                // Calculate balance based on account class
                $balance = 0;
                switch (strtolower($item->class_name)) {
                    case 'assets':
                        $balance = $item->total_debit - $item->total_credit; // Assets: debit increases
                        break;
                    case 'liabilities':
                        $balance = $item->total_credit - $item->total_debit; // Liabilities: credit increases
                        break;
                    case 'equity':
                        $balance = $item->total_credit - $item->total_debit; // Equity: credit increases
                        break;
                    case 'income':
                    case 'revenue':
                        $balance = $item->total_credit - $item->total_debit; // Revenue: credit increases
                        break;
                    case 'expenses':
                    case 'expense':
                        $balance = $item->total_debit - $item->total_credit; // Expenses: debit increases
                        break;
                    default:
                        $balance = $item->total_debit - $item->total_credit;
                }
                
                return [
                    'class_name' => $item->class_name,
                    'class_code' => $item->class_code,
                    'balance' => $balance,
                    'account_count' => $item->account_count
                ];
            })
            ->sortByDesc(function ($item) {
                return abs($item['balance']);
            })
            ->values()
            ->toArray();
            
        return $balanceSheetData;
    }
    
    private function getFinancialReportData()
    {
        $company = auth()->user()->company;
        
        // Get all chart accounts with their balances grouped by account class
        $chartAccountsData = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->select(
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'account_class.name as class_name',
                'account_class_groups.name as group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'account_class.name', 'account_class_groups.name')
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
                        'sum' => $balance
                    ];
                    break;
                case 'liabilities':
                    $balance = $account->credit_total - $account->debit_total; // Liabilities: credit increases
                    $chartAccountsLiabilities[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'equity':
                    $balance = $account->credit_total - $account->debit_total; // Equity: credit increases
                    $chartAccountsEquitys[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'income':
                case 'revenue':
                    $balance = $account->credit_total - $account->debit_total; // Revenue: credit increases
                    $chartAccountsRevenues[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'expenses':
                case 'expense':
                    $balance = $account->debit_total - $account->credit_total; // Expenses: debit increases
                    $chartAccountsExpense[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
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
    
    private function getPreviousYearData()
    {
        $company = auth()->user()->company;
        $currentYear = date('Y');
        $previousYear = $currentYear - 1;
        
        // Get previous year financial data by account
        $previousYearData = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereYear('gl_transactions.date', $previousYear)
            ->select(
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'account_class.name as class_name',
                'account_class_groups.name as group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'account_class.name', 'account_class_groups.name')
            ->get();
            
        // Group by account class and calculate balances
        $previousYearAssets = [];
        $previousYearLiabilities = [];
        $previousYearEquitys = [];
        $previousYearRevenues = [];
        $previousYearExpense = [];
        
        foreach ($previousYearData as $account) {
            // Calculate balance based on account class
            $balance = 0;
            
            // Categorize based on account class
            switch (strtolower($account->class_name)) {
                case 'assets':
                    $balance = $account->debit_total - $account->credit_total; // Assets: debit increases
                    $previousYearAssets[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'liabilities':
                    $balance = $account->credit_total - $account->debit_total; // Liabilities: credit increases
                    $previousYearLiabilities[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'equity':
                    $balance = $account->credit_total - $account->debit_total; // Equity: credit increases
                    $previousYearEquitys[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'income':
                case 'revenue':
                    $balance = $account->credit_total - $account->debit_total; // Revenue: credit increases
                    $previousYearRevenues[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'expenses':
                case 'expense':
                    $balance = $account->debit_total - $account->credit_total; // Expenses: debit increases
                    $previousYearExpense[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
            }
        }
        
        // Calculate previous year profit/loss
        $sumRevenue = collect($previousYearRevenues)->flatten(1)->sum('sum');
        $sumExpense = collect($previousYearExpense)->flatten(1)->sum('sum');
        $previousYearProfitLoss = $sumRevenue - $sumExpense;
        
        return [
            'year' => $previousYear,
            'chartAccountsAssets' => $previousYearAssets,
            'chartAccountsLiabilities' => $previousYearLiabilities,
            'chartAccountsEquitys' => $previousYearEquitys,
            'chartAccountsRevenues' => $previousYearRevenues,
            'chartAccountsExpense' => $previousYearExpense,
            'profitLoss' => $previousYearProfitLoss
        ];
    }
} 
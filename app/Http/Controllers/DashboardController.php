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
use App\Models\Bill;

class DashboardController extends Controller
{
    public function index()
    {
        $company = auth()->user()->company;
        
        // Get balance sheet data
        $balanceSheetData = $this->getBalanceSheetData();
        
        // Get comprehensive financial report data
        $financialReportData = $this->getFinancialReportData();
        
        // Get recent activities - filter by company through branch
        $recentJournals = Journal::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->with(['user', 'branch'])
        ->latest()
        ->take(5)
        ->get();
            
        $recentPayments = Payment::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->with(['user', 'branch'])
        ->latest()
        ->take(5)
        ->get();
            
        $recentBills = Bill::where('company_id', $company->id)
        ->with(['user', 'branch', 'supplier'])
        ->latest()
        ->take(5)
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
            
        return view('dashboard', compact(
            'balanceSheetData',
            'financialReportData',
            'recentJournals',
            'recentPayments', 
            'recentBills',
            'bankReconciliationStats'
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
                $balance = $item->total_debit - $item->total_credit;
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
            $balance = $account->debit_total - $account->credit_total;
            $accountData = [
                'account_id' => $account->account_id,
                'account' => $account->account,
                'sum' => $balance
            ];
            
            // Categorize based on account class
            switch (strtolower($account->class_name)) {
                case 'assets':
                    $chartAccountsAssets[$account->group_name][] = $accountData;
                    break;
                case 'liabilities':
                    $chartAccountsLiabilities[$account->group_name][] = $accountData;
                    break;
                case 'equity':
                    $chartAccountsEquitys[$account->group_name][] = $accountData;
                    break;
                case 'income':
                case 'revenue':
                    $chartAccountsRevenues[$account->group_name][] = $accountData;
                    break;
                case 'expenses':
                case 'expense':
                    $chartAccountsExpense[$account->group_name][] = $accountData;
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
} 
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ChartAccount;
use App\Models\GlTransaction;
use Vinkla\Hashids\Facades\Hashids;

class TransactionController extends Controller
{
    public function doubleEntries($accountId)
    {
        // Get the account details
        $account_id = Hashids::decode($accountId)[0] ?? null;
        if (!$account_id) {
            abort(404, 'Account not found');
        }
        
        $account = ChartAccount::with(['accountClassGroup.accountClass'])
            ->findOrFail($account_id);
            
        // Fetch transactions where this account is either Debited or Credited
        $transactions = GlTransaction::where('chart_account_id', $account_id)
            ->with(['journal', 'paymentVoucher', 'bill'])
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculate running balance and prepare data
        $runningBalance = 0;
        $transactionsWithBalance = $transactions->map(function ($transaction) use (&$runningBalance) {
            if ($transaction->nature == 'debit') {
                $runningBalance += $transaction->amount;
            } else {
                $runningBalance -= $transaction->amount;
            }
            
            return [
                'transaction' => $transaction,
                'running_balance' => $runningBalance,
                'debit_amount' => $transaction->nature == 'debit' ? $transaction->amount : 0,
                'credit_amount' => $transaction->nature == 'credit' ? $transaction->amount : 0,
            ];
        });

        // Calculate totals for balancing
        $totalDebit = $transactions->where('nature', 'debit')->sum('amount');
        $totalCredit = $transactions->where('nature', 'credit')->sum('amount');
        $finalBalance = $totalDebit - $totalCredit;

        return view('transactions.double-entries', [
            'transactions' => $transactionsWithBalance,
            'chartAccount' => $account,
            'account_name' => $account->account_name,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'balance' => $finalBalance,
        ]);
    }

    public function getDoubleEntry($category_id, $category, $category_type, $category_key)
    {
        $id = Hashids::decode($category_id)[0] ?? null;

        $transactions = GlTransaction::where('category_id', $id)
            ->where('category', $category)
            ->when(true, function ($query) use ($category_key, $category_type) {
                if ($category_key === 'Loan' && $category_type !== 'Repayment') {
                    // Only apply category_key (exclude category_type)
                    return $query->where('category_key', 'Loan');
                } else {
                    // Apply both category_type and category_key
                    return $query->where('category_type', $category_type)
                        ->where('category_key', $category_key);
                }
            })
            ->orderBy('date', 'asc')
            ->with('chartAccount')
            ->get();

        $totalDebit = $transactions->where('nature', 'debit')->sum('amount');
        $totalCredit = $transactions->where('nature', 'credit')->sum('amount');

        return view('transactions.double-entry', [
            'transactions' => $transactions,
            'category' => $category,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
        ]);
    }

    public function showTransactionDetails($transactionId, $transactionType = null)
    {
        // Decode the transaction ID and type
        $decodedId = Hashids::decode($transactionId);
        if (empty($decodedId)) {
            abort(404, 'Transaction not found');
        }
        
        $transactionId = $decodedId[0];
        
        // Get the specific transaction
        $transaction = GlTransaction::with(['chartAccount', 'journal', 'paymentVoucher', 'bill'])
            ->findOrFail($transactionId);
            
        // Get all transactions with the same transaction_id and transaction_type
        $allRelatedTransactions = GlTransaction::where('transaction_id', $transaction->transaction_id)
            ->where('transaction_type', $transaction->transaction_type)
            ->with(['chartAccount'])
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Group by nature for better display
        $debitTransactions = $allRelatedTransactions->where('nature', 'debit');
        $creditTransactions = $allRelatedTransactions->where('nature', 'credit');
        
        // Calculate totals
        $totalDebit = $debitTransactions->sum('amount');
        $totalCredit = $creditTransactions->sum('amount');
        $balance = $totalDebit - $totalCredit;
        
        return view('transactions.transaction-details', compact(
            'transaction',
            'allRelatedTransactions',
            'debitTransactions',
            'creditTransactions',
            'totalDebit',
            'totalCredit',
            'balance'
        ));
    }
} 
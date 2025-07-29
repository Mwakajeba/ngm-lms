<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\ChartAccount;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\GlTransaction;
use App\Traits\TransactionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReceiptVoucherController extends Controller
{
    use TransactionHelper;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Get receipts for the current company/branch
        $receipts = Receipt::with(['bankAccount', 'customer', 'user', 'receiptItems'])
            ->whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->orderBy('date', 'desc')
            ->get();

        // Calculate stats
        $stats = [
            'total' => $receipts->count(),
            'this_month' => $receipts->where('date', '>=', now()->startOfMonth())->count(),
            'total_amount' => $receipts->sum('amount'),
            'this_month_amount' => $receipts->where('date', '>=', now()->startOfMonth())->sum('amount'),
        ];

        return view('accounting.receipt-vouchers.index', compact('receipts', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Get bank accounts for the current company
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        // Get customers for the current company/branch
        $customers = Customer::where('company_id', $user->company_id)
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->orderBy('name')
            ->get();

        // Get chart accounts for the current company
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
            ->orderBy('account_name')
            ->get();

        return view('accounting.receipt-vouchers.create', compact('bankAccounts', 'customers', 'chartAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'customer_id' => 'required|exists:customers,id',
            'description' => 'nullable|string',
            'line_items' => 'required|array|min:1',
            'line_items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'line_items.*.amount' => 'required|numeric|min:0.01',
            'line_items.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            return $this->runTransaction(function () use ($request) {
                $user = Auth::user();
                $totalAmount = collect($request->line_items)->sum('amount');

                // Create receipt
                $receipt = Receipt::create([
                    'reference' => $request->reference ?: 'RV-' . strtoupper(uniqid()),
                    'reference_type' => 'manual',
                    'reference_number' => $request->reference,
                    'amount' => $totalAmount,
                    'date' => $request->date,
                    'description' => $request->description,
                    'user_id' => $user->id,
                    'bank_account_id' => $request->bank_account_id,
                    'customer_id' => $request->customer_id,
                    'branch_id' => $user->branch_id,
                    'approved' => true, // Auto-approve for now
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                // Create receipt items
                $receiptItems = [];
                foreach ($request->line_items as $lineItem) {
                    $receiptItems[] = [
                        'receipt_id' => $receipt->id,
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'amount' => $lineItem['amount'],
                        'description' => $lineItem['description'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                ReceiptItem::insert($receiptItems);

                // Create GL transactions
                $bankAccount = BankAccount::find($request->bank_account_id);

                // Debit bank account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $request->customer_id,
                    'amount' => $totalAmount,
                    'nature' => 'debit',
                    'transaction_id' => $receipt->id,
                    'transaction_type' => 'receipt',
                    'date' => $request->date,
                    'description' => $request->description ?: "Receipt voucher {$receipt->reference}",
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Credit each chart account
                foreach ($request->line_items as $lineItem) {
                    GlTransaction::create([
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'customer_id' => $request->customer_id,
                        'amount' => $lineItem['amount'],
                        'nature' => 'credit',
                        'transaction_id' => $receipt->id,
                        'transaction_type' => 'receipt',
                        'date' => $request->date,
                        'description' => $lineItem['description'] ?: "Receipt voucher {$receipt->reference}",
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                    ]);
                }

                return redirect()->route('accounting.receipt-vouchers.show', $receipt)
                    ->with('success', 'Receipt voucher created successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create receipt voucher: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receiptVoucher)
    {
        $receiptVoucher->load([
            'bankAccount',
            'customer.company',
            'customer.branch',
            'user',
            'receiptItems.chartAccount',
            'glTransactions.chartAccount',
            'branch'
        ]);

        return view('accounting.receipt-vouchers.show', compact('receiptVoucher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Receipt $receiptVoucher)
    {
        $user = Auth::user();

        // Get bank accounts for the current company
        $bankAccounts = BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('name')
            ->get();

        // Get customers for the current company/branch
        $customers = Customer::where('company_id', $user->company_id)
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->orderBy('name')
            ->get();

        // Get chart accounts for the current company
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
            ->orderBy('account_name')
            ->get();

        $receiptVoucher->load('receiptItems');

        return view('accounting.receipt-vouchers.edit', compact('receiptVoucher', 'bankAccounts', 'customers', 'chartAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receipt $receiptVoucher)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'customer_id' => 'required|exists:customers,id',
            'description' => 'nullable|string',
            'line_items' => 'required|array|min:1',
            'line_items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'line_items.*.amount' => 'required|numeric|min:0.01',
            'line_items.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            return $this->runTransaction(function () use ($request, $receiptVoucher) {
                $user = Auth::user();
                $totalAmount = collect($request->line_items)->sum('amount');

                // Update receipt
                $receiptVoucher->update([
                    'reference' => $request->reference ?: $receiptVoucher->reference,
                    'reference_number' => $request->reference,
                    'amount' => $totalAmount,
                    'date' => $request->date,
                    'description' => $request->description,
                    'bank_account_id' => $request->bank_account_id,
                    'customer_id' => $request->customer_id,
                ]);

                // Delete existing receipt items and GL transactions
                $receiptVoucher->receiptItems()->delete();
                $receiptVoucher->glTransactions()->delete();

                // Create new receipt items
                $receiptItems = [];
                foreach ($request->line_items as $lineItem) {
                    $receiptItems[] = [
                        'receipt_id' => $receiptVoucher->id,
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'amount' => $lineItem['amount'],
                        'description' => $lineItem['description'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                ReceiptItem::insert($receiptItems);

                // Create new GL transactions
                $bankAccount = BankAccount::find($request->bank_account_id);

                // Debit bank account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $request->customer_id,
                    'amount' => $totalAmount,
                    'nature' => 'debit',
                    'transaction_id' => $receiptVoucher->id,
                    'transaction_type' => 'receipt',
                    'date' => $request->date,
                    'description' => $request->description ?: "Receipt voucher {$receiptVoucher->reference}",
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                ]);

                // Credit each chart account
                foreach ($request->line_items as $lineItem) {
                    GlTransaction::create([
                        'chart_account_id' => $lineItem['chart_account_id'],
                        'customer_id' => $request->customer_id,
                        'amount' => $lineItem['amount'],
                        'nature' => 'credit',
                        'transaction_id' => $receiptVoucher->id,
                        'transaction_type' => 'receipt',
                        'date' => $request->date,
                        'description' => $lineItem['description'] ?: "Receipt voucher {$receiptVoucher->reference}",
                        'branch_id' => $user->branch_id,
                        'user_id' => $user->id,
                    ]);
                }

                return redirect()->route('accounting.receipt-vouchers.show', $receiptVoucher)
                    ->with('success', 'Receipt voucher updated successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update receipt voucher: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receipt $receiptVoucher)
    {
        try {
            return $this->runTransaction(function () use ($receiptVoucher) {
                // Delete GL transactions first
                $receiptVoucher->glTransactions()->delete();

                // Delete receipt items
                $receiptVoucher->receiptItems()->delete();

                // Delete receipt
                $receiptVoucher->delete();

                return redirect()->route('accounting.receipt-vouchers.index')
                    ->with('success', 'Receipt voucher deleted successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete receipt voucher: ' . $e->getMessage()]);
        }
    }
}

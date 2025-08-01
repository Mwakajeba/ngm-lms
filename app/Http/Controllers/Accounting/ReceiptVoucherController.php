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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptVoucherController extends Controller
{
    use TransactionHelper;

    /**
     * Debug method to test controller accessibility
     */
    public function debug()
    {
        return response()->json([
            'message' => 'ReceiptVoucherController is accessible',
            'user' => Auth::user()->name ?? 'No user',
            'timestamp' => now()
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Get receipts for the current company/branch
        $receipts = Receipt::with(['bankAccount', 'user', 'receiptItems'])
            ->whereHas('bankAccount.chartAccount.accountClassGroup', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->when($user->branch_id, function ($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->orderBy('date', 'desc')
            ->get();

        // Load customer relationships for receipts with payee_type = 'customer'
        $customerReceiptIds = $receipts->where('payee_type', 'customer')->pluck('payee_id')->filter();
        if ($customerReceiptIds->isNotEmpty()) {
            $receipts->load([
                'customer' => function ($query) use ($customerReceiptIds) {
                    $query->whereIn('id', $customerReceiptIds);
                }
            ]);
        }

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
        // Debug: Log the incoming request data
        \Log::info('Receipt voucher store request started');
        \Log::info('Request method:', ['method' => $request->method()]);
        \Log::info('Request URL:', ['url' => $request->url()]);
        \Log::info('Request headers:', $request->headers->all());
        \Log::info('Request all data:', $request->all());
        \Log::info('Request input:', $request->input());
        \Log::info('Request has file attachment:', ['has_file' => $request->hasFile('attachment')]);

        // Check if line_items are present
        \Log::info('Line items data:', ['line_items' => $request->input('line_items')]);

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payee_type' => 'required|in:customer,other',
            'customer_id' => 'required_if:payee_type,customer|exists:customers,id',
            'payee_name' => 'nullable|string|max:255|required_if:payee_type,other',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf|max:2048',
            'line_items' => 'required|array|min:1',
            'line_items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'line_items.*.amount' => 'required|numeric|min:0.01',
            'line_items.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            \Log::error('Receipt voucher validation failed:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        \Log::info('Validation passed, proceeding with creation');

        try {
            return $this->runTransaction(function () use ($request) {
                $user = Auth::user();
                $totalAmount = collect($request->line_items)->sum('amount');

                \Log::info('Creating receipt voucher with total amount:', ['total' => $totalAmount]);

                // Handle file upload
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $attachmentPath = $file->storeAs('receipt-attachments', $fileName, 'public');
                }

                // Set payee information
                if ($request->payee_type === 'customer') {
                    $payeeType = 'customer';
                    $payeeId = $request->customer_id;
                    $payeeName = null;
                } else {
                    $payeeType = 'other';
                    $payeeId = null;
                    $payeeName = $request->payee_name;
                }

                \Log::info('Payee information:', [
                    'type' => $payeeType,
                    'id' => $payeeId,
                    'name' => $payeeName
                ]);

                // Create receipt
                $receipt = Receipt::create([
                    'reference' => $request->reference ?: 'RV-' . strtoupper(uniqid()),
                    'reference_type' => 'manual',
                    'reference_number' => $request->reference,
                    'amount' => $totalAmount,
                    'date' => $request->date,
                    'description' => $request->description,
                    'attachment' => $attachmentPath,
                    'user_id' => $user->id,
                    'bank_account_id' => $request->bank_account_id,
                    'payee_type' => $payeeType,
                    'payee_id' => $payeeId,
                    'payee_name' => $payeeName,
                    'branch_id' => $user->branch_id,
                    'approved' => true, // Auto-approve for now
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                \Log::info('Receipt created successfully:', ['receipt_id' => $receipt->id]);

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
                \Log::info('Receipt items created:', ['count' => count($receiptItems)]);

                // Create GL transactions
                $bankAccount = BankAccount::find($request->bank_account_id);

                // Debit bank account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $payeeType === 'customer' ? $payeeId : null,
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
                        'customer_id' => $payeeType === 'customer' ? $payeeId : null,
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

                \Log::info('GL transactions created successfully');

                return redirect()->route('accounting.receipt-vouchers.show', $receipt)
                    ->with('success', 'Receipt voucher created successfully.');
            });
        } catch (\Exception $e) {
            \Log::error('Receipt voucher creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            'payee_type' => 'required|in:customer,other',
            'customer_id' => 'required_if:payee_type,customer|exists:customers,id',
            'payee_name' => 'nullable|string|max:255|required_if:payee_type,other',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf|max:2048',
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

                // Handle file upload and attachment removal
                $attachmentPath = $receiptVoucher->attachment;

                // Check if user wants to remove attachment
                if ($request->has('remove_attachment') && $request->remove_attachment == '1') {
                    // Delete old attachment if exists
                    if ($receiptVoucher->attachment && Storage::disk('public')->exists($receiptVoucher->attachment)) {
                        Storage::disk('public')->delete($receiptVoucher->attachment);
                    }
                    $attachmentPath = null;
                } elseif ($request->hasFile('attachment')) {
                    // Delete old attachment if exists
                    if ($receiptVoucher->attachment && Storage::disk('public')->exists($receiptVoucher->attachment)) {
                        Storage::disk('public')->delete($receiptVoucher->attachment);
                    }

                    $file = $request->file('attachment');
                    $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $attachmentPath = $file->storeAs('receipt-attachments', $fileName, 'public');
                }

                // Set payee information
                if ($request->payee_type === 'customer') {
                    $payeeType = 'customer';
                    $payeeId = $request->customer_id;
                    $payeeName = null;
                } else {
                    $payeeType = 'other';
                    $payeeId = null;
                    $payeeName = $request->payee_name;
                }

                // Update receipt
                $receiptVoucher->update([
                    'reference' => $request->reference ?: $receiptVoucher->reference,
                    'reference_number' => $request->reference,
                    'amount' => $totalAmount,
                    'date' => $request->date,
                    'description' => $request->description,
                    'attachment' => $attachmentPath,
                    'bank_account_id' => $request->bank_account_id,
                    'payee_type' => $payeeType,
                    'payee_id' => $payeeId,
                    'payee_name' => $payeeName,
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
                    'customer_id' => $payeeType === 'customer' ? $payeeId : null,
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
                        'customer_id' => $payeeType === 'customer' ? $payeeId : null,
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
                // Delete attachment if exists
                if ($receiptVoucher->attachment && Storage::disk('public')->exists($receiptVoucher->attachment)) {
                    Storage::disk('public')->delete($receiptVoucher->attachment);
                }

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

    /**
     * Download attachment.
     */
    public function downloadAttachment(Receipt $receiptVoucher)
    {
        if (!$receiptVoucher->attachment) {
            return redirect()->back()->withErrors(['error' => 'No attachment found.']);
        }

        if (!Storage::disk('public')->exists($receiptVoucher->attachment)) {
            return redirect()->back()->withErrors(['error' => 'Attachment file not found.']);
        }

        return Storage::disk('public')->download($receiptVoucher->attachment);
    }

    /**
     * Remove attachment.
     */
    public function removeAttachment(Receipt $receiptVoucher)
    {
        try {
            // Delete attachment file if exists
            if ($receiptVoucher->attachment && Storage::disk('public')->exists($receiptVoucher->attachment)) {
                Storage::disk('public')->delete($receiptVoucher->attachment);
            }

            // Update receipt to remove attachment reference
            $receiptVoucher->update(['attachment' => null]);

            return redirect()->back()->with('success', 'Attachment removed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to remove attachment: ' . $e->getMessage()]);
        }
    }
}

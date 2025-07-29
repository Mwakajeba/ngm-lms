<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\ChartAccount;
use App\Models\BankAccount;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Payment::with(['user', 'bankAccount', 'customer', 'branch', 'approvedBy', 'paymentItems.chartAccount']);

        // Filter by company scope
        if ($user->company_id) {
            $query->whereHas('branch', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        // Filter by branch scope
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('approved', false);
            }
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => $payments->total(),
            'approved' => Payment::where('approved', true)->count(),
            'pending' => Payment::where('approved', false)->count(),
            'total_amount' => Payment::sum('amount'),
        ];

        return view('accounting.payment-vouchers.index', compact('payments', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get chart accounts for the company
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })->with('accountClassGroup.accountClass')->get();

        // Get bank accounts
        $bankAccounts = BankAccount::whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })->get();

        // Get suppliers
        $suppliers = Supplier::where('company_id', $user->company_id)->get();

        // Get customers
        $customers = Customer::where('company_id', $user->company_id)->get();

        // Get branches
        $branches = Branch::where('company_id', $user->company_id)->get();

        return view('accounting.payment-vouchers.create', compact(
            'chartAccounts', 
            'bankAccounts', 
            'suppliers', 
            'customers', 
            'branches'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|max:255',
            'reference_type' => 'required|string|max:255',
            'reference_number' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'customer_id' => 'nullable|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'items' => 'required|array|min:1',
            'items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            
            // Calculate total amount
            $totalAmount = collect($request->items)->sum('amount');

            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('payment-attachments', 'public');
            }

            // Create payment voucher
            $payment = Payment::create([
                'reference' => $request->reference,
                'reference_type' => $request->reference_type,
                'reference_number' => $request->reference_number,
                'amount' => $totalAmount,
                'date' => $request->date,
                'description' => $request->description,
                'user_id' => $user->id,
                'attachment' => $attachmentPath,
                'bank_account_id' => $request->bank_account_id,
                'customer_id' => $request->customer_id,
                'branch_id' => $request->branch_id,
                'approved' => false,
            ]);

            // Create payment items
            foreach ($request->items as $item) {
                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'chart_account_id' => $item['chart_account_id'],
                    'amount' => $item['amount'],
                    'description' => $item['description'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('accounting.payment-vouchers')
                ->with('success', 'Payment voucher created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if payment creation fails
            if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }

            return back()->withInput()
                ->with('error', 'Failed to create payment voucher. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $paymentVoucher)
    {
        $paymentVoucher->load([
            'user', 
            'bankAccount', 
            'customer', 
            'branch', 
            'approvedBy',
            'paymentItems.chartAccount.accountClassGroup.accountClass'
        ]);

        return view('accounting.payment-vouchers.show', compact('paymentVoucher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $paymentVoucher)
    {
        // Check if payment is approved
        if ($paymentVoucher->approved) {
            return redirect()->route('accounting.payment-vouchers.show', $paymentVoucher)
                ->with('error', 'Cannot edit approved payment voucher.');
        }

        $user = Auth::user();
        
        // Get chart accounts for the company
        $chartAccounts = ChartAccount::whereHas('accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })->with('accountClassGroup.accountClass')->get();

        // Get bank accounts
        $bankAccounts = BankAccount::whereHas('chartAccount.accountClassGroup', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })->get();

        // Get suppliers
        $suppliers = Supplier::where('company_id', $user->company_id)->get();

        // Get customers
        $customers = Customer::where('company_id', $user->company_id)->get();

        // Get branches
        $branches = Branch::where('company_id', $user->company_id)->get();

        $paymentVoucher->load('paymentItems');

        return view('accounting.payment-vouchers.edit', compact(
            'paymentVoucher',
            'chartAccounts', 
            'bankAccounts', 
            'suppliers', 
            'customers', 
            'branches'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $paymentVoucher)
    {
        // Check if payment is approved
        if ($paymentVoucher->approved) {
            return redirect()->route('accounting.payment-vouchers.show', $paymentVoucher)
                ->with('error', 'Cannot edit approved payment voucher.');
        }

        $request->validate([
            'reference' => 'required|string|max:255',
            'reference_type' => 'required|string|max:255',
            'reference_number' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'customer_id' => 'nullable|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'items' => 'required|array|min:1',
            'items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total amount
            $totalAmount = collect($request->items)->sum('amount');

            // Handle file upload
            $attachmentPath = $paymentVoucher->attachment;
            if ($request->hasFile('attachment')) {
                // Delete old attachment
                if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                    Storage::disk('public')->delete($attachmentPath);
                }
                $attachmentPath = $request->file('attachment')->store('payment-attachments', 'public');
            }

            // Update payment voucher
            $paymentVoucher->update([
                'reference' => $request->reference,
                'reference_type' => $request->reference_type,
                'reference_number' => $request->reference_number,
                'amount' => $totalAmount,
                'date' => $request->date,
                'description' => $request->description,
                'attachment' => $attachmentPath,
                'bank_account_id' => $request->bank_account_id,
                'customer_id' => $request->customer_id,
                'branch_id' => $request->branch_id,
            ]);

            // Delete existing payment items
            $paymentVoucher->paymentItems()->delete();

            // Create new payment items
            foreach ($request->items as $item) {
                PaymentItem::create([
                    'payment_id' => $paymentVoucher->id,
                    'chart_account_id' => $item['chart_account_id'],
                    'amount' => $item['amount'],
                    'description' => $item['description'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('accounting.payment-vouchers')
                ->with('success', 'Payment voucher updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update payment voucher. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $paymentVoucher)
    {
        // Check if payment is approved
        if ($paymentVoucher->approved) {
            return redirect()->route('accounting.payment-vouchers.show', $paymentVoucher)
                ->with('error', 'Cannot delete approved payment voucher.');
        }

        try {
            DB::beginTransaction();

            // Delete attachment
            if ($paymentVoucher->attachment && Storage::disk('public')->exists($paymentVoucher->attachment)) {
                Storage::disk('public')->delete($paymentVoucher->attachment);
            }

            // Delete payment items
            $paymentVoucher->paymentItems()->delete();

            // Delete payment voucher
            $paymentVoucher->delete();

            DB::commit();

            return redirect()->route('accounting.payment-vouchers')
                ->with('success', 'Payment voucher deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete payment voucher. Please try again.');
        }
    }

    /**
     * Approve payment voucher
     */
    public function approve(Payment $paymentVoucher)
    {
        if ($paymentVoucher->approved) {
            return back()->with('error', 'Payment voucher is already approved.');
        }

        $paymentVoucher->update([
            'approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Payment voucher approved successfully!');
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(Payment $paymentVoucher)
    {
        if (!$paymentVoucher->attachment) {
            return back()->with('error', 'No attachment found.');
        }

        if (!Storage::disk('public')->exists($paymentVoucher->attachment)) {
            return back()->with('error', 'Attachment file not found.');
        }

        return Storage::disk('public')->download($paymentVoucher->attachment);
    }
}

<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // For now, we'll provide sample data since the model doesn't exist yet
        // In a real implementation, you would query the ReceiptVoucher model
        $receiptVouchers = collect([]); // Empty collection for now

        $stats = [
            'total' => 0,
            'this_month' => 0,
            'total_amount' => 0,
            'this_month_amount' => 0,
        ];

        return view('accounting.receipt-vouchers.index', compact('receiptVouchers', 'stats'));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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

        return view('accounting.receipt-vouchers.edit', compact('bankAccounts', 'customers', 'chartAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $bankAccounts = BankAccount::with('chartAccount.accountClassGroup.accountClass')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $chartAccounts = ChartAccount::with('accountClassGroup.accountClass')
            ->orderBy('account_name')
            ->get();

        return view('bank-accounts.create', compact('chartAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number',
        ]);

        BankAccount::create($request->all());

        return redirect()->route('accounting.bank-accounts')
            ->with('success', 'Bank account created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(BankAccount $bankAccount): View
    {
        $bankAccount->load('chartAccount.accountClassGroup.accountClass');

        return view('bank-accounts.show', compact('bankAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount): View
    {
        $chartAccounts = ChartAccount::with('accountClassGroup.accountClass')
            ->orderBy('account_name')
            ->get();

        return view('bank-accounts.edit', compact('bankAccount', 'chartAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $request->validate([
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number,' . $bankAccount->id,
        ]);

        $bankAccount->update($request->all());

        return redirect()->route('accounting.bank-accounts')
            ->with('success', 'Bank account updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        $bankAccount->delete();

        return redirect()->route('accounting.bank-accounts')
            ->with('success', 'Bank account deleted successfully!');
    }
}
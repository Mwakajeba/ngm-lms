<?php

namespace App\Http\Controllers;

use App\Models\AccountClassGroup;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChartAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $chartAccounts = ChartAccount::with(['accountClassGroup.accountClass'])->paginate(10);
        return view('chart-accounts.index', compact('chartAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $accountClassGroups = AccountClassGroup::with('accountClass')->get();
        return view('chart-accounts.create', compact('accountClassGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'account_class_group_id' => 'required|exists:account_class_groups,id',
            'account_code' => 'required|string|max:255|unique:chart_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'has_cash_flow' => 'boolean',
            'has_equity' => 'boolean',
        ]);

        // Handle boolean fields properly for unchecked checkboxes
        $data = $request->all();
        $data['has_cash_flow'] = $request->has('has_cash_flow');
        $data['has_equity'] = $request->has('has_equity');

        ChartAccount::create($data);

        return redirect()->route('accounting.accounts')
            ->with('success', 'Chart Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartAccount $chartAccount): View
    {
        $chartAccount->load(['accountClassGroup.accountClass']);
        return view('chart-accounts.show', compact('chartAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartAccount $chartAccount): View
    {
        $accountClassGroups = AccountClassGroup::with('accountClass')->get();
        return view('chart-accounts.edit', compact('chartAccount', 'accountClassGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartAccount $chartAccount): RedirectResponse
    {
        $request->validate([
            'account_class_group_id' => 'required|exists:account_class_groups,id',
            'account_code' => 'required|string|max:255|unique:chart_accounts,account_code,' . $chartAccount->id,
            'account_name' => 'required|string|max:255',
            'has_cash_flow' => 'boolean',
            'has_equity' => 'boolean',
        ]);

        // Handle boolean fields properly for unchecked checkboxes
        $data = $request->all();
        $data['has_cash_flow'] = $request->has('has_cash_flow');
        $data['has_equity'] = $request->has('has_equity');

        $chartAccount->update($data);

        return redirect()->route('accounting.accounts')
            ->with('success', 'Chart Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartAccount $chartAccount): RedirectResponse
    {
        $chartAccount->delete();

        return redirect()->route('accounting.accounts')
            ->with('success', 'Chart Account deleted successfully.');
    }
}

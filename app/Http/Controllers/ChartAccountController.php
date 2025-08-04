<?php

namespace App\Http\Controllers;

use App\Models\AccountClassGroup;
use App\Models\ChartAccount;
use App\Models\CashFlowCategory;
use App\Models\EquityCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Vinkla\Hashids\Facades\Hashids;

class ChartAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $chartAccounts = ChartAccount::with(['accountClassGroup.accountClass', 'cashFlowCategory', 'equityCategory'])->paginate(10);
        return view('chart-accounts.index', compact('chartAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $accountClassGroups = AccountClassGroup::with('accountClass')->get();
        $cashFlowCategories = CashFlowCategory::all();
        $equityCategories = EquityCategory::all();
        return view('chart-accounts.create', compact('accountClassGroups', 'cashFlowCategories', 'equityCategories'));
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
            'cash_flow_category_id' => 'nullable|exists:cash_flow_categories,id',
            'equity_category_id' => 'nullable|exists:equity_categories,id',
        ]);

        // Handle boolean fields properly for unchecked checkboxes
        $data = $request->all();
        $data['has_cash_flow'] = $request->has('has_cash_flow');
        $data['has_equity'] = $request->has('has_equity');

        // Set category IDs to null if checkboxes are unchecked
        if (!$data['has_cash_flow']) {
            $data['cash_flow_category_id'] = null;
        }
        if (!$data['has_equity']) {
            $data['equity_category_id'] = null;
        }

        ChartAccount::create($data);

        return redirect()->route('accounting.chart-accounts.index')
            ->with('success', 'Chart Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.chart-accounts.index')->withErrors(['Chart Account not found.']);
        }

        $chartAccount = ChartAccount::findOrFail($decoded[0]);
        $chartAccount->load(['accountClassGroup.accountClass', 'cashFlowCategory', 'equityCategory']);
        return view('chart-accounts.show', compact('chartAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.chart-accounts.index')->withErrors(['Chart Account not found.']);
        }

        $chartAccount = ChartAccount::findOrFail($decoded[0]);
        $accountClassGroups = AccountClassGroup::with('accountClass')->get();
        $cashFlowCategories = CashFlowCategory::all();
        $equityCategories = EquityCategory::all();
        return view('chart-accounts.edit', compact('chartAccount', 'accountClassGroups', 'cashFlowCategories', 'equityCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $encodedId)
    {
        // Decode chart account ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.chart-accounts.index')->withErrors(['Chart Account not found.']);
        }

        $chartAccount = ChartAccount::findOrFail($decoded[0]);

        $request->validate([
            'account_class_group_id' => 'required|exists:account_class_groups,id',
            'account_code' => 'required|string|max:255|unique:chart_accounts,account_code,' . $chartAccount->id,
            'account_name' => 'required|string|max:255',
            'has_cash_flow' => 'boolean',
            'has_equity' => 'boolean',
            'cash_flow_category_id' => 'nullable|exists:cash_flow_categories,id',
            'equity_category_id' => 'nullable|exists:equity_categories,id',
        ]);

        // Handle boolean fields properly for unchecked checkboxes
        $data = $request->all();
        $data['has_cash_flow'] = $request->has('has_cash_flow');
        $data['has_equity'] = $request->has('has_equity');

        // Set category IDs to null if checkboxes are unchecked
        if (!$data['has_cash_flow']) {
            $data['cash_flow_category_id'] = null;
        }
        if (!$data['has_equity']) {
            $data['equity_category_id'] = null;
        }

        $chartAccount->update($data);

        return redirect()->route('accounting.chart-accounts.index')
            ->with('success', 'Chart Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.chart-accounts.index')->withErrors(['Chart Account not found.']);
        }

        $chartAccount = ChartAccount::findOrFail($decoded[0]);
        $chartAccount->delete();

        return redirect()->route('accounting.chart-accounts.index')
            ->with('success', 'Chart Account deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CashCollateral;
use App\Models\Customer;
use App\Models\CashCollateralType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashCollateralController extends Controller
{
    public function index()
    {
        $cashCollaterals = CashCollateral::with(['customer', 'type'])
            ->where('branch_id', Auth::user()->branch_id)
            ->where('company_id', Auth::user()->company_id)
            ->paginate(10);

        return view('cash_collaterals.index', compact('cashCollaterals'));
    }

    public function create()
    {
        $customers = Customer::where('branch_id', Auth::user()->branch_id)
            ->where('company_id', Auth::user()->company_id)
            ->get();

        $types = CashCollateralType::where('is_active', true)->get();

        return view('cash_collaterals.create', compact('customers', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type_id' => 'required|exists:cash_collateral_types,id',
        ]);

        $data = $request->only(['customer_id', 'type_id', 'amount']);
        $user = Auth::user();
        $data['amount'] = 0;
        $data['branch_id'] = $user->branch_id;
        $data['company_id'] = $user->company_id;

        CashCollateral::create($data);

        return redirect()->route('cash_collaterals.index')->with('success', 'Cash Collateral created successfully.');
    }

    public function show(CashCollateral $cashCollateral)
    {
        $this->authorizeUserAccess($cashCollateral);

        $cashCollateral->load(['customer', 'type']);

        return view('cash_collaterals.show', compact('cashCollateral'));
    }

    public function edit(CashCollateral $cashCollateral)
    {
        $this->authorizeUserAccess($cashCollateral);

        $customers = Customer::where('branch_id', Auth::user()->branch_id)
            ->where('company_id', Auth::user()->company_id)
            ->get();

        $types = CashCollateralType::where('is_active', true)->get();

        return view('cash_collaterals.edit', compact('cashCollateral', 'customers', 'types'));
    }

    public function update(Request $request, CashCollateral $cashCollateral)
    {
        $this->authorizeUserAccess($cashCollateral);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type_id' => 'required|exists:cash_collateral_types,id',
        ]);

        $data = $request->only(['customer_id', 'type_id', 'amount']);
        $user = Auth::user();
        $data['amount'] = 0;
        $data['branch_id'] = $user->branch_id;
        $data['company_id'] = $user->company_id;

        $cashCollateral->update($data);

        return redirect()->route('cash_collaterals.index')->with('success', 'Cash Collateral updated successfully.');
    }

    public function destroy(CashCollateral $cashCollateral)
    {
        $this->authorizeUserAccess($cashCollateral);

        $cashCollateral->delete();

        return redirect()->route('cash_collaterals.index')->with('success', 'Cash Collateral deleted successfully.');
    }

    // Optional helper method to ensure users can only access their branch/company records
    protected function authorizeUserAccess(CashCollateral $cashCollateral)
    {
        $user = Auth::user();

        if ($cashCollateral->branch_id !== $user->branch_id || $cashCollateral->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
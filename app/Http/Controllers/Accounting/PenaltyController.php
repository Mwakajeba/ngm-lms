<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penalty;
use App\Models\Company;
use App\Models\Branch;
use App\Models\ChartAccount;
use Illuminate\Support\Facades\Validator;

class PenaltyController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        if ($companyId) {
            $penalties = Penalty::with(['company', 'branch', 'chartAccount', 'createdBy'])
                ->byCompany($companyId)
                ->orderBy('name')
                ->get();
        } else {
            $penalties = Penalty::with(['company', 'branch', 'chartAccount', 'createdBy'])
                ->orderBy('name')
                ->get();
        }

        $stats = [
            'total' => $penalties->count(),
            'active' => $penalties->where('status', 'active')->count(),
            'inactive' => $penalties->where('status', 'inactive')->count(),
            'fixed' => $penalties->where('penalty_type', 'fixed')->count(),
            'percentage' => $penalties->where('penalty_type', 'percentage')->count(),
            'outstanding_amount' => $penalties->where('deduction_type', 'outstanding_amount')->count(),
            'principal' => $penalties->where('deduction_type', 'principal')->count(),
        ];

        return view('accounting.penalties.index', compact('penalties', 'stats'));
    }

    public function create()
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        $companies = Company::orderBy('name')->get();

        if ($companyId) {
            $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }

        $chartAccounts = ChartAccount::orderBy('account_name')->get();
        $statusOptions = Penalty::getStatusOptions();
        $penaltyTypeOptions = Penalty::getPenaltyTypeOptions();
        $deductionTypeOptions = Penalty::getDeductionTypeOptions();

        return view('accounting.penalties.create', compact('companies', 'branches', 'chartAccounts', 'statusOptions', 'penaltyTypeOptions', 'deductionTypeOptions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'penalty_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'deduction_type' => 'required|in:outstanding_amount,principal',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();
        $companyId = $user->company_id ?? $request->company_id ?? Company::first()->id ?? 1;

        $penalty = Penalty::create([
            'name' => $request->name,
            'chart_account_id' => $request->chart_account_id,
            'penalty_type' => $request->penalty_type,
            'amount' => $request->amount,
            'deduction_type' => $request->deduction_type,
            'description' => $request->description,
            'status' => $request->status,
            'company_id' => $companyId,
            'branch_id' => $request->branch_id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('accounting.penalties.index')->with('success', 'Penalty created successfully!');
    }

    public function show(Penalty $penalty)
    {
        $penalty->load(['company', 'branch', 'chartAccount', 'createdBy', 'updatedBy']);

        return view('accounting.penalties.show', compact('penalty'));
    }

    public function edit(Penalty $penalty)
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        $companies = Company::orderBy('name')->get();

        if ($companyId) {
            $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }

        $chartAccounts = ChartAccount::orderBy('account_name')->get();
        $statusOptions = Penalty::getStatusOptions();
        $penaltyTypeOptions = Penalty::getPenaltyTypeOptions();
        $deductionTypeOptions = Penalty::getDeductionTypeOptions();

        return view('accounting.penalties.edit', compact('penalty', 'companies', 'branches', 'chartAccounts', 'statusOptions', 'penaltyTypeOptions', 'deductionTypeOptions'));
    }

    public function update(Request $request, Penalty $penalty)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'penalty_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'deduction_type' => 'required|in:outstanding_amount,principal',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();

        $penalty->update([
            'name' => $request->name,
            'chart_account_id' => $request->chart_account_id,
            'penalty_type' => $request->penalty_type,
            'amount' => $request->amount,
            'deduction_type' => $request->deduction_type,
            'description' => $request->description,
            'status' => $request->status,
            'company_id' => $request->company_id ?? $penalty->company_id,
            'branch_id' => $request->branch_id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('accounting.penalties.index')->with('success', 'Penalty updated successfully!');
    }

    public function destroy(Penalty $penalty)
    {
        try {
            $penalty->delete();
            return redirect()->route('accounting.penalties.index')->with('success', 'Penalty deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('accounting.penalties.index')->with('error', 'Failed to delete penalty: ' . $e->getMessage());
        }
    }

    public function changeStatus(Request $request, Penalty $penalty)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $penalty->update(['status' => $request->status]);

        $statusText = $request->status === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('accounting.penalties.show', $penalty)->with('success', "Penalty {$statusText} successfully!");
    }
}

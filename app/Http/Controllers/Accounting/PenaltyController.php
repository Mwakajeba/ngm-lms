<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penalty;
use App\Models\Company;
use App\Models\Branch;
use App\Models\ChartAccount;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

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

    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.penalties.index')->withErrors(['Penalty not found.']);
        }

        $penalty = Penalty::findOrFail($decoded[0]);
        $penalty->load(['company', 'branch', 'chartAccount', 'createdBy', 'updatedBy']);

        return view('accounting.penalties.show', compact('penalty'));
    }

    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.penalties.index')->withErrors(['Penalty not found.']);
        }

        $penalty = Penalty::findOrFail($decoded[0]);

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

    public function update(Request $request, $encodedId)
    {
        // Decode penalty ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.penalties.index')->withErrors(['Penalty not found.']);
        }

        $penalty = Penalty::findOrFail($decoded[0]);

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

        $penalty->update([
            'name' => $request->name,
            'chart_account_id' => $request->chart_account_id,
            'penalty_type' => $request->penalty_type,
            'amount' => $request->amount,
            'deduction_type' => $request->deduction_type,
            'description' => $request->description,
            'status' => $request->status,
            'company_id' => $companyId,
            'branch_id' => $request->branch_id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('accounting.penalties.index')->with('success', 'Penalty updated successfully!');
    }

    public function destroy($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.penalties.index')->withErrors(['Penalty not found.']);
        }

        $penalty = Penalty::findOrFail($decoded[0]);

        try {
            $penalty->delete();
            return redirect()->route('accounting.penalties.index')->with('success', 'Penalty deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete penalty. Please try again.');
        }
    }

    public function changeStatus(Request $request, $encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.penalties.index')->withErrors(['Penalty not found.']);
        }

        $penalty = Penalty::findOrFail($decoded[0]);

        $newStatus = $penalty->status === 'active' ? 'inactive' : 'active';
        $penalty->update(['status' => $newStatus]);

        $statusText = $newStatus === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('accounting.penalties.index')->with('success', "Penalty {$statusText} successfully!");
    }
}

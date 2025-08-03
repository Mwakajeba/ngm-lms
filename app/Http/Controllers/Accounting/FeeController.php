<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fee;
use App\Models\Company;
use App\Models\Branch;
use App\Models\ChartAccount;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class FeeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        if ($companyId) {
            $fees = Fee::with(['company', 'branch', 'chartAccount', 'createdBy'])
                ->byCompany($companyId)
                ->orderBy('name')
                ->get();
        } else {
            $fees = Fee::with(['company', 'branch', 'chartAccount', 'createdBy'])
                ->orderBy('name')
                ->get();
        }

        $stats = [
            'total' => $fees->count(),
            'active' => $fees->where('status', 'active')->count(),
            'inactive' => $fees->where('status', 'inactive')->count(),
            'fixed' => $fees->where('fee_type', 'fixed')->count(),
            'percentage' => $fees->where('fee_type', 'percentage')->count(),
        ];

        return view('accounting.fees.index', compact('fees', 'stats'));
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
        $statusOptions = Fee::getStatusOptions();
        $feeTypeOptions = Fee::getFeeTypeOptions();

        return view('accounting.fees.create', compact('companies', 'branches', 'chartAccounts', 'statusOptions', 'feeTypeOptions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'fee_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
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

        $fee = Fee::create([
            'name' => $request->name,
            'chart_account_id' => $request->chart_account_id,
            'fee_type' => $request->fee_type,
            'amount' => $request->amount,
            'description' => $request->description,
            'status' => $request->status,
            'company_id' => $companyId,
            'branch_id' => $request->branch_id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('accounting.fees.index')->with('success', 'Fee created successfully!');
    }

    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.fees.index')->withErrors(['Fee not found.']);
        }

        $fee = Fee::findOrFail($decoded[0]);
        $fee->load(['company', 'branch', 'chartAccount', 'createdBy', 'updatedBy']);

        return view('accounting.fees.show', compact('fee'));
    }

    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.fees.index')->withErrors(['Fee not found.']);
        }

        $fee = Fee::findOrFail($decoded[0]);

        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        $companies = Company::orderBy('name')->get();

        if ($companyId) {
            $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }

        $chartAccounts = ChartAccount::orderBy('account_name')->get();
        $statusOptions = Fee::getStatusOptions();
        $feeTypeOptions = Fee::getFeeTypeOptions();

        return view('accounting.fees.edit', compact('fee', 'companies', 'branches', 'chartAccounts', 'statusOptions', 'feeTypeOptions'));
    }

    public function update(Request $request, $encodedId)
    {
        // Decode fee ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.fees.index')->withErrors(['Fee not found.']);
        }

        $fee = Fee::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'fee_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
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

        $fee->update([
            'name' => $request->name,
            'chart_account_id' => $request->chart_account_id,
            'fee_type' => $request->fee_type,
            'amount' => $request->amount,
            'description' => $request->description,
            'status' => $request->status,
            'company_id' => $companyId,
            'branch_id' => $request->branch_id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('accounting.fees.index')->with('success', 'Fee updated successfully!');
    }

    public function destroy($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.fees.index')->withErrors(['Fee not found.']);
        }

        $fee = Fee::findOrFail($decoded[0]);

        try {
            $fee->delete();
            return redirect()->route('accounting.fees.index')->with('success', 'Fee deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete fee. Please try again.');
        }
    }

    public function changeStatus(Request $request, $encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('accounting.fees.index')->withErrors(['Fee not found.']);
        }

        $fee = Fee::findOrFail($decoded[0]);

        $newStatus = $fee->status === 'active' ? 'inactive' : 'active';
        $fee->update(['status' => $newStatus]);

        $statusText = $newStatus === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('accounting.fees.index')->with('success', "Fee {$statusText} successfully!");
    }
}

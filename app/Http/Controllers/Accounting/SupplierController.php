<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        if ($companyId) {
            $suppliers = Supplier::with(['company', 'branch', 'createdBy'])
                ->byCompany($companyId)
                ->orderBy('name')
                ->get();
        } else {
            // If user doesn't have company_id, show all suppliers
            $suppliers = Supplier::with(['company', 'branch', 'createdBy'])
                ->orderBy('name')
                ->get();
        }

        $stats = [
            'total' => $suppliers->count(),
            'active' => $suppliers->where('status', 'active')->count(),
            'inactive' => $suppliers->where('status', 'inactive')->count(),
            'blacklisted' => $suppliers->where('status', 'blacklisted')->count(),
        ];

        return view('accounting.suppliers.index', compact('suppliers', 'stats'));
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

        $regions = Region::orderBy('name')->get();
        $statusOptions = Supplier::getStatusOptions();

        return view('accounting.suppliers.create', compact('companies', 'branches', 'regions', 'statusOptions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|size:12|regex:/^[0-9]+$/',
            'address' => 'nullable|string|max:500',
            'region' => 'nullable|string|max:100',
            'company_registration_name' => 'nullable|string|max:255',
            'tin_number' => 'nullable|string|max:50|regex:/^[0-9]+$/',
            'vat_number' => 'nullable|string|max:50|regex:/^[0-9]+$/',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'products_or_services' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,blacklisted',
            'branch_id' => 'nullable|exists:branches,id',
        ], [
            'phone.size' => 'Phone number must be exactly 12 digits.',
            'phone.regex' => 'Phone number must contain only numbers.',
            'tin_number.regex' => 'TIN number must contain only numbers.',
            'vat_number.regex' => 'VAT number must contain only numbers.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = auth()->user();
        $companyId = $user->company_id ?? Company::first()->id ?? 1;

        $supplier = Supplier::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'region' => $request->region,
            'company_registration_name' => $request->company_registration_name,
            'tin_number' => $request->tin_number,
            'vat_number' => $request->vat_number,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'account_name' => $request->account_name,
            'products_or_services' => $request->products_or_services,
            'status' => $request->status,
            'company_id' => $companyId,
            'branch_id' => $request->branch_id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('accounting.suppliers.index')
            ->with('success', 'Supplier created successfully!');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['company', 'branch', 'createdBy', 'updatedBy']);

        return view('accounting.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        $user = auth()->user();
        $companyId = $user->company_id ?? null;

        $companies = Company::orderBy('name')->get();

        if ($companyId) {
            $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }

        $regions = Region::orderBy('name')->get();
        $statusOptions = Supplier::getStatusOptions();

        return view('accounting.suppliers.edit', compact('supplier', 'companies', 'branches', 'regions', 'statusOptions'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|size:12|regex:/^[0-9]+$/',
            'address' => 'nullable|string|max:500',
            'region' => 'nullable|string|max:100',
            'company_registration_name' => 'nullable|string|max:255',
            'tin_number' => 'nullable|string|max:50|regex:/^[0-9]+$/',
            'vat_number' => 'nullable|string|max:50|regex:/^[0-9]+$/',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'products_or_services' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,blacklisted',
            'branch_id' => 'nullable|exists:branches,id',
        ], [
            'phone.size' => 'Phone number must be exactly 12 digits.',
            'phone.regex' => 'Phone number must contain only numbers.',
            'tin_number.regex' => 'TIN number must contain only numbers.',
            'vat_number.regex' => 'VAT number must contain only numbers.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $supplier->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'region' => $request->region,
            'company_registration_name' => $request->company_registration_name,
            'tin_number' => $request->tin_number,
            'vat_number' => $request->vat_number,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'account_name' => $request->account_name,
            'products_or_services' => $request->products_or_services,
            'status' => $request->status,
            'branch_id' => $request->branch_id,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('accounting.suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('accounting.suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }

    public function changeStatus(Request $request, Supplier $supplier)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,blacklisted'
        ]);

        $supplier->update([
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'Supplier status updated successfully!');
    }
}
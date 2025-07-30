<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Region;
use App\Models\User;
use App\Models\CashCollateralType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    // Display all customers
    public function index()
    {
        $customers = Customer::with(['branch', 'company', 'user', 'region', 'district'])->latest()->get();
        return view('customers.index', compact('customers'));
    }

    // Show form to create a new customer
    public function create()
    {
        $branchId = auth()->user()->branch_id;
        $loanOfficers = collect(); // empty by default

        // Get all users with loan officer role
        // if (Role::where('name', 'loanofficer')->where('guard_name', 'web')->exists()) {
        //     $loanOfficers = User::role('loanofficer')->get(); // Removed branch filter to show all loan officers
        // }
        $loanOfficers = User::all();

        $collateralTypes = CashCollateralType::where('is_active', 1)->get(); // active types only
        $branches = Branch::all();
        $companies = Company::all();
        $registrars = User::all();
        $regions = Region::all();
        
        return view('customers.create', compact('branches', 'companies', 'registrars', 'regions', 'loanOfficers', 'collateralTypes'));
    }

    // Store a new customer
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'phone1' => 'required|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'dob' => 'required|date',
            'sex' => 'required|in:M,F',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'work' => 'nullable|string|max:255',
            'workAddress' => 'nullable|string|max:500',
            'idType' => 'nullable|string|max:100',
            'idNumber' => 'nullable|string|max:100',
            'relation' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'loan_officer_ids' => 'nullable|array',
            'loan_officer_ids.*' => 'exists:users,id',
            'has_cash_collateral' => 'nullable|boolean',
            'collateral_type_id' => 'nullable|exists:cash_collateral_types,id', // Made optional
        ]);

        $data = $request->except(['customerNo', 'loan_officer_ids', 'collateral_type_id']);
        $password = 12345;
        $date = date('Y-m-d');
        $data['customerNo'] = 100000 + (\App\Models\Customer::max('id') ?? 0) + 1;
        $data['password'] = Hash::make($password);
        $data['branch_id'] = auth()->user()->branch_id;
        $data['company_id'] = auth()->user()->company_id;
        $data['registrar'] = auth()->id();
        $data['dateRegistered'] = $date;
        $data['has_cash_collateral'] = $request->has('has_cash_collateral') ? true : false; // Set boolean value

        // Upload files
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('documents', 'public');
        }

        DB::beginTransaction();
        try {
            $customer = Customer::create($data);

            // Attach loan officers if any selected
            if ($request->has('loan_officer_ids') && !empty($request->loan_officer_ids)) {
                foreach ($request->loan_officer_ids as $officerId) {
                    DB::table('customer_officer')->insert([
                        'customer_id' => $customer->id,
                        'officer_id' => $officerId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Create cash collateral only if selected and type provided
            if ($request->has('has_cash_collateral') && $request->has('collateral_type_id') && $request->collateral_type_id) {
                \App\Models\CashCollateral::create([
                    'customer_id' => $customer->id,
                    'type_id' => $request->input('collateral_type_id'),
                    'amount' => 0,
                    'branch_id' => auth()->user()->branch_id,
                    'company_id' => auth()->user()->company_id,
                ]);
            }

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }

    // Display one customer
    public function show(Customer $customer)
    {
        $customer->load('collaterals.type', 'loans', 'loanOfficers');
        return view('customers.show', compact('customer'));
    }

    // Show form to edit a customer
    public function edit(Customer $customer)
    {
        $branchId = auth()->user()->branch_id;
        $loanOfficers = collect(); // empty by default

        // Get all users with loan officer role
        // if (Role::where('name', 'loanofficer')->where('guard_name', 'web')->exists()) {
        //     $loanOfficers = User::role('loanofficer')->get(); // Removed branch filter to show all loan officers
        // }

        $loanOfficers = User::all();

        $collateralTypes = CashCollateralType::where('is_active', 1)->get();
        $branches = Branch::all();
        $companies = Company::all();
        $registrars = User::all();
        $regions = Region::all();
        
        // Load loan officers for this customer
        $customer->load('loanOfficers');
        
        return view('customers.edit', compact('branches', 'companies', 'registrars', 'regions', 'loanOfficers', 'collateralTypes', 'customer'));
    }

    // Update customer data
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000', // Added description validation
            'phone1' => 'required|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'dob' => 'required|date',
            'sex' => 'required|in:M,F',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'work' => 'nullable|string|max:255',
            'workAddress' => 'nullable|string|max:500',
            'idType' => 'nullable|string|max:100',
            'idNumber' => 'nullable|string|max:100',
            'relation' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'password' => 'nullable|min:6',
            'loan_officer_ids' => 'nullable|array',
            'loan_officer_ids.*' => 'exists:users,id',
            'has_cash_collateral' => 'nullable|boolean',
            'collateral_type_id' => 'nullable|exists:cash_collateral_types,id', // Made optional
        ]);

        $data = $request->except(['customerNo', 'loan_officer_ids', 'collateral_type_id']);

        // Set these from logged-in user
        $data['branch_id'] = auth()->user()->branch_id;
        $data['company_id'] = auth()->user()->company_id;
        $data['registrar'] = auth()->id();
        $data['has_cash_collateral'] = $request->has('has_cash_collateral') ? true : false; // Set boolean value

        // Hash password only if provided
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']); // Don't overwrite with null
        }

        // Photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        // Document upload
        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('documents', 'public');
        }

        DB::beginTransaction();
        try {
            $customer->update($data);

            // Sync loan officers
            if ($request->has('loan_officer_ids')) {
                // Delete previous ones
                DB::table('customer_officer')->where('customer_id', $customer->id)->delete();

                // Insert new ones
                if (!empty($request->loan_officer_ids)) {
                    foreach ($request->loan_officer_ids as $officerId) {
                        DB::table('customer_officer')->insert([
                            'customer_id' => $customer->id,
                            'officer_id' => $officerId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            } else {
                // If none selected, remove all previous
                DB::table('customer_officer')->where('customer_id', $customer->id)->delete();
            }

            // Handle cash collateral
            if ($request->has('has_cash_collateral') && $request->has('collateral_type_id') && $request->collateral_type_id) {
                // Check if collateral already exists
                $existingCollateral = \App\Models\CashCollateral::where('customer_id', $customer->id)->first();
                
                if ($existingCollateral) {
                    $existingCollateral->update([
                        'type_id' => $request->input('collateral_type_id'),
                    ]);
                } else {
                    \App\Models\CashCollateral::create([
                        'customer_id' => $customer->id,
                        'type_id' => $request->input('collateral_type_id'),
                        'amount' => 0,
                        'branch_id' => auth()->user()->branch_id,
                        'company_id' => auth()->user()->company_id,
                    ]);
                }
            } else {
                // If not checked, remove existing collateral
                \App\Models\CashCollateral::where('customer_id', $customer->id)->delete();
            }

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update customer: ' . $e->getMessage());
        }
    }

    // Delete customer
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }
}
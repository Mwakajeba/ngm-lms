<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Region;
use App\Models\User;
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
        $loanOfficers = collect(); // empty by default

        if (Role::where('name', 'loanofficer')->where('guard_name', 'web')->exists()) {
            $loanOfficers = User::role('loanofficer')->where('branch_id', $branchId)->get();
        }
        $branchId = auth()->user()->branch_id;


        $branches = Branch::all();
        $companies = Company::all();
        $registrars = User::all();
        $regions = Region::all();
        return view('customers.create', compact('branches', 'companies', 'registrars','regions','loanOfficers'));
    }

    // Show form to create a new customer
   public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone1' => 'required',
            'dob' => 'required|date',
            'sex' => 'required|in:M,F',
            'loan_officer_ids' => 'nullable|array',
            'loan_officer_ids.*' => 'exists:users,id',
        ]);

        //$data = $request->all();
        $data = $request->except(['customerNo', 'loan_officer_ids']);
        $password = 12345;
        $date = date('Y-m-d');
        $data['customerNo'] = 100000 + (\App\Models\Customer::max('id') ?? 0) + 1;
        $data['password'] = Hash::make($password);
        $data['branch_id'] = auth()->user()->branch_id;
        $data['company_id'] = auth()->user()->company_id;
        $data['registrar'] = auth()->id();
        $data['dateRegistered'] = $date;

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
           if ($request->has('loan_officer_ids')) {
                foreach ($request->loan_officer_ids as $officerId) {
                    DB::table('customer_officer')->insert([
                        'customer_id' => $customer->id,
                        'officer_id' => $officerId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
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
        return view('customers.show', compact('customer'));
    }

    // Show form to edit a customer
    public function edit(Customer $customer)
    {
        $branchId = auth()->user()->branch_id;
        $loanOfficers = collect(); // empty by default

        if (Role::where('name', 'loanofficer')->where('guard_name', 'web')->exists()) {
            $loanOfficers = User::role('loanofficer')->where('branch_id', $branchId)->get();
        }

        $branches = Branch::all();
        $companies = Company::all();
        $registrars = User::all();
        $regions = Region::all();
        return view('customers.edit', compact('branches', 'companies', 'registrars','regions','loanOfficers'));
    }

    // Update customer data
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'customerNo' => 'required|unique:customers,customerNo,' . $customer->id,
            'name' => 'required',
            'phone1' => 'required',
            'dob' => 'required|date',
            'sex' => 'required|in:M,F',
            'password' => 'nullable|min:6',
        ]);

        $data = $request->all();
        //$data = $request->except(['customerNo', 'loan_officers']);
        //$data['customerNo'] = 100000 + (\App\Models\Customer::max('id') ?? 0) + 1;

        // Set these from logged-in user
        $data['branch_id'] = auth()->user()->branch_id;
        $data['company_id'] = auth()->user()->company_id;
        $data['registrar'] = auth()->id();

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

        $customer->update($data);

        // Sync loan officers
        if ($request->has('loan_officer_ids')) {
            // Delete previous ones
            DB::table('customer_officer')->where('customer_id', $customer->id)->delete();

            // Insert new ones
            foreach ($request->loan_officer_ids as $officerId) {
                DB::table('customer_officer')->insert([
                    'customer_id' => $customer->id,
                    'officer_id' => $officerId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            // If none selected, remove all previous
            DB::table('customer_officer')->where('customer_id', $customer->id)->delete();
        }

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }


    // Delete customer
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Region;
use App\Models\User;
use App\Models\CashCollateralType;
use App\Models\Filetype;
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

        $loanOfficers = User::all();
        $filetypes = Filetype::orderBy('name')->get();

        $collateralTypes = CashCollateralType::where('is_active', 1)->get(); // active types only
        $branches = Branch::all();
        $companies = Company::all();
        $registrars = User::all();
        $regions = Region::all();
        
        return view('customers.create', compact('branches', 'companies', 'registrars', 'regions', 'loanOfficers', 'collateralTypes','filetypes'));
    }

    // Store a new customer
    public function store(Request $request)
    {
        // Basic validation rules
        $rules = [
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
            'loan_officer_ids' => 'nullable|array',
            'loan_officer_ids.*' => 'exists:users,id',
            'has_cash_collateral' => 'nullable|boolean',
            'collateral_type_id' => 'nullable|exists:cash_collateral_types,id',

            // Dynamic filetypes + documents
            'filetypes'   => 'nullable|array',
            'filetypes.*' => 'exists:filetypes,id',
            'documents'   => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];

        $validated = $request->validate($rules);

        // Prepare customer data
        $data = $request->except(['customerNo', 'loan_officer_ids', 'collateral_type_id', 'filetypes', 'documents']);
        $password = 12345;
        $date = now()->toDateString();

        $data['customerNo'] = 100000 + (\App\Models\Customer::max('id') ?? 0) + 1;
        $data['password'] = Hash::make($password);
        $data['branch_id'] = auth()->user()->branch_id;
        $data['company_id'] = auth()->user()->company_id;
        $data['registrar'] = auth()->id();
        $data['dateRegistered'] = $date;
        $data['has_cash_collateral'] = $request->has('has_cash_collateral');

        // Upload photo
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        // Upload document
        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('documents', 'public');
        }

        DB::beginTransaction();
        try {
            $customer = \App\Models\Customer::create($data);

            // Attach loan officers
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

            // Add cash collateral
            if ($request->has('has_cash_collateral') && $request->input('collateral_type_id')) {
                \App\Models\CashCollateral::create([
                    'customer_id' => $customer->id,
                    'type_id' => $request->input('collateral_type_id'),
                    'amount' => 0,
                    'branch_id' => auth()->user()->branch_id,
                    'company_id' => auth()->user()->company_id,
                ]);
            }

            // Save uploaded filetypes + documents
            if ($request->has('filetypes') && $request->hasFile('documents')) {
                $filetypes = $request->input('filetypes');
                $documents = $request->file('documents');

                foreach ($filetypes as $index => $filetypeId) {
                    if (isset($documents[$index])) {
                        $file = $documents[$index];
                        $path = $file->store('documents', 'public');

                        DB::table('customer_file_types')->insert([
                            'customer_id'   => $customer->id,
                            'filetype_id'   => $filetypeId,
                            'document_path' => $path,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }


    // Display one customer
    public function show(Customer $customer)
    {
        $customer->load('collaterals.type', 'loans', 'loanOfficers','filetypes');
        //$customer = Customer::with('filetypes')->findOrFail($id);
        return view('customers.show', compact('customer'));
    }

    // Show form to edit a customer
    public function edit(Customer $customer)
    {
        $branchId = auth()->user()->branch_id;
        $loanOfficers = collect(); // empty by default

        $loanOfficers = User::all();

        $collateralTypes = CashCollateralType::where('is_active', 1)->get();
        $branches = Branch::all();
        $companies = Company::all();
        $registrars = User::all();
        $regions = Region::all();

        $filetypes = Filetype::orderBy('name')->get();
        
        // Load loan officers for this customer
        $customer->load('loanOfficers');
        
        return view('customers.edit', compact('branches', 'companies', 'registrars', 'regions', 'loanOfficers', 'collateralTypes', 'customer', 'filetypes'));
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
            'password' => 'nullable|min:6',
            'loan_officer_ids' => 'nullable|array',
            'loan_officer_ids.*' => 'exists:users,id',
            'has_cash_collateral' => 'nullable|boolean',
            'collateral_type_id' => 'nullable|exists:cash_collateral_types,id', // Made optional

            'filetypes' => 'nullable|array',
            'filetypes.*' => 'exists:filetypes,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
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

            // Sync File Types and Uploaded Documents
            if ($request->has('filetypes') && $request->hasFile('documents')) {
                $filetypes = $request->filetypes;
                $documents = $request->file('documents');

                // Delete existing filetype entries to prevent duplicates
                DB::table('customer_file_types')->where('customer_id', $customer->id)->delete();

                foreach ($filetypes as $index => $filetypeId) {
                    if (isset($documents[$index])) {
                        $file = $documents[$index];
                        $path = $file->store('documents', 'public');

                        DB::table('customer_file_types')->insert([
                            'customer_id' => $customer->id,
                            'filetype_id' => $filetypeId,
                            'document_path' => $path,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
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
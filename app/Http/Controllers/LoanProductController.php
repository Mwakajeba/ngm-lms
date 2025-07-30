<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use App\Models\ChartAccount;
use App\Models\Fee;
use App\Models\Penalty;
use App\Models\CashCollateralType;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoanProductController extends Controller
{
    /**
     * Display a listing of loan products
     */
    public function index()
    {
        $loanProducts = LoanProduct::with([
            'principalReceivableAccount',
            'interestReceivableAccount',
            'interestRevenueAccount'
        ])->latest()->get();

        return view('loan-products.index', compact('loanProducts'));
    }

    /**
     * Show the form for creating a new loan product
     */
    public function create()
    {
        // Get chart accounts for dropdowns
        $chartAccounts = ChartAccount::all();

        // Get fees and penalties for dropdowns
        $fees = Fee::where('status', 'active')->get();
        $penalties = Penalty::where('status', 'active')->get();

        // Get cash collateral types for dropdowns
        $cashCollateralTypes = CashCollateralType::where('is_active', 1)->get();

        // Get roles for approval levels
        $roles = Role::whereNotIn('name', ['admin', 'super-admin'])->orderBy('name')->get();

        // Define options for dropdowns
        $productTypes = [
            'personal' => 'Personal Loan',
            'business' => 'Business Loan',
            'mortgage' => 'Mortgage Loan',
            'vehicle' => 'Vehicle Loan',
            'education' => 'Education Loan',
            'agriculture' => 'Agriculture Loan'
        ];

        $interestCycles = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi_annually' => 'Semi Annually',
            'annually' => 'Annually'
        ];

        $interestMethods = [
            'flat_rate' => 'Flat Rate',
            'reducing_balance_with_equal_installment' => 'Reducing Balance with Equal Installment',
            'reducing_balance_with_equal_principal' => 'Reducing Balance with Equal Principal',
        ];

        $topUpTypes = [
            'percentage' => 'Percentage',
            'fixed_amount' => 'Fixed Amount',
            'none' => 'None'
        ];

        $cashCollateralValueTypes = [
            'percentage' => 'Percentage',
            'fixed_amount' => 'Fixed Amount'
        ];

        return view('loan-products.create', compact(
            'chartAccounts',
            'fees',
            'penalties',
            'cashCollateralTypes',
            'roles',
            'productTypes',
            'interestCycles',
            'interestMethods',
            'topUpTypes',
            'cashCollateralValueTypes'
        ));
    }

    /**
     * Store a newly created loan product
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:loan_products',
            'product_type' => 'required|string|max:100',
            'minimum_interest_rate' => 'required|numeric|min:0|max:100',
            'maximum_interest_rate' => 'required|numeric|min:0|max:100|gte:minimum_interest_rate',
            'interest_cycle' => 'required|string|max:50',
            'interest_method' => 'required|string|max:50',
            'minimum_principal' => 'required|numeric|min:0',
            'maximum_principal' => 'required|numeric|min:0|gte:minimum_principal',
            'minimum_period' => 'required|integer|min:1',
            'maximum_period' => 'required|integer|min:1|gte:minimum_period',
            'has_top_up' => 'boolean',
            'top_up_type' => 'required_if:has_top_up,1|string|max:50',
            'top_up_type_value' => 'required_if:top_up_type,percentage,fixed_amount|numeric|min:0',
            'has_cash_collateral' => 'boolean',
            'cash_collateral_type' => 'nullable|string|max:100',
            'cash_collateral_value_type' => 'nullable|string|max:50',
            'cash_collateral_value' => 'nullable|numeric|min:0',
            'has_approval_levels' => 'boolean',
            'approval_levels' => 'nullable|string|max:500',
            'principal_receivable_account_id' => 'required|exists:chart_accounts,id',
            'interest_receivable_account_id' => 'required|exists:chart_accounts,id',
            'interest_revenue_account_id' => 'required|exists:chart_accounts,id',
            'fees_id' => 'nullable|array',
            'fees_id.*' => 'nullable|exists:fees,id',
            'penalty_id' => 'nullable|array',
            'penalty_id.*' => 'nullable|exists:penalties,id',
            'repayment_order' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Custom validation for approval levels
        if ($request->has('has_approval_levels') && $request->approval_levels) {
            $approvalRoles = explode(',', $request->approval_levels);
            $validRoles = Role::pluck('id')->toArray();

            foreach ($approvalRoles as $roleId) {
                $roleId = trim($roleId);
                if (!empty($roleId) && !in_array((int) $roleId, $validRoles)) {
                    return redirect()->back()
                        ->withErrors(['approval_levels' => 'Invalid role ID "' . $roleId . '" in approval levels.'])
                        ->withInput();
                }
            }
        }

        // Custom validation for repayment order
        if ($request->repayment_order) {
            $repaymentComponents = explode(',', $request->repayment_order);
            $validComponents = ['principal', 'interest', 'fees', 'penalties'];

            foreach ($repaymentComponents as $component) {
                $component = trim($component);
                if (!empty($component) && !in_array($component, $validComponents)) {
                    return redirect()->back()
                        ->withErrors(['repayment_order' => 'Invalid component "' . $component . '" in repayment order.'])
                        ->withInput();
                }
            }
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['has_cash_collateral'] = $request->has('has_cash_collateral');
            $data['has_approval_levels'] = $request->has('has_approval_levels');

            // Handle top up configuration
            if (!$request->has('has_top_up')) {
                $data['top_up_type'] = 'none';
                $data['top_up_type_value'] = null;
            }

            // Handle fees and penalties arrays
            $data['fees_ids'] = $request->fees_id ? array_filter($request->fees_id) : null;
            $data['penalty_ids'] = $request->penalty_id ? array_filter($request->penalty_id) : null;

            $loanProduct = LoanProduct::create($data);

            DB::commit();

            return redirect()->route('loan-products.index')
                ->with('success', 'Loan product created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error creating loan product: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified loan product
     */
    public function show(LoanProduct $loanProduct)
    {
        $loanProduct->load([
            'principalReceivableAccount',
            'interestReceivableAccount',
            'interestRevenueAccount',
            'cashCollateralType'
            // TODO: Add loan_product_id to loans table and uncomment this
            // 'loans'
        ]);

        return view('loan-products.show', compact('loanProduct'));
    }

    /**
     * Show the form for editing the specified loan product
     */
    public function edit(LoanProduct $loanProduct)
    {
        // Get chart accounts for dropdowns
        $chartAccounts = ChartAccount::all();

        // Get fees and penalties for dropdowns
        $fees = Fee::where('status', 'active')->get();
        $penalties = Penalty::where('status', 'active')->get();

        // Get cash collateral types for dropdowns
        $cashCollateralTypes = CashCollateralType::where('is_active', 1)->get();

        // Get roles for approval levels
        $roles = Role::whereNotIn('name', ['admin', 'super-admin'])->orderBy('name')->get();

        // Define options for dropdowns
        $productTypes = [
            'personal' => 'Personal Loan',
            'business' => 'Business Loan',
            'mortgage' => 'Mortgage Loan',
            'vehicle' => 'Vehicle Loan',
            'education' => 'Education Loan',
            'agriculture' => 'Agriculture Loan'
        ];

        $interestCycles = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi_annually' => 'Semi Annually',
            'annually' => 'Annually'
        ];

        $interestMethods = [
            'flat_rate' => 'Flat Rate',
            'reducing_balance_with_equal_installment' => 'Reducing Balance with Equal Installment',
            'reducing_balance_with_equal_principal' => 'Reducing Balance with Equal Principal',
        ];

        $topUpTypes = [
            'percentage' => 'Percentage',
            'fixed_amount' => 'Fixed Amount',
            'none' => 'None'
        ];

        $cashCollateralValueTypes = [
            'percentage' => 'Percentage',
            'fixed_amount' => 'Fixed Amount'
        ];

        return view('loan-products.edit', compact(
            'loanProduct',
            'chartAccounts',
            'fees',
            'penalties',
            'cashCollateralTypes',
            'roles',
            'productTypes',
            'interestCycles',
            'interestMethods',
            'topUpTypes',
            'cashCollateralValueTypes'
        ));
    }

    /**
     * Update the specified loan product
     */
    public function update(Request $request, LoanProduct $loanProduct)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:loan_products,name,' . $loanProduct->id,
            'product_type' => 'required|string|max:100',
            'minimum_interest_rate' => 'required|numeric|min:0|max:100',
            'maximum_interest_rate' => 'required|numeric|min:0|max:100|gte:minimum_interest_rate',
            'interest_cycle' => 'required|string|max:50',
            'interest_method' => 'required|string|max:50',
            'minimum_principal' => 'required|numeric|min:0',
            'maximum_principal' => 'required|numeric|min:0|gte:minimum_principal',
            'minimum_period' => 'required|integer|min:1',
            'maximum_period' => 'required|integer|min:1|gte:minimum_period',
            'has_top_up' => 'boolean',
            'top_up_type' => 'required_if:has_top_up,1|string|max:50',
            'top_up_type_value' => 'required_if:top_up_type,percentage,fixed_amount|numeric|min:0',
            'has_cash_collateral' => 'boolean',
            'cash_collateral_type' => 'nullable|string|max:100',
            'cash_collateral_value_type' => 'nullable|string|max:50',
            'cash_collateral_value' => 'nullable|numeric|min:0',
            'has_approval_levels' => 'boolean',
            'approval_levels' => 'nullable|string|max:500',
            'principal_receivable_account_id' => 'required|exists:chart_accounts,id',
            'interest_receivable_account_id' => 'required|exists:chart_accounts,id',
            'interest_revenue_account_id' => 'required|exists:chart_accounts,id',
            'fees_id' => 'nullable|array',
            'fees_id.*' => 'nullable|exists:fees,id',
            'penalty_id' => 'nullable|array',
            'penalty_id.*' => 'nullable|exists:penalties,id',
            'repayment_order' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Custom validation for approval levels
        if ($request->has('has_approval_levels') && $request->approval_levels) {
            $approvalRoles = explode(',', $request->approval_levels);
            $validRoles = Role::pluck('id')->toArray();

            foreach ($approvalRoles as $roleId) {
                $roleId = trim($roleId);
                if (!empty($roleId) && !in_array((int) $roleId, $validRoles)) {
                    return redirect()->back()
                        ->withErrors(['approval_levels' => 'Invalid role ID "' . $roleId . '" in approval levels.'])
                        ->withInput();
                }
            }
        }

        // Custom validation for repayment order
        if ($request->repayment_order) {
            $repaymentComponents = explode(',', $request->repayment_order);
            $validComponents = ['principal', 'interest', 'fees', 'penalties'];

            foreach ($repaymentComponents as $component) {
                $component = trim($component);
                if (!empty($component) && !in_array($component, $validComponents)) {
                    return redirect()->back()
                        ->withErrors(['repayment_order' => 'Invalid component "' . $component . '" in repayment order.'])
                        ->withInput();
                }
            }
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['has_cash_collateral'] = $request->has('has_cash_collateral');
            $data['has_approval_levels'] = $request->has('has_approval_levels');

            // Handle top up configuration
            if (!$request->has('has_top_up')) {
                $data['top_up_type'] = 'none';
                $data['top_up_type_value'] = null;
            }

            // Handle fees and penalties arrays
            $data['fees_ids'] = $request->fees_id ? array_filter($request->fees_id) : null;
            $data['penalty_ids'] = $request->penalty_id ? array_filter($request->penalty_id) : null;

            $loanProduct->update($data);

            DB::commit();

            return redirect()->route('loan-products.index')
                ->with('success', 'Loan product updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error updating loan product: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified loan product
     */
    public function destroy(LoanProduct $loanProduct)
    {
        // TODO: Add loan_product_id to loans table and uncomment this check
        // Check if there are any loans using this product
        // if ($loanProduct->loans()->count() > 0) {
        //     return redirect()->route('loan-products.index')
        //         ->with('error', 'Cannot delete loan product. There are existing loans using this product.');
        // }

        DB::beginTransaction();
        try {
            $loanProduct->delete();

            DB::commit();

            return redirect()->route('loan-products.index')
                ->with('success', 'Loan product deleted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('loan-products.index')
                ->with('error', 'Error deleting loan product: ' . $e->getMessage());
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Group;
use App\Models\Loan;
use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class LoanController extends Controller
{
    public function index()
    {

        return view('loans.index');
    }

    public function listLoans()
    {
        $branchId = auth()->user()->branch_id;
        $loans = Loan::with('customer', 'product', 'branch')
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->latest()->get();

        return view('loans.list', compact('loans'));
    }

    public function create()
    {
        $customers = Customer::all();
        $groups = Group::all();
        $products = LoanProduct::all();
        $bankAccounts = BankAccount::all();
        $sectors = ['Agriculture', 'Business', 'Education', 'Health', 'Other']; // Example sectors
        return view('loans.create', compact('customers', 'groups', 'products', 'sectors', 'bankAccounts'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'    => 'required|exists:loan_products,id',
            'period'        => 'required|integer|min:1',
            'interest'      => 'required|numeric|min:0',
            'amount'        => 'required|numeric|min:0',
            'date_applied'  => 'required|date|before_or_equal:today',
            'customer_id'   => 'required|exists:customers,id',
            'group_id'      => 'required|exists:groups,id',
            'account_id'    => 'required|exists:bank_accounts,id',
            'sector'        => 'required|string',
        ]);

        $product = LoanProduct::findOrFail($validated['product_id']);

        // Validate product constraints
        $this->validateProductLimits($validated, $product);

        try {
            DB::transaction(function () use ($validated, $product) {
                // Step 1: Create the loan
                $loan = Loan::create([
                    'product_id'      => $validated['product_id'],
                    'period'          => $validated['period'],
                    'interest'        => $validated['interest'],
                    'date_applied'    => $validated['date_applied'],
                    'amount'          => $validated['amount'],
                    'group_id'        => $validated['group_id'],
                    'customer_id'     => $validated['customer_id'],
                    'bank_account_id' => $validated['account_id'],
                    'disbursed_on'    => $validated['date_applied'],
                    'sector'          => $validated['sector'],
                    'branch_id'       => auth()->user()->branch_id,
                    'status'          => 'active',
                ]);

                // Step 2: Calculate interest and dates
                $interestAmount = $loan->calculateInterestAmount($validated['interest']);
                $dates = $loan->getRepaymentDates();

                // Step 3: Update loan with totals and dates
                $loan->update([
                    'interest_amount'       => $interestAmount,
                    'amount_total'          => $loan->amount + $interestAmount,
                    'first_repayment_date'  => $dates['first_repayment_date'],
                    'last_repayment_date'   => $dates['last_repayment_date'],
                ]);
            });

            return redirect()->route('loans.list')->with('success', 'Loan application created successfully.');
        } catch (\Throwable $th) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to process loan application: ' . $th->getMessage()])
                ->withInput();
        }
    }


    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loans.list')->withErrors(['Loan not found.']);
        }

        $loan = Loan::findOrFail($decoded[0]);

        // Fetch supporting data
        $customers     = Customer::all();
        $groups        = Group::all();
        $products      = LoanProduct::all();
        $bankAccounts  = BankAccount::all();
        $sectors       = ['Agriculture', 'Business', 'Education', 'Health', 'Other']; // You can move this to config if reusable

        return view('loans.edit', [
            'loan'         => $loan,
            'customers'    => $customers,
            'groups'       => $groups,
            'products'     => $products,
            'bankAccounts' => $bankAccounts,
            'sectors'      => $sectors,
        ]);
    }

    public function update(Request $request, $encodedId)
    {
        // Decode loan ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loans.list')->withErrors(['Loan not found.']);
        }

        $loan = Loan::findOrFail($decoded[0]);

        // Validate incoming request
        $validated = $request->validate([
            'product_id'    => 'required|exists:loan_products,id',
            'period'        => 'required|integer|min:1',
            'interest'      => 'required|numeric|min:0',
            'amount'        => 'required|numeric|min:0',
            'date_applied'  => 'required|date|before_or_equal:today',
            'customer_id'   => 'required|exists:customers,id',
            'group_id'      => 'required|exists:groups,id',
            'account_id'    => 'required|exists:bank_accounts,id',
            'sector'        => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $loan) {
                $product = LoanProduct::findOrFail($validated['product_id']);

                // Validate product constraints
                $this->validateProductLimits($validated, $product);

                // Update base loan details
                $loan->fill([
                    'product_id'      => $validated['product_id'],
                    'period'          => $validated['period'],
                    'interest'        => $validated['interest'],
                    'date_applied'    => $validated['date_applied'],
                    'amount'          => $validated['amount'],
                    'group_id'        => $validated['group_id'],
                    'customer_id'     => $validated['customer_id'],
                    'bank_account_id' => $validated['account_id'],
                    'disbursed_on'    => $validated['date_applied'],
                    'sector'          => $validated['sector'],
                    'branch_id'       => auth()->user()->branch_id,
                ])->save();

                // Recalculate interest and repayment dates
                $interestAmount = $loan->calculateInterestAmount($validated['interest']);
                $repaymentDates = $loan->getRepaymentDates();

                $loan->update([
                    'interest_amount'       => $interestAmount,
                    'amount_total'          => $loan->amount + $interestAmount,
                    'first_repayment_date'  => $repaymentDates['first_repayment_date'],
                    'last_repayment_date'   => $repaymentDates['last_repayment_date'],
                ]);
            });

            return redirect()->route('loans.list')->with('success', 'Loan updated successfully.');
        } catch (\Throwable $th) {
            return back()->withErrors(['error' => 'Failed to update loan: ' . $th->getMessage()])->withInput();
        }
    }

    //////PRODUCT LIMITS ////////////////////////////////
    protected function validateProductLimits(array $data, LoanProduct $product)
    {
        if ($data['period'] < $product->minimum_period || $data['period'] > $product->maximum_period) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'period' => "Period must be between {$product->minimum_period} and {$product->maximum_period} months.",
            ]);
        }

        if ($data['interest'] < $product->minimum_interest_rate || $data['interest'] > $product->maximum_interest_rate) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'interest' => "Interest rate must be between {$product->minimum_interest_rate}% and {$product->maximum_interest_rate}%.",
            ]);
        }

        if ($data['amount'] < $product->minimum_principal || $data['amount'] > $product->maximum_principal) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => "Amount must be between {$product->minimum_principal} and {$product->maximum_principal}.",
            ]);
        }
    }


    public function destroy($encodedId)
    {
        try {
            // Decode the encoded ID
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.list')->withErrors(['Loan not found.']);
            }

            // Fetch the loan
            $loan = Loan::findOrFail($decoded[0]);

            // Optional: check if loan is deletable (e.g., not disbursed)
            if ($loan->status === 'disbursed') {
                return redirect()->route('loans.index')->withErrors(['You cannot delete a disbursed loan.']);
            }

            // Delete the loan
            $loan->delete();

            return redirect()->route('loans.list')->with('success', 'Loan deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('loans.list')->withErrors(['error' => 'Failed to delete loan: ' . $e->getMessage()]);
        }
    }

    public function show($encodedId)
{
    $decoded = Hashids::decode($encodedId);
    if (empty($decoded)) {
        return redirect()->route('loans.index')->withErrors(['Loan not found.']);
    }

    $loan = Loan::with([
        'customer.region', 'customer.district', 'customer.branch', 'customer.company', 'customer.user',
        'product',
        'bankAccount',
        'group'
        
    ])->findOrFail($decoded[0]);

    return view('loans.show', compact('loan'));
}

}

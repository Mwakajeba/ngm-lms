<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Filetype;
use App\Models\GlTransaction;
use App\Models\Group;
use App\Models\Loan;
use App\Models\LoanFile;
use App\Models\LoanProduct;
use App\Models\Payment;
use App\Models\PaymentItem;
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
            'product_id' => 'required|exists:loan_products,id',
            'period' => 'required|integer|min:1',
            'interest' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'date_applied' => 'required|date|before_or_equal:today',
            'customer_id' => 'required|exists:customers,id',
            'group_id' => 'required|exists:groups,id',
            'account_id' => 'required|exists:bank_accounts,id',
            'sector' => 'required|string',
        ]);

        $product = LoanProduct::with('principalReceivableAccount')->findOrFail($validated['product_id']);
        $this->validateProductLimits($validated, $product);

        $userId = auth()->id();
        $branchId = auth()->user()->branch_id;

        try {
            DB::transaction(function () use ($validated, $product, $userId, $branchId) {
                // Step 1: Create Loan
                $loan = Loan::create([
                    'product_id' => $validated['product_id'],
                    'period' => $validated['period'],
                    'interest' => $validated['interest'],
                    'amount' => $validated['amount'],
                    'customer_id' => $validated['customer_id'],
                    'group_id' => $validated['group_id'],
                    'bank_account_id' => $validated['account_id'],
                    'date_applied' => $validated['date_applied'],
                    'disbursed_on' => $validated['date_applied'],
                    'sector' => $validated['sector'],
                    'branch_id' => $branchId,
                    'status' => 'active',
                ]);

                // Step 2: Calculate interest and repayment dates
                $interestAmount = $loan->calculateInterestAmount($validated['interest']);
                $repaymentDates = $loan->getRepaymentDates();

                // Step 3: Update Loan with totals and schedule
                $loan->update([
                    'interest_amount' => $interestAmount,
                    'amount_total' => $loan->amount + $interestAmount,
                    'first_repayment_date' => $repaymentDates['first_repayment_date'],
                    'last_repayment_date' => $repaymentDates['last_repayment_date'],
                ]);

                // Step 4: Generate repayment schedule
                $loan->generateRepaymentSchedule($validated['interest']);

                // Step 5: Record Payment
                $bankAccount = BankAccount::findOrFail($validated['account_id']);
                $notes = "Being disbursement for loan of {$product->name}, paid to {$loan->customer->name}, TSHS.{$validated['amount']}";
                $principalReceivable = optional($product->principalReceivableAccount)->id;
                if (!$principalReceivable) {
                    throw new \Exception('Principal receivable account not set for this loan product.');
                }


                $payment = Payment::create([
                    'reference' => $loan->id,
                    'reference_type' => 'Loan Payment',
                    'reference_number' => null,
                    'date' => $validated['date_applied'],
                    'amount' => $validated['amount'],
                    'description' => $notes,
                    'user_id' => $userId,
                    'customer_id' => $validated['customer_id'],
                    'bank_account_id' => $validated['account_id'],
                    'branch_id' => $branchId,
                    'approved' => true,
                    'approved_by' => $userId,
                    'approved_at' => now(),
                ]);

                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'chart_account_id' => $principalReceivable,
                    'amount' => $validated['amount'],
                    'description' => $notes,
                ]);

                // Step 6: GL Transactions
                GlTransaction::insert([
                    [
                        'chart_account_id' => $bankAccount->chart_account_id,
                        'customer_id' => $loan->customer_id,
                        'amount' => $validated['amount'],
                        'nature' => 'credit',
                        'transaction_id' => $loan->id,
                        'transaction_type' => 'Loan Disbursement',
                        'date' => $validated['date_applied'],
                        'description' => $notes,
                        'branch_id' => $branchId,
                        'user_id' => $userId,
                    ],
                    [
                        'chart_account_id' => $principalReceivable,
                        'customer_id' => $loan->customer_id,
                        'amount' => $validated['amount'],
                        'nature' => 'debit',
                        'transaction_id' => $loan->id,
                        'transaction_type' => 'Loan Disbursement',
                        'date' => $validated['date_applied'],
                        'description' => $notes,
                        'branch_id' => $branchId,
                        'user_id' => $userId,
                    ]
                ]);
            });

            return redirect()->route('loans.list')->with('success', 'Loan application created successfully.');
        } catch (\Throwable $th) {
            return back()->withErrors([
                'error' => 'Failed to process loan application: ' . $th->getMessage()
            ])->withInput();
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
        $customers = Customer::all();
        $groups = Group::all();
        $products = LoanProduct::all();
        $bankAccounts = BankAccount::all();
        $sectors = ['Agriculture', 'Business', 'Education', 'Health', 'Other']; // You can move this to config if reusable

        return view('loans.edit', [
            'loan' => $loan,
            'customers' => $customers,
            'groups' => $groups,
            'products' => $products,
            'bankAccounts' => $bankAccounts,
            'sectors' => $sectors,
        ]);
    }

    public function update(Request $request, $encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loans.index')->withErrors(['Loan not found.']);
        }

        $loan = Loan::findOrFail($decoded[0]);

        $validated = $request->validate([
            'product_id' => 'required|exists:loan_products,id',
            'period' => 'required|integer|min:1',
            'interest' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'date_applied' => 'required|date|before_or_equal:today',
            'customer_id' => 'required|exists:customers,id',
            'group_id' => 'required|exists:groups,id',
            'account_id' => 'required|exists:bank_accounts,id',
            'sector' => 'required|string',
        ]);

        $product = LoanProduct::with('principalReceivableAccount')->findOrFail($validated['product_id']);
        $this->validateProductLimits($validated, $product);

        $userId = auth()->id();
        $branchId = auth()->user()->branch_id;

        try {
            DB::transaction(function () use ($loan, $validated, $product, $userId, $branchId) {

                // Step 1: Update base loan fields
                $loan->update([
                    'product_id' => $validated['product_id'],
                    'period' => $validated['period'],
                    'interest' => $validated['interest'],
                    'amount' => $validated['amount'],
                    'customer_id' => $validated['customer_id'],
                    'group_id' => $validated['group_id'],
                    'bank_account_id' => $validated['account_id'],
                    'date_applied' => $validated['date_applied'],
                    'disbursed_on' => $validated['date_applied'],
                    'sector' => $validated['sector'],
                    'branch_id' => $branchId,
                ]);

                // Step 2: Calculate interest and repayment dates
                $interestAmount = $loan->calculateInterestAmount($validated['interest']);
                $repaymentDates = $loan->getRepaymentDates();

                $loan->update([
                    'interest_amount' => $interestAmount,
                    'amount_total' => $loan->amount + $interestAmount,
                    'first_repayment_date' => $repaymentDates['first_repayment_date'],
                    'last_repayment_date' => $repaymentDates['last_repayment_date'],
                ]);

                // Step 3: Clear and regenerate loan schedule
                $loan->schedule()->delete();
                $loan->generateRepaymentSchedule($validated['interest']);

                // Step 4: Clear previous payment + GL records
                Payment::where('reference', $loan->id)->delete();
                PaymentItem::whereHas('payment', function ($query) use ($loan) {
                    $query->where('reference', $loan->id);
                })->delete();
                GlTransaction::where('transaction_id', $loan->id)
                    ->where('transaction_type', 'Loan Disbursement')
                    ->delete();

                // Step 5: Create payment record
                $bankAccount = BankAccount::findOrFail($validated['account_id']);
                $notes = "Being disbursement for loan of {$product->name}, paid to {$loan->customer->name}, TSHS.{$validated['amount']}";
                $principalReceivable = optional($product->principalReceivableAccount)->id;
                if (!$principalReceivable) {
                    throw new \Exception('Principal receivable account not set for this loan product.');
                }


                $payment = Payment::create([
                    'reference' => $loan->id,
                    'reference_type' => 'Loan Payment',
                    'reference_number' => null,
                    'date' => $validated['date_applied'],
                    'amount' => $validated['amount'],
                    'description' => $notes,
                    'user_id' => $userId,
                    'customer_id' => $validated['customer_id'],
                    'bank_account_id' => $validated['account_id'],
                    'branch_id' => $branchId,
                    'approved' => true,
                    'approved_by' => $userId,
                    'approved_at' => now(),
                ]);

                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'chart_account_id' => $principalReceivable,
                    'amount' => $validated['amount'],
                    'description' => $notes,
                ]);

                // Step 6: Create GL entries
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $loan->customer_id,
                    'amount' => $validated['amount'],
                    'nature' => 'credit',
                    'transaction_id' => $loan->id,
                    'transaction_type' => 'Loan Disbursement',
                    'date' => $validated['date_applied'],
                    'description' => $notes,
                    'branch_id' => $branchId,
                    'user_id' => $userId,
                ]);

                GlTransaction::create([
                    'chart_account_id' => $principalReceivable,
                    'customer_id' => $loan->customer_id,
                    'amount' => $validated['amount'],
                    'nature' => 'debit',
                    'transaction_id' => $loan->id,
                    'transaction_type' => 'Loan Disbursement',
                    'date' => $validated['date_applied'],
                    'description' => $notes,
                    'branch_id' => $branchId,
                    'user_id' => $userId,
                ]);
            });

            return redirect()->route('loans.index')->with('success', 'Loan updated successfully.');
        } catch (\Throwable $th) {
            return back()->withErrors([
                'error' => 'Failed to update loan: ' . $th->getMessage()
            ])->withInput();
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

            // Check if loan can be deleted - prevent deletion of active or authorized loans
            if (in_array($loan->status, ['active', 'authorized'])) {
                return redirect()->route('loans.list')->withErrors(['You cannot delete an active or authorized loan. Only pending, rejected, or other non-active loans can be deleted.']);
            }

            // Delete the loan
            $loan->delete();

            return redirect()->route('loans.list')->with('success', 'Loan deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('loans.list')->withErrors(['error' => 'Failed to delete loan: ' . $e->getMessage()]);
        }
    }
 //////////////////SHOW LOAN DETAIL/////////////////////
    public function show($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loans.index')->withErrors(['Loan not found.']);
        }

        $loan = Loan::with([
            'customer.region',
            'customer.district',
            'customer.branch',
            'customer.company',
            'customer.user',
            'product',
            'bankAccount',
            'group',
            'loanFiles',
            'schedule'

        ])->findOrFail($decoded[0]);
        $guarantorCustomers = Customer::where('category', 'guarantor')->get();


        return view('loans.show', compact('loan', 'guarantorCustomers'));
    }

    ////////////////////UPLOAD LOAN DOCUMENT/////////////////////

    public function loanDocument(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:2048',
        ]);

        // Step 1: Create or find file type
        $fileType = Filetype::firstOrCreate(['name' => $request->name]);

        // Step 2: Store file in public storage
        $filePath = $request->file('file')->store('loan_documents', 'public');

        // Step 3: Save record in loan_files
        LoanFile::create([
            'loan_id' => $request->loan_id,
            'file_type_id' => $fileType->id,
            'file_path' => $filePath,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }
    ///////////////////ADD GUARANTOR/////////////////
    public function addGuarantor(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'guarantor_id' => 'required|exists:customers,id',
            'relation' => 'nullable|string|max:100',
        ]);

        $loan->guarantors()->attach($validated['guarantor_id'], ['relation' => $validated['relation']]);

        return redirect()->back()->with('success', 'Guarantor added successfully.');
    }
    ///////REMOVE GUARANTOR/////
    public function removeGuarantor(Loan $loan, $guarantorId)
    {
        $loan->guarantors()->detach($guarantorId);

        return redirect()->back()->with('success', 'Guarantor removed successfully.');
    }

    // Loan Application Methods
    public function applicationIndex()
    {
        $branchId = auth()->user()->branch_id;
        $loanApplications = Loan::with('customer', 'product', 'branch')
            ->where('branch_id', $branchId)
            ->latest()
            ->paginate(10);

        return view('loans.application.index', compact('loanApplications'));
    }

    public function applicationCreate()
    {
        $customers = Customer::all();
        $groups = Group::all();
        $products = LoanProduct::all();
        $bankAccounts = BankAccount::all();
        $sectors = ['Agriculture', 'Business', 'Education', 'Health', 'Other'];

        return view('loans.application.create', compact('customers', 'groups', 'products', 'sectors', 'bankAccounts'));
    }

    public function applicationStore(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:loan_products,id',
            'period' => 'required|integer|min:1',
            'interest' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'date_applied' => 'required|date|before_or_equal:today',
            'customer_id' => 'required|exists:customers,id',
            'group_id' => 'nullable|exists:groups,id',
            'account_id' => 'required|exists:bank_accounts,id',
            'sector' => 'required|string',
        ]);

        $product = LoanProduct::with('principalReceivableAccount')->findOrFail($validated['product_id']);
        $this->validateProductLimits($validated, $product);

        $userId = auth()->id();
        $branchId = auth()->user()->branch_id;

        //check if customer is active
        // $customer = Customer::findOrFail($validated['customer_id']);
        // if (!$customer->is_active) {
        //     return back()->withErrors(['error' => 'Customer is not active.']);
        // }

        //check if loan product is active
        if (!$product->is_active) {
            return back()->withErrors(['error' => 'Loan product is not active.']);
        }

        //check the min and max amount for the loan product
        if ($validated['amount'] < $product->minimum_principal || $validated['amount'] > $product->maximum_principal) {
            return back()->withErrors(['error' => 'Loan amount must be between ' . $product->minimum_principal . ' and ' . $product->maximum_principal . '.']);
        }

        //check the min and max interest rate for the loan product
        if ($validated['interest'] < $product->minimum_interest_rate || $validated['interest'] > $product->maximum_interest_rate) {
            return back()->withErrors(['error' => 'Interest rate must be between ' . $product->minimum_interest_rate . ' and ' . $product->maximum_interest_rate . '.']);
        }

        //check the min and max period for the loan product
        if ($validated['period'] < $product->minimum_period || $validated['period'] > $product->maximum_period) {
            return back()->withErrors(['error' => 'Period must be between ' . $product->minimum_period . ' and ' . $product->maximum_period . '.']);
        }

        //check if member has enough collateral balance
        //1. check if this loan product require cash collateral
        if ($product->has_cash_collateral) {
            $customer = Customer::findOrFail($validated['customer_id']);
            $requiredCollateral = $product->cash_collateral_value_type === 'percentage'
                ? $customer->cash_collateral_balance * ($product->cash_collateral_value / 100)
                : $product->cash_collateral_value;

            if ($requiredCollateral < $validated['amount']) {
                return back()->withErrors(['error' => 'Member does not have enough collateral balance.']);
            }
        }

        //check if member already has a loan with the same product
        $existingLoan = Loan::where('customer_id', $validated['customer_id'])
            ->where('product_id', $validated['product_id'])
            ->where('status', '!=', 'rejected')
            ->first();
        if ($existingLoan) {
            return back()->withErrors(['error' => 'Member already has a loan with the same product.']);
        }


        try {
            DB::beginTransaction();
            $loan = Loan::create([
                'product_id' => $validated['product_id'],
                'period' => $validated['period'],
                'amount' => $validated['amount'],
                'customer_id' => $validated['customer_id'],
                'group_id' => $validated['group_id'],
                'bank_account_id' => $validated['account_id'],
                'date_applied' => $validated['date_applied'],
                'sector' => $validated['sector'],
                'branch_id' => $branchId,
                'status' => 'pending',
                'interest_amount' => 0, // Will be calculated below
                'amount_total' => 0, // Will be calculated below
                'first_repayment_date' => null,
                'last_repayment_date' => null,
                'disbursed_on' => null,
                'top_up_id' => null
            ]);

            // Calculate interest amount after loan is created
            $interestAmount = $loan->calculateInterestAmount($validated['interest']);
            $loan->update([
                'interest_amount' => $interestAmount,
                'amount_total' => $validated['amount'] + $interestAmount,
            ]);

            DB::commit();

            return redirect()->route('loans.application.index')->with('success', 'Loan application submitted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Failed to submit loan application: ' . $th->getMessage()
            ])->withInput();
        }
    }

    public function applicationShow($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
        }

        $loanApplication = Loan::with([
            'customer.region',
            'customer.district',
            'customer.branch',
            'customer.company',
            'customer.user',
            'product',
            'bankAccount',
            'group'
        ])->findOrFail($decoded[0]);

        return view('loans.application.show', compact('loanApplication'));
    }

    public function applicationEdit($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
        }

        $loanApplication = Loan::findOrFail($decoded[0]);

        // Check if application can be edited
        if ($loanApplication->status !== 'pending') {
            return redirect()->route('loans.application.index')->withErrors(['Only pending applications can be edited.']);
        }

        $customers = Customer::all();
        $groups = Group::all();
        $products = LoanProduct::all();
        $bankAccounts = BankAccount::all();
        $sectors = ['Agriculture', 'Business', 'Education', 'Health', 'Other'];

        return view('loans.application.edit', compact('loanApplication', 'customers', 'groups', 'products', 'sectors', 'bankAccounts'));
    }

    public function applicationUpdate(Request $request, $encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
        }

        $loanApplication = Loan::findOrFail($decoded[0]);

        // Check if application can be edited
        if ($loanApplication->status !== 'pending') {
            return redirect()->route('loans.application.index')->withErrors(['Only pending applications can be edited.']);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:loan_products,id',
            'period' => 'required|integer|min:1',
            'interest' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'date_applied' => 'required|date|before_or_equal:today',
            'customer_id' => 'required|exists:customers,id',
            'group_id' => 'nullable|exists:groups,id',
            'account_id' => 'required|exists:bank_accounts,id',
            'sector' => 'required|string',
        ]);

        $product = LoanProduct::with('principalReceivableAccount')->findOrFail($validated['product_id']);
        $this->validateProductLimits($validated, $product);

        try {
            $loanApplication->update([
                'product_id' => $validated['product_id'],
                'period' => $validated['period'],
                'interest' => $validated['interest'],
                'amount' => $validated['amount'],
                'customer_id' => $validated['customer_id'],
                'group_id' => $validated['group_id'],
                'bank_account_id' => $validated['account_id'],
                'date_applied' => $validated['date_applied'],
                'sector' => $validated['sector'],
            ]);

            return redirect()->route('loans.application.index')->with('success', 'Loan application updated successfully.');
        } catch (\Throwable $th) {
            return back()->withErrors([
                'error' => 'Failed to update loan application: ' . $th->getMessage()
            ])->withInput();
        }
    }

    public function applicationApprove($encodedId)
    {
        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
            }

            $loanApplication = Loan::findOrFail($decoded[0]);

            if ($loanApplication->status !== 'pending') {
                return redirect()->route('loans.application.index')->withErrors(['Only pending applications can be approved.']);
            }

            // Convert application to active loan
            $loanApplication->update([
                'status' => 'active',
                'disbursed_on' => $loanApplication->date_applied,
            ]);

            // Calculate interest and repayment dates
            $interestAmount = $loanApplication->calculateInterestAmount($loanApplication->interest);
            $repaymentDates = $loanApplication->getRepaymentDates();

            // Update loan with totals and schedule
            $loanApplication->update([
                'interest_amount' => $interestAmount,
                'amount_total' => $loanApplication->amount + $interestAmount,
                'first_repayment_date' => $repaymentDates['first_repayment_date'],
                'last_repayment_date' => $repaymentDates['last_repayment_date'],
            ]);

            // Generate repayment schedule
            $loanApplication->generateRepaymentSchedule($loanApplication->interest);

            // Record Payment and GL Transactions (similar to store method)
            $this->processLoanDisbursement($loanApplication);

            return redirect()->route('loans.application.index')->with('success', 'Loan application approved and disbursed successfully.');
        } catch (\Throwable $th) {
            return redirect()->route('loans.application.index')->withErrors(['Failed to approve application: ' . $th->getMessage()]);
        }
    }

    public function applicationReject($encodedId)
    {
        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
            }

            $loanApplication = Loan::findOrFail($decoded[0]);

            if ($loanApplication->status !== 'pending') {
                return redirect()->route('loans.application.index')->withErrors(['Only pending applications can be rejected.']);
            }

            $loanApplication->update([
                'status' => 'rejected',
            ]);

            return redirect()->route('loans.application.index')->with('success', 'Loan application rejected successfully.');
        } catch (\Throwable $th) {
            return redirect()->route('loans.application.index')->withErrors(['Failed to reject application: ' . $th->getMessage()]);
        }
    }

    private function processLoanDisbursement($loan)
    {
        $userId = auth()->id();
        $branchId = auth()->user()->branch_id;
        $product = $loan->product;
        $bankAccount = $loan->bankAccount;

        $notes = "Being disbursement for loan of {$product->name}, paid to {$loan->customer->name}, TSHS.{$loan->amount}";
        $principalReceivable = optional($product->principalReceivableAccount)->id;

        if (!$principalReceivable) {
            throw new \Exception('Principal receivable account not set for this loan product.');
        }

        // Create Payment record
        $payment = Payment::create([
            'reference' => $loan->id,
            'reference_type' => 'Loan Payment',
            'reference_number' => null,
            'date' => $loan->date_applied,
            'amount' => $loan->amount,
            'description' => $notes,
            'user_id' => $userId,
            'customer_id' => $loan->customer_id,
            'bank_account_id' => $loan->bank_account_id,
            'branch_id' => $branchId,
            'approved' => true,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        PaymentItem::create([
            'payment_id' => $payment->id,
            'chart_account_id' => $principalReceivable,
            'amount' => $loan->amount,
            'description' => $notes,
        ]);

        // Create GL Transactions
        GlTransaction::insert([
            [
                'chart_account_id' => $bankAccount->chart_account_id,
                'customer_id' => $loan->customer_id,
                'amount' => $loan->amount,
                'nature' => 'credit',
                'transaction_id' => $loan->id,
                'transaction_type' => 'Loan Disbursement',
                'date' => $loan->date_applied,
                'description' => $notes,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ],
            [
                'chart_account_id' => $principalReceivable,
                'customer_id' => $loan->customer_id,
                'amount' => $loan->amount,
                'nature' => 'debit',
                'transaction_id' => $loan->id,
                'transaction_type' => 'Loan Disbursement',
                'date' => $loan->date_applied,
                'description' => $notes,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]
        ]);
    }

    //function to delete loan application
    public function applicationDelete($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
        }

        try {
            DB::beginTransaction();
            $loanApplication = Loan::findOrFail($decoded[0]);

            // Check if loan application can be deleted - prevent deletion of active or authorized loans
            if (in_array($loanApplication->status, ['active', 'authorized'])) {
                DB::rollBack();
                return redirect()->route('loans.application.index')->withErrors(['You cannot delete an active or authorized loan. Only pending, rejected, or other non-active loans can be deleted.']);
            }

            $loanApplication->delete();
            DB::commit();
            return redirect()->route('loans.application.index')->with('success', 'Loan application deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('loans.application.index')->withErrors(['Failed to delete loan application: ' . $th->getMessage()]);
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CashCollateral;
use App\Models\Customer;
use App\Models\Filetype;
use App\Models\GlTransaction;
use App\Models\Group;
use App\Models\Loan;
use App\Models\LoanApproval;
use App\Models\LoanFile;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Role;
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

    public function loansByStatus($status)
    {
        $branchId = auth()->user()->branch_id;

        // Validate status
        $validStatuses = ['applied', 'checked', 'approved', 'authorized', 'active', 'defaulted', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            return redirect()->route('loans.index')->withErrors(['Invalid loan status.']);
        }

        $loans = Loan::with('customer', 'product', 'branch')
            ->where('branch_id', $branchId)
            ->where('status', $status)
            ->latest()->get();

        // Get status display name
        $statusNames = [
            'applied' => 'Applied Loans',
            'checked' => 'Checked Applications',
            'approved' => 'Approved Applications',
            'authorized' => 'Authorized Applications',
            'active' => 'Active Loans',
            'defaulted' => 'Defaulted Loans',
            'rejected' => 'Rejected Applications'
        ];

        $pageTitle = $statusNames[$status] ?? ucfirst($status) . ' Loans';

        return view('loans.list', compact('loans', 'pageTitle', 'status'));
    }

    public function create()
    {
        $customers = Customer::with('groups')->where('category', 'Borrower')->get();
        info($customers);
        $products = LoanProduct::all();
        $bankAccounts = BankAccount::all();
        $sectors = ['Agriculture', 'Business', 'Education', 'Health', 'Other']; // Example sectors
        return view('loans.create', compact('customers', 'products', 'sectors', 'bankAccounts'));
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
        // 🔐 Check collateral OUTSIDE transaction
        if ($product->requiresCollateral()) {
            $requiredCollateral = $product->calculateRequiredCollateral($validated['amount']);
            $availableCollateral = CashCollateral::getCashCollateralBalance($validated['customer_id']);

            if ($availableCollateral < $requiredCollateral) {
                return redirect()->back()->withErrors([
                    'collateral' => 'The customer does not have enough cash collateral to qualify for this loan. 
                Required: TZS ' . number_format($requiredCollateral, 2) .
                        ', Available: TZS ' . number_format($availableCollateral, 2) . '.',
                ])->withInput();
            }
        }

        $userId = auth()->id();
        $branchId = auth()->user()->branch_id;

        try {
            DB::transaction(function () use ($validated, $product, $userId, $branchId) {
                // Step 1: Create Loan with initial status



                // Step 1: Create Loan
                $loan = Loan::create([
                    'product_id' => $validated['product_id'],
                    'period' => $validated['period'],
                    'interest' => $validated['interest'],
                    'amount' => $validated['amount'],
                    'customer_id' => $validated['customer_id'],
                    'interest' => $validated['interest'],
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
                // Step 7: Post Penalty Amount to GL (if exists)
                $penalty = $product->penalty; // assuming single penalty relation

                $penaltyAmount = LoanSchedule::where('loan_id', $loan->id)->sum('penalty_amount');

                if ($penaltyAmount > 0) {
                    $receivableId = $penalty->penalty_receivables_account_id;  // from penalties table
                    $incomeId = $penalty->penalty_income_account_id;          // from penalties table

                    if (!$receivableId || !$incomeId) {
                        throw new \Exception('Penalty chart accounts not configured.');
                    }

                    GlTransaction::insert([
                        [
                            'chart_account_id' => $receivableId,
                            'customer_id' => $loan->customer_id,
                            'amount' => $penaltyAmount,
                            'nature' => 'debit',
                            'transaction_id' => $loan->id,
                            'transaction_type' => 'Loan Penalty',
                            'date' => $validated['date_applied'],
                            'description' => $notes,
                            'branch_id' => $branchId,
                            'user_id' => $userId,
                        ],
                        [
                            'chart_account_id' => $incomeId,
                            'customer_id' => $loan->customer_id,
                            'amount' => $penaltyAmount,
                            'nature' => 'credit',
                            'transaction_id' => $loan->id,
                            'transaction_type' => 'Loan Penalty',
                            'date' => $validated['date_applied'],
                            'description' => $notes,
                            'branch_id' => $branchId,
                            'user_id' => $userId,
                        ]
                    ]);
                }
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
        // 🔐 Check collateral OUTSIDE transaction
        if ($product->requiresCollateral()) {
            $requiredCollateral = $product->calculateRequiredCollateral($validated['amount']);
            $availableCollateral = CashCollateral::getCashCollateralBalance($validated['customer_id']);

            if ($availableCollateral < $requiredCollateral) {
                return redirect()->back()->withErrors([
                    'collateral' => 'The customer does not have enough cash collateral to qualify for this loan. 
                Required: TZS ' . number_format($requiredCollateral, 2) .
                        ', Available: TZS ' . number_format($availableCollateral, 2) . '.',
                ])->withInput();
            }
        }

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
                    'interest' => $validated['interest'],
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
            'schedule',
            'approvals.user',
            'approvals' => function ($query) {
                $query->orderBy('approval_level', 'asc');
            },
            'guarantors' // add this if not eager loaded already
        ])->findOrFail($decoded[0]);

        // Get IDs of guarantors already attached to this loan
        $guarantorIdsAlreadyAdded = $loan->guarantors->pluck('id')->toArray();

        // Fetch guarantors excluding already assigned ones
        $guarantorCustomers = Customer::where('category', 'guarantor')
            ->whereNotIn('id', $guarantorIdsAlreadyAdded)
            ->get();

        $filetypes = Filetype::all();

        return view('loans.show', compact('loan', 'guarantorCustomers', 'filetypes'));
    }


    ////////////////////UPLOAD LOAN DOCUMENT/////////////////////

    public function loanDocument(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'file_type_id' => 'required|exists:filetypes,id',
            'file' => 'required|file|max:2048',
        ]);

        // Step 2: Store file in public storage
        $filePath = $request->file('file')->store('loan_documents', 'public');

        // Step 3: Save record in loan_files
        LoanFile::create([
            'loan_id' => $request->loan_id,
            'file_type_id' => $request->file_type_id,
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
        $loanApplications = Loan::with('customer', 'product', 'branch', 'approvals')
            ->where('branch_id', $branchId)
            ->where('status', 'applied')
            ->latest()
            ->paginate(10);

        return view('loans.application.index', compact('loanApplications'));
    }

    public function applicationCreate()
    {
        $customers = Customer::where('category','borrower')->get();
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

            // Determine initial status based on approval levels
            $initialStatus = $product->has_approval_levels ? Loan::STATUS_APPLIED : Loan::STATUS_ACTIVE;

            $loan = Loan::create([
                'product_id' => $validated['product_id'],
                'period' => $validated['period'],
                'interest' => $validated['interest'],
                'amount' => $validated['amount'],
                'customer_id' => $validated['customer_id'],
                'group_id' => $validated['group_id'],
                'bank_account_id' => '',
                'date_applied' => $validated['date_applied'],
                'sector' => $validated['sector'],
                'branch_id' => $branchId,
                'status' => $initialStatus,
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

            // If no approval levels required, process disbursement immediately
            if (!$product->has_approval_levels) {
                $this->processLoanDisbursement($loan);
            }

            DB::commit();

            $message = $product->has_approval_levels
                ? 'Loan application submitted successfully and awaiting approval.'
                : 'Loan application created and disbursed successfully.';

            return redirect()->route('loans.application.index')->with('success', $message);
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
            'schedule',
            'approvals.user',
            'approvals' => function ($query) {
                $query->orderBy('approval_level', 'asc');
            },
            'guarantors' // add this if not eager loaded already
        ])->findOrFail($decoded[0]);

        // Get IDs of guarantors already attached to this loan
        $guarantorIdsAlreadyAdded = $loan->guarantors->pluck('id')->toArray();

        // Fetch guarantors excluding already assigned ones
        $guarantorCustomers = Customer::where('category', 'guarantor')
            ->whereNotIn('id', $guarantorIdsAlreadyAdded)
            ->get();

        $filetypes = Filetype::all();

        return view('loans.show', compact('loan', 'guarantorCustomers', 'filetypes'));
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

    /**
     * Dynamic approval method - handles all approval levels
     */
    public function approveLoan($encodedId, Request $request)
    {
        \Log::info('approveLoan method called', [
            'encodedId' => $encodedId,
            'request_method' => $request->method(),
            'request_url' => $request->url(),
            'request_data' => $request->all()
        ]);

        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                \Log::error('Failed to decode ID', ['encodedId' => $encodedId]);
                return redirect()->back()->withErrors(['Loan application not found.']);
            }

            $loan = Loan::findOrFail($decoded[0]);
            $user = auth()->user();

            // Debug information
            \Log::info('Approval attempt', [
                'loan_id' => $loan->id,
                'loan_status' => $loan->status,
                'user_id' => $user->id,
                'user_roles' => $user->roles->pluck('id')->toArray(),
                'product_approval_levels' => $loan->product->approval_levels ?? 'none',
                'approval_roles' => $loan->getApprovalRoles(),
                'next_level' => $loan->getNextApprovalLevel(),
                'next_role' => $loan->getNextApprovalRole(),
                'next_action' => $loan->getNextApprovalAction(),
                'can_approve' => $loan->canBeApprovedByUser($user),
                // 'has_approved' => $loan->hasUserApproved($user)
            ]);

            // Validate user has permission to approve
            if (!$loan->canBeApprovedByUser($user)) {
                \Log::warning('User does not have permission to approve', [
                    'user_id' => $user->id,
                    'required_role' => $loan->getNextApprovalRole(),
                    'user_roles' => $user->roles->pluck('id')->toArray()
                ]);
                return redirect()->back()->withErrors(['You do not have permission to approve this loan. Required role: ' . $loan->getApprovalLevelName($loan->getNextApprovalLevel())]);
            }

            // Check if user has already approved this loan
            // if ($loan->hasUserApproved($user)) {
            //     \Log::warning('User has already approved this loan', [
            //         'user_id' => $user->id,
            //         'loan_id' => $loan->id
            //     ]);
            //     return redirect()->back()->withErrors(['You have already approved this loan.']);
            // }

            $validated = $request->validate([
                'comments' => 'nullable|string|max:1000',
            ]);

            $nextAction = $loan->getNextApprovalAction();
            $nextLevel = $loan->getNextApprovalLevel();
            $roleName = $loan->getApprovalLevelName($nextLevel);

            if (!$nextAction || !$nextLevel) {
                \Log::error('Unable to determine next approval action', [
                    'nextAction' => $nextAction,
                    'nextLevel' => $nextLevel
                ]);
                return redirect()->back()->withErrors(['Unable to determine next approval action.']);
            }

            \Log::info('About to start database transaction', [
                'nextAction' => $nextAction,
                'nextLevel' => $nextLevel,
                'roleName' => $roleName
            ]);

            DB::transaction(function () use ($loan, $user, $validated, $nextAction, $nextLevel, $roleName) {
                \Log::info('Creating approval record', [
                    'loan_id' => $loan->id,
                    'user_id' => $user->id,
                    'role_name' => $roleName,
                    'approval_level' => $nextLevel,
                    'action' => $nextAction
                ]);

                // Update loan status based on action
                $oldStatus = $loan->status;
                switch ($nextAction) {
                    case 'check':
                        $loan->update(['status' => Loan::STATUS_CHECKED]);
                        $actionForRecord = 'checked';
                        break;
                    case 'approve':
                        $loan->update(['status' => Loan::STATUS_APPROVED]);
                        $actionForRecord = 'approved';
                        break;
                    case 'authorize':
                        $loan->update(['status' => Loan::STATUS_AUTHORIZED]);
                        $actionForRecord = 'authorized';
                        break;
                    case 'disburse':
                        // Process disbursement
                        $loan->update([
                            'status' => Loan::STATUS_ACTIVE,
                            'disbursed_on' => now(),
                        ]);

                        // Calculate interest and repayment dates
                        $interestAmount = $loan->calculateInterestAmount($loan->interest);
                        $repaymentDates = $loan->getRepaymentDates();

                        // Update loan with totals and schedule
                        $loan->update([
                            'interest_amount' => $interestAmount,
                            'amount_total' => $loan->amount + $interestAmount,
                            'first_repayment_date' => $repaymentDates['first_repayment_date'],
                            'last_repayment_date' => $repaymentDates['last_repayment_date'],
                        ]);

                        // Generate repayment schedule
                        $loan->generateRepaymentSchedule($loan->interest);

                        // Process disbursement
                        $this->processLoanDisbursement($loan);
                        $actionForRecord = 'active';
                        break;
                }

                // Create approval record with the correct action value
                $approval = LoanApproval::create([
                    'loan_id' => $loan->id,
                    'user_id' => $user->id,
                    'role_name' => $roleName,
                    'approval_level' => $nextLevel,
                    'action' => $actionForRecord,
                    'comments' => $validated['comments'] ?? null,
                    'approved_at' => now(),
                ]);

                \Log::info('Approval record created', ['approval_id' => $approval->id]);

                \Log::info('Loan status updated', [
                    'old_status' => $oldStatus,
                    'new_status' => $loan->fresh()->status,
                    'action' => $nextAction
                ]);
            });

            $actionMessages = [
                'check' => 'checked',
                'approve' => 'approved',
                'authorize' => 'authorized',
                'disburse' => 'disbursed'
            ];

            $message = $actionMessages[$nextAction] ?? 'processed';

            // Redirect based on the new status
            $newStatus = $loan->fresh()->status;
            \Log::info('Approval completed successfully', [
                'new_status' => $newStatus,
                'message' => $message
            ]);

            switch ($newStatus) {
                case 'checked':
                    return redirect()->route('loans.by-status', 'checked')->with('success', "Loan application {$message} successfully.");
                case 'approved':
                    return redirect()->route('loans.by-status', 'approved')->with('success', "Loan application {$message} successfully.");
                case 'authorized':
                    return redirect()->route('loans.by-status', 'authorized')->with('success', "Loan application {$message} successfully.");
                case 'active':
                    return redirect()->route('loans.by-status', 'active')->with('success', "Loan application {$message} successfully.");
                default:
                    return redirect()->route('loans.application.index')->with('success', "Loan application {$message} successfully.");
            }
        } catch (\Throwable $th) {
            \Log::error('Approval failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['Failed to process loan: ' . $th->getMessage()]);
        }
    }

    /**
     * Reject loan application
     */
    public function rejectLoan($encodedId, Request $request)
    {
        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.application.index')->withErrors(['Loan application not found.']);
            }

            $loan = Loan::findOrFail($decoded[0]);
            $user = auth()->user();

            // Validate loan can be rejected
            if (!$loan->canBeRejected()) {
                return redirect()->back()->withErrors(['This loan cannot be rejected at its current status.']);
            }

            // Validate user has permission to reject
            if (!$loan->canBeApprovedByUser($user)) {
                return redirect()->back()->withErrors(['You do not have permission to reject this loan.']);
            }

            // Check if user has already approved this loan
            // if ($loan->hasUserApproved($user)) {
            //     return redirect()->back()->withErrors(['You have already approved this loan.']);
            // }

            $validated = $request->validate([
                'comments' => 'required|string|max:1000',
            ]);

            $nextLevel = $loan->getNextApprovalLevel();
            $roleName = $loan->getApprovalLevelName($nextLevel);

            DB::transaction(function () use ($loan, $user, $validated, $nextLevel, $roleName) {
                // Create rejection record
                LoanApproval::create([
                    'loan_id' => $loan->id,
                    'user_id' => $user->id,
                    'role_name' => $roleName,
                    'approval_level' => $nextLevel,
                    'action' => 'rejected',
                    'comments' => $validated['comments'],
                    'approved_at' => now(),
                ]);

                // Update loan status
                $loan->update(['status' => Loan::STATUS_REJECTED]);
            });

            return redirect()->route('loans.by-status', 'rejected')->with('success', 'Loan application rejected successfully.');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors(['Failed to reject loan: ' . $th->getMessage()]);
        }
    }

    /**
     * Legacy methods for backward compatibility
     */
    public function checkLoan($encodedId, Request $request)
    {
        return $this->approveLoan($encodedId, $request);
    }

    public function authorizeLoan($encodedId, Request $request)
    {
        return $this->approveLoan($encodedId, $request);
    }

    public function disburseLoan($encodedId, Request $request)
    {
        return $this->approveLoan($encodedId, $request);
    }

    public function applicationApprove($encodedId)
    {
        return $this->approveLoan($encodedId, request());
    }

    public function applicationReject($encodedId)
    {
        return $this->rejectLoan($encodedId, request());
    }

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

    /**
     * Mark loan as defaulted
     */
    public function defaultLoan($encodedId, Request $request)
    {
        try {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return redirect()->route('loans.list')->withErrors(['Loan not found.']);
            }

            $loan = Loan::findOrFail($decoded[0]);
            $user = auth()->user();

            // Validate loan can be defaulted
            if ($loan->status !== Loan::STATUS_ACTIVE) {
                return redirect()->route('loans.list')->withErrors(['Only active loans can be marked as defaulted.']);
            }

            $validated = $request->validate([
                'comments' => 'required|string|max:1000',
            ]);

            DB::transaction(function () use ($loan, $user, $validated) {
                // Create default record
                LoanApproval::create([
                    'loan_id' => $loan->id,
                    'user_id' => $user->id,
                    'role_name' => 'System',
                    'approval_level' => 0,
                    'action' => 'defaulted',
                    'comments' => $validated['comments'],
                    'approved_at' => now(),
                ]);

                $loan->update([
                    'status' => Loan::STATUS_DEFAULTED,
                ]);
            });

            return redirect()->route('loans.list')->with('success', 'Loan marked as defaulted successfully.');
        } catch (\Throwable $th) {
            return redirect()->route('loans.list')->withErrors(['Failed to mark loan as defaulted: ' . $th->getMessage()]);
        }
    }
}

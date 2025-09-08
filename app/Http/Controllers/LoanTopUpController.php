<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CashCollateral;
use App\Models\Customer;
use App\Models\Filetype;
use App\Models\GlTransaction;
use App\Models\Group;
use App\Models\Loan;
use App\Models\LoanTopup;
use App\Models\LoanApproval;
use App\Models\LoanFile;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
use App\Models\ChartAccount;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;


class LoanTopUpController extends Controller
{
    /**
     * Show the loan top-up form.
     */
    public function show($encodedId)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('loans.list')->withErrors(['Loan not found.']);
        }
        $loan = Loan::find($decoded[0]);
        if (!$loan) {
            return redirect()->route('loans.list')->withErrors(['Loan not found.']);
        }
        $loan->encodedId = $encodedId;
        return view('loans.top_up', compact('loan'));
    }

    /**
     * Handle the loan top-up submission.
     */
    public function store(Request $request, $encodedId)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($encodedId);
        if ($request->topup_type === 'restructure') {
            // Restructure: close old loan, create new loan with combined balance
            $oldLoan->status = 'restructured';
            $oldLoan->save();

            $newLoan = Loan::create([
                'customer_id'      => $oldLoan->customer_id,
                'group_id'         => $oldLoan->group_id,
                'product_id'       => $oldLoan->product_id,
                'amount'           => $oldBalance + $request->topup_amount,
                'interest'         => $oldLoan->interest,
                'period'           => $oldLoan->period,
                'amount_total'     => $oldBalance + $request->topup_amount, // or recalculate with interest
                'bank_account_id'  => $oldLoan->bank_account_id,
                'date_applied'     => now(),
                'disbursed_on'     => now(),
                'status'           => 'active',
                'sector'           => $oldLoan->sector,
                'interest_cycle'   => $oldLoan->interest_cycle,
                'loan_officer_id'  => $oldLoan->loan_officer_id,
                'branch_id'        => $oldLoan->branch_id,
                'top_up_id'        => $oldLoan->id,
            ]);

            $interestAmount = $newLoan->calculateInterestAmount($newLoan->interest);
            $repaymentDates = $newLoan->getRepaymentDates();
            $newLoan->update([
                'interest_amount' => $interestAmount,
                'amount_total' => $newLoan->amount + $interestAmount,
                'first_repayment_date' => $repaymentDates['first_repayment_date'],
                'last_repayment_date' => $repaymentDates['last_repayment_date'],
            ]);
            $newLoan->generateRepaymentSchedule($newLoan->interest);

            LoanTopup::create([
                'old_loan_id'   => $oldLoan->id,
                'new_loan_id'   => $newLoan->id,
                'old_balance'   => $oldBalance,
                'topup_amount'  => $request->topup_amount,
                'topup_type'    => 'restructure',
            ]);
            // Only new loan is active
        } elseif ($request->topup_type === 'additional') {
            // Top-up: create new loan for additional amount, keep old loan active
            $newLoan = Loan::create([
                'customer_id'      => $loan->customer_id,
                'group_id'         => $loan->group_id,
                'product_id'       => $loan->product_id,
                'amount'           => $request->topup_amount,
                'interest'         => $loan->interest,
                'period'           => $loan->period,
                'amount_total'     => $request->topup_amount, // or recalculate with interest
                'bank_account_id'  => $loan->bank_account_id,
                'date_applied'     => now(),
                'disbursed_on'     => now(),
                'status'           => 'active',
                'sector'           => $loan->sector,
                'interest_cycle'   => $loan->interest_cycle,
                'loan_officer_id'  => $loan->loan_officer_id,
                'branch_id'        => $loan->branch_id,
                'top_up_id'        => $loan->id,
            ]);

            $interestAmount = $newLoan->calculateInterestAmount($newLoan->interest);
            $repaymentDates = $newLoan->getRepaymentDates();
            $newLoan->update([
                'interest_amount' => $interestAmount,
                'amount_total' => $newLoan->amount + $interestAmount,
                'first_repayment_date' => $repaymentDates['first_repayment_date'],
                'last_repayment_date' => $repaymentDates['last_repayment_date'],
            ]);
            $newLoan->generateRepaymentSchedule($newLoan->interest);

            LoanTopup::create([
                'old_loan_id'   => $loan->id,
                'new_loan_id'   => $newLoan->id,
                'old_balance'   => $loan->balance,
                'topup_amount'  => $request->topup_amount,
                'topup_type'    => 'additional',
            ]);
            // Both loans remain active
        }
        // Optionally, create a LoanTopUp model or log the transaction

        // For now, just redirect with success
        return redirect()->route('loans.show', $encodedId)
            ->with('success', 'Loan top-up submitted successfully.');
    }
}

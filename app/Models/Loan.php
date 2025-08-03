<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Optional if you want soft deletes

class Loan extends Model
{
    // Uncomment if using soft deletes
    // use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'group_id',
        'product_id',
        'amount',
        'interest_amount',
        'period',
        'amount_total',
        'bank_account_id',
        'date_applied',
        'disbursed_on',
        'status',
        'sector',
        'top_up_id',
        'first_repayment_date',
        'last_repayment_date',
        'branch_id',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }


    public function product()
    {
        return $this->belongsTo(LoanProduct::class, 'product_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function topUpLoan()
    {
        return $this->belongsTo(Loan::class, 'top_up_id');
    }

    public function topUpChildren()
    {
        return $this->hasMany(Loan::class, 'top_up_id');
    }

    public function schedule()
    {
        return $this->hasMany(LoanSchedule::class, 'loan_id');
    }


    public function loanFiles()
    {
        return $this->hasMany(LoanFile::class, 'loan_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }


    public function calculateInterestAmount(float $rate = null, bool $returnSchedule = false): float|array
    {
        $product = $this->product;
        if (!$product) return $returnSchedule ? [] : 0;

        $principal = $this->amount;
        $rate = $rate ?? $this->interest ?? $product->interest ?? 0;

        $period = $this->period;
        $method = $product->interest_method ?? 'flat_rate';

        $ratePerPeriod = $rate / 100;

        $schedule = [];
        $interestAmount = 0;

        switch ($method) {
            case 'flat_rate':
                $interestAmount = $principal * $ratePerPeriod * $period;
                if ($returnSchedule) {
                    $monthlyPrincipal = $principal / $period;
                    $monthlyInterest = $interestAmount / $period;
                    for ($i = 1; $i <= $period; $i++) {
                        $schedule[] = [
                            'principal' => round($monthlyPrincipal, 2),
                            'interest' => round($monthlyInterest, 2),
                            'total' => round($monthlyPrincipal + $monthlyInterest, 2),
                        ];
                    }
                }
                break;

            case 'reducing_balance_with_equal_installment':
                $r = $ratePerPeriod;
                $n = $period;
                $P = $principal;

                $emi = ($P * $r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
                $totalPayable = $emi * $n;
                $interestAmount = $totalPayable - $P;

                if ($returnSchedule) {
                    $balance = $P;
                    for ($i = 1; $i <= $n; $i++) {
                        $interest = $balance * $r;
                        $principalPart = $emi - $interest;
                        $balance -= $principalPart;

                        $schedule[] = [
                            'principal' => round($principalPart, 2),
                            'interest' => round($interest, 2),
                            'total' => round($emi, 2),
                        ];
                    }
                }
                break;

            case 'reducing_balance_with_equal_principal':
                $monthlyPrincipal = $principal / $period;
                $balance = $principal;
                $totalInterest = 0;

                if ($returnSchedule) {
                    for ($i = 1; $i <= $period; $i++) {
                        $interest = $balance * $ratePerPeriod;
                        $totalInterest += $interest;
                        $schedule[] = [
                            'principal' => round($monthlyPrincipal, 2),
                            'interest' => round($interest, 2),
                            'total' => round($monthlyPrincipal + $interest, 2),
                        ];
                        $balance -= $monthlyPrincipal;
                    }
                } else {
                    for ($i = 1; $i <= $period; $i++) {
                        $interest = $balance * $ratePerPeriod;
                        $totalInterest += $interest;
                        $balance -= $monthlyPrincipal;
                    }
                }

                $interestAmount = $totalInterest;
                break;

            default:
                $interestAmount = $principal * $ratePerPeriod * $period;
                break;
        }

        return $returnSchedule ? $schedule : round($interestAmount, 2);
    }



    public function getRepaymentDates()
    {
        $cycle = $this->product->interest_cycle ?? 'monthly'; // e.g., monthly, weekly
        $period = $this->period;
        $disbursedOn = Carbon::parse($this->disbursed_on);

        // 1. Get first repayment date
        switch ($cycle) {
            case 'Daily':
                $first = $disbursedOn->copy()->addDay();
                $last  = $first->copy()->addDays($period - 1);
                break;

            case 'Weekly':
                $first = $disbursedOn->copy()->addWeek();
                $last  = $first->copy()->addWeeks($period - 1);
                break;

            case 'Monthly':
                $first = $disbursedOn->copy()->addMonth();
                $last  = $first->copy()->addMonths($period - 1);
                break;

            case 'Quarterly':
                $first = $disbursedOn->copy()->addMonths(3);
                $last  = $first->copy()->addMonths(3 * ($period - 1));
                break;

            case 'Semi Annually':
                $first = $disbursedOn->copy()->addMonths(6);
                $last  = $first->copy()->addMonths(6 * ($period - 1));
                break;

            case 'Annually':
                $first = $disbursedOn->copy()->addYear();
                $last  = $first->copy()->addYears($period - 1);
                break;

            default:
                // fallback: monthly
                $first = $disbursedOn->copy()->addMonth();
                $last  = $first->copy()->addMonths($period - 1);
        }

        return [
            'first_repayment_date' => $first->toDateString(),
            'last_repayment_date'  => $last->toDateString(),
        ];
    }

    public function generateRepaymentSchedule(float $rate)
    {
        $product = $this->product;
        if (!$product) return;

        $principal = $this->amount;
        $interestAmount = $this->interest_amount;
        $period = $this->period;
        $method = strtolower($product->interest_method ?? 'flat_rate');
        $startDate = Carbon::parse($this->first_repayment_date);
        $gracePeriod = $product->grace_period ?? 0;

        switch ($method) {
            case 'flat_rate':
                $principalInstallment = round($principal / $period, 2);
                $interestInstallment  = round($interestAmount / $period, 2);

                for ($i = 0; $i < $period; $i++) {
                    $dueDate = $startDate->copy()->addMonths($i);
                    LoanSchedule::create([
                        'loan_id'         => $this->id,
                        'customer_id'     => $this->customer_id,
                        'due_date'        => $dueDate,
                        'end_date'        => $dueDate->copy()->addDays(5),
                        'end_grace_date'  => $dueDate->copy()->addDays($gracePeriod),
                        'principal'       => $principalInstallment,
                        'interest'        => $interestInstallment,
                    ]);
                }
                break;

            case 'reducing_balance_with_equal_installment':
            case 'reducing_balance_with_equal_principal':
                // ✅ FIXED: return schedule as array
                $schedule = $this->calculateInterestAmount($rate, true);

                foreach ($schedule as $i => $row) {
                    $dueDate = $startDate->copy()->addMonths($i);
                    LoanSchedule::create([
                        'loan_id'         => $this->id,
                        'customer_id'     => $this->customer_id,
                        'due_date'        => $dueDate,
                        'end_date'        => $dueDate->copy()->addDays(5),
                        'end_grace_date'  => $dueDate->copy()->addDays($gracePeriod),
                        'principal'       => $row['principal'],
                        'interest'        => $row['interest'],
                    ]);
                }
                break;
        }
    }
}

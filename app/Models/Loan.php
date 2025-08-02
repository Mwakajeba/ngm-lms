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

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function calculateInterestAmount(float $rate = null): float
    {
        $product = $this->product;
        if (!$product) return 0;

        $principal = $this->amount;
        $rate = $rate ?? $this->interest ?? $product->interest ?? 0;

        $period = $this->period;
        $method = $product->interest_method ?? 'flat_rate';




        $ratePerPeriod = $rate / 100;

        switch ($method) {
            case 'flat_rate':
                $interestAmount = $principal * $ratePerPeriod * $period;
                break;

            case 'reducing_balance_with_equal_installment':
                $r = $ratePerPeriod;
                $n = $period;
                $P = $principal;

                $emi = ($P * $r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
                $totalPayable = $emi * $n;
                $interestAmount = $totalPayable - $P;
                break;

            case 'reducing_balance_with_equal_principal':
                $monthlyPrincipal = $principal / $period;
                $balance = $principal;
                $totalInterest = 0;

                for ($i = 1; $i <= $period; $i++) {
                    $interest = $balance * $ratePerPeriod;
                    $totalInterest += $interest;
                    $balance -= $monthlyPrincipal;
                }

                $interestAmount = $totalInterest;
                break;

            default:
                $interestAmount = $principal * $ratePerPeriod * $period;
                break;
        }

        return round($interestAmount, 2);
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
}

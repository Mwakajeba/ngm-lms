<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSchedule extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'loan_schedules';
    protected $fillable = ['loan_id', 'interest', 'principal', 'end_date', 'end_grace_date', 'end_pernalty_date', 'customer_id', 'due_date', 'fee_amount', 'penalty_amount'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function repayments()
    {
        return $this->hasMany(Repayment::class, 'loan_schedule_id');
    }

    /**
     * Get the total amount paid for this schedule
     */
    public function getPaidAmountAttribute()
    {
        return $this->repayments->sum(function ($repayment) {
            return $repayment->principal + $repayment->interest + $repayment->fee_amount + $repayment->penalt_amount;
        });
    }

    /**
     * Get the remaining amount to be paid for this schedule
     */
    public function getRemainingAmountAttribute()
    {
        $totalDue = $this->principal + $this->interest + $this->fee_amount + $this->penalty_amount;
        return max(0, $totalDue - $this->paid_amount);
    }

    /**
     * Check if this schedule is fully paid
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->remaining_amount <= 0;
    }

    /**
     * Get the total amount due for this schedule
     */
    public function getTotalDueAttribute()
    {
        return $this->principal + $this->interest + $this->fee_amount + $this->penalty_amount;
    }

    /**
     * Get the percentage of payment completed
     */
    public function getPaymentPercentageAttribute()
    {
        if ($this->total_due <= 0) {
            return 100;
        }
        return min(100, round(($this->paid_amount / $this->total_due) * 100, 2));
    }

    /**
     * Check if the loan associated with this schedule is active
     */
    public function getIsLoanActiveAttribute()
    {
        return $this->loan && $this->loan->status === Loan::STATUS_ACTIVE;
    }

    /**
     * Check if the loan associated with this schedule is active (method version)
     */
    public function isLoanActive()
    {
        return $this->loan && $this->loan->status === Loan::STATUS_ACTIVE;
    }
    public function fullPrincipalPaid()
    {
        $totalPrincipalPaid = $this->repayments->sum('principal');
        return $totalPrincipalPaid >= $this->principal;
    }
}

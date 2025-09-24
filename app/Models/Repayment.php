<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repayment extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'repayments';
    protected $fillable = [
        'customer_id',
        'loan_id',
        'loan_schedule_id',
        'bank_account_id',
        'principal',
        'interest',
        'penalt_amount',
        'fee_amount',
        'due_date',
        'cash_deposit',
        'payment_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
        'principal' => 'float',
        'interest' => 'float',
        'penalt_amount' => 'float',
        'fee_amount' => 'float',
        'cash_deposit' => 'float',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function schedule()
    {
        return $this->belongsTo(LoanSchedule::class, 'loan_schedule_id');
    }

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class, 'bank_account_id');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'reference_number', 'id');
    }

    /***********
     * accesor amount_paid
     */

    public function getAmountPaidAttribute()
    {
        return $this->principal + $this->interest + $this->penalt_amount + $this->fee_amount;
    }

    /***********
     * accesor arrears_amount
     */
    public function getArrearsAmountAttribute()
    {
        // Fetch the schedule
        $schedule = $this->schedule;

        if (!$schedule) {
            return 0.0;
        }

        // Total due from schedule
        $totalDue = $schedule->principal + $schedule->interest;

        // Total paid across all repayments for that schedule
        $repayments = self::where('loan_schedule_id', $this->loan_schedule_id)->get();

        $totalPaid = $repayments->sum('principal')
            + $repayments->sum('interest');

        return round($totalDue - $totalPaid, 2);
    }

    /**
     * Get total principal paid for a specific loan
     * 
     * @param int $loanId
     * @return float
     */
    public static function getTotalPrincipalPaidForLoan($loanId): float
    {
        return self::where('loan_id', $loanId)->sum('principal');
    }

    /**
     * Get total interest paid for a specific loan
     * 
     * @param int $loanId
     * @return float
     */
    public static function getTotalInterestPaidForLoan($loanId): float
    {
        return self::where('loan_id', $loanId)->sum('interest');
    }
}

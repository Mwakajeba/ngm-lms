<?php

namespace App\Models;

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
        return $this->belongsTo(LoanProduct::class);
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
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'customer_id',
        'amount',
        'disbursed_on',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

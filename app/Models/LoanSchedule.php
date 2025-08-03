<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSchedule extends Model
{
    use HasFactory;
    protected $table = 'loan_shedules';
    protected $fillable = ['loan_id', 'interest', 'principal', 'end_date','end_grace_date','end_pernalty_date','customer_id','due_date'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlTransaction extends Model
{
    use HasFactory;

    protected $table = 'gl_transactions';

    protected $fillable = [
        'chart_account_id',
        'customer_id',
        'amount',
        'nature',
        'transaction_id',
        'transaction_type',
        'date',
        'description',
        'branch_id',
        'user_id',
    ];

    // Optional: Define relationships
    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

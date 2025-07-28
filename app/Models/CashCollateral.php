<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashCollateral extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'amount',
        'type_id',
        'description',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function type()
    {
        return $this->belongsTo(CashCollateralType::class, 'type_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'chart_account_id',
        'amount',
        'description',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class);
    }
}

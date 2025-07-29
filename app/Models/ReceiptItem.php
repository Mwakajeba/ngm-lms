<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_id',
        'chart_account_id',
        'amount',
    ];

    // Relationships
    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class);
    }
}

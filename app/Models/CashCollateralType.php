<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashCollateralType extends Model
{
    protected $table = 'cash_collateral_types';

    protected $fillable = [
        'name',
        'chart_account_id',
        'description',
        'is_active',
    ];

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class, 'chart_account_id');
    }
}

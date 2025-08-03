<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanProduct extends Model
{
    protected $fillable = [
        'name',
        'product_type',
        'minimum_interest_rate',
        'maximum_interest_rate',
        'interest_cycle',
        'interest_method',
        'minimum_principal',
        'maximum_principal',
        'minimum_period',
        'maximum_period',
        'top_up_type',
        'top_up_type_value',
        'has_cash_collateral',
        'cash_collateral_type',
        'cash_collateral_value_type',
        'cash_collateral_value',
        'has_approval_levels',
        'approval_levels',
        'principal_receivable_account_id',
        'interest_receivable_account_id',
        'interest_revenue_account_id',
        'fees_ids',
        'penalty_ids',
        'repayment_order',
    ];

    protected $casts = [
        'minimum_interest_rate' => 'decimal:2',
        'maximum_interest_rate' => 'decimal:2',
        'minimum_principal' => 'decimal:2',
        'maximum_principal' => 'decimal:2',
        'top_up_type_value' => 'decimal:2',
        'has_cash_collateral' => 'boolean',
        'cash_collateral_value' => 'decimal:2',
        'has_approval_levels' => 'boolean',
        'minimum_period' => 'integer',
        'maximum_period' => 'integer',
        'fees_ids' => 'array',
        'penalty_ids' => 'array',
    ];


    public static function frequencies()
    {
        return [
            'Daily'          => 365,
            'Weekly'         => 52,
            'Monthly'        => 12,
            'Quarterly'      => 4,
            'Semi Annually'  => 2,
            'Annually'       => 1,
        ];
    }

    /**
     * Get the principal receivable account for this loan product
     */
    public function principalReceivableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'principal_receivable_account_id');
    }

    /**
     * Get the interest receivable account for this loan product
     */
    public function interestReceivableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'interest_receivable_account_id');
    }

    /**
     * Get the interest revenue account for this loan product
     */
    public function interestRevenueAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'interest_revenue_account_id');
    }

    /**
     * Get the fees associated with this loan product
     */
    public function fees()
    {
        return $this->belongsToMany(Fee::class, null, null, null, 'fees_ids');
    }

    /**
     * Get the penalties associated with this loan product
     */
    public function penalties()
    {
        return $this->belongsToMany(Penalty::class, null, null, null, 'penalty_ids');
    }

    /**
     * Get the fees for this loan product
     */
    public function getFeesAttribute()
    {
        if (!$this->fees_ids) {
            return collect();
        }
        return Fee::whereIn('id', $this->fees_ids)->get();
    }

    /**
     * Get the penalties for this loan product
     */
    public function getPenaltiesAttribute()
    {
        if (!$this->penalty_ids) {
            return collect();
        }
        return Penalty::whereIn('id', $this->penalty_ids)->get();
    }

    /**
     * Get the cash collateral type associated with this loan product
     */
    public function cashCollateralType()
    {
        return $this->belongsTo(CashCollateralType::class, 'cash_collateral_type', 'name');
    }

    /**
     * Get the loans associated with this product
     */
    // TODO: Add loan_product_id to loans table and uncomment this relationship
    // public function loans()
    // {
    //     return $this->hasMany(Loan::class);
    // }

    /**
     * Get repayment order as array
     */
    public function getRepaymentOrderArrayAttribute()
    {
        return $this->repayment_order ? explode(',', $this->repayment_order) : [];
    }

    /**
     * Set repayment order from array
     */
    public function setRepaymentOrderArrayAttribute($value)
    {
        $this->attributes['repayment_order'] = is_array($value) ? implode(',', $value) : $value;
    }
}

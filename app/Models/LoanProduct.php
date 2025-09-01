<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanProduct extends Model
{
    use LogsActivity;
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
        'grace_period', // Added grace period
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
        'direct_writeoff_account_id',
        'provision_writeoff_account_id',
        'income_provision_account_id',
        'fees_ids',
        'penalty_ids',
        'repayment_order',
        'is_active',
        'penalt_deduction_criteria',
        'allow_push_to_ess',
    ];

    public function incomeProvisionAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'income_provision_account_id');
    }
    public function directWriteoffAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'direct_writeoff_account_id');
    }

    public function provisionWriteoffAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'provision_writeoff_account_id');
    }

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
        'is_active' => 'boolean',
        'allow_push_to_ess' => 'boolean',
    ];


    public static function frequencies()
    {
        return [
            'Daily' => 365,
            'Weekly' => 52,
            'Monthly' => 12,
            'Quarterly' => 4,
            'Semi Annually' => 2,
            'Annually' => 1,
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
    // public function fees()
    // {
    //     return $this->belongsToMany(Fee::class, null, null, null, 'fees_ids');
    // }

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'fee_ids')->where('include_in_schedule', true);
    }


    /**
     * Get the penalties associated with this loan product
     */
    // public function penalties()
    // {
    //     return $this->belongsToMany(Penalty::class, null, null, null, 'penalty_ids');
    // }
    public function penalty()
    {
        return $this->belongsTo(Penalty::class, 'penalty_ids')->where('status', 'active');
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
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

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

    public function requiresCollateral(): bool
    {
        return $this->has_cash_collateral;
    }

    public function calculateRequiredCollateral(float $loanAmount): float
    {
        if (!$this->has_cash_collateral)
            return 0;

        return $this->cash_collateral_value_type === 'percentage'
            ? ($loanAmount * $this->cash_collateral_value / 100)
            : $this->cash_collateral_value;
    }

    //// GET TOPUP AMOUNT FOR THIS LOAN////

    public function topupAmount(float $loanAmount)
    {
        return $this->top_up_type === 'percentage'
            ? ($loanAmount * $this->top_up_type_value / 100)
            : $this->top_up_type_value;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChartAccount extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chart_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_class_group_id',
        'account_code',
        'account_name',
        'has_cash_flow',
        'has_equity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_cash_flow' => 'boolean',
        'has_equity' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the account class group that owns the chart account.
     */
    public function accountClassGroup(): BelongsTo
    {
        return $this->belongsTo(AccountClassGroup::class, 'account_class_group_id');
    }
}

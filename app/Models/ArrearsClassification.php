<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArrearsClassification extends Model
{
    use LogsActivity;

    protected $table = 'arrears_classifications';

    protected $fillable = [
        'company_id',
        'days_from',
        'days_to',
        'bucket_label',
        'status',
        'provision_percentage',
        'sort_order',
        'is_active',
        'comments',
    ];

    protected $casts = [
        'days_from' => 'integer',
        'days_to' => 'integer',
        'provision_percentage' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

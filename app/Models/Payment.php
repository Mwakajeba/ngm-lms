<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'reference_type',
        'reference_number',
        'amount',
        'date',
        'description',
        'attachment',
        'bank_account_id',
        'customer_id',
        'branch_id',
        'user_id',
        'approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'datetime',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function paymentItems()
    {
        return $this->hasMany(PaymentItem::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('approved', false);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByReference($query, $reference)
    {
        return $query->where('reference', 'like', "%{$reference}%");
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return $this->approved ? 
            '<span class="badge bg-success">Approved</span>' : 
            '<span class="badge bg-warning">Pending</span>';
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->format('d/m/Y');
    }

    public function getAttachmentNameAttribute()
    {
        if (!$this->attachment) {
            return null;
        }
        return basename($this->attachment);
    }
}

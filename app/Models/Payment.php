<?php

namespace App\Models;

use App\Helpers\HashIdHelper;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory,LogsActivity;

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
        'supplier_id',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }



    public function paymentItems()
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function glTransactions()
    {
        return $this->hasMany(GlTransaction::class, 'transaction_id')
            ->where('transaction_type', 'payment');
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
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('M d, Y') : 'N/A';
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->approved) {
            return '<span class="badge bg-success">Approved</span>';
        }
        return '<span class="badge bg-warning">Pending</span>';
    }

    public function getTotalAmountAttribute()
    {
        return $this->paymentItems->sum('amount');
    }

    public function getAttachmentNameAttribute()
    {
        if (!$this->attachment) {
            return null;
        }
        return basename($this->attachment);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'hash_id';
    }

    /**
     * Resolve the model instance for the given hash ID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If field is hash_id, decode the hash ID
        if ($field === 'hash_id' || $field === null) {
            $id = HashIdHelper::decode($value);
            if ($id !== null) {
                return $this->findOrFail($id);
            }
        }
        
        // If not a hash ID, try as regular ID
        return $this->findOrFail($value);
    }

    /**
     * Get the hash ID for this model.
     */
    public function getHashIdAttribute()
    {
        return HashIdHelper::encode($this->id);
    }

    /**
     * Get the hash ID for routing.
     */
    public function getRouteKey()
    {
        return HashIdHelper::encode($this->id);
    }
}

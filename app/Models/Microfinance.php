<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Microfinance extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email'
    ];

    /**
     * Get the microfinance's display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->name ?: 'Valued Customer';
    }

    /**
     * Scope to get only microfinances with valid emails
     */
    public function scopeWithValidEmails($query)
    {
        return $query->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->where('email', 'like', '%@%');
    }
}

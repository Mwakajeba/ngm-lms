<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountClass extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'account_class';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the account class groups for this class.
     */
    public function accountClassGroups(): HasMany
    {
        return $this->hasMany(AccountClassGroup::class, 'class_id');
    }
}

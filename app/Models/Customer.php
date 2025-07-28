<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Extend this if customers log in
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // If customers do NOT need to login, extend Model instead of Authenticatable
    // use Illuminate\Database\Eloquent\Model;

    protected $fillable = [
        'customerNo',
        'name',
        'work',
        'workAddress',
        'phone1',
        'phone2',
        'registrar',
        'idType',
        'idNumber',
        'dob',
        'region',
        'district',
        'branch_id',
        'company_id',
        'sex',
        'password',
        'dateRegistered',
        'relation',
        'photo',
        'document',
        'has_cash_collateral',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'dob' => 'date',
        'dateRegistered' => 'date',
    ];

    // Relationships
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'registrar');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function collaterals()
    {
        return $this->hasMany(CashCollateral::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
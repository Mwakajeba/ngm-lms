<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filetype extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_file_types')
                    ->withTimestamps();
    }

    public function loanFiles()
    {
        return $this->hasMany(LoanFile::class, 'file_type_id');
    }
}

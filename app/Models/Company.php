<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
    'name', 'email', 'phone', 'address', 'logo', 'bg_color','txt_color',
];

public function branches()
{
    return $this->hasMany(Branch::class);
}

}

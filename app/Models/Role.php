<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Menu;

class Role extends SpatieRole
{
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_role');
    }
}
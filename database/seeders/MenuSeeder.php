<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
//use Spatie\Permission\Models\Role;
use App\Models\Role;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // Clean existing menus (optional for dev)
        // Menu::truncate();

        // Create "Users" parent menu
        $usersMenu = Menu::firstOrCreate([
            'name' => 'Users',
            'route' => null,
            'parent_id' => null,
            'icon' => 'bx bx-home'
        ]);

        // Create submenus
        $userList = Menu::firstOrCreate([
            'name' => 'User List',
            'route' => 'users.index',
            'parent_id' => $usersMenu->id,
            'icon' => 'bx bx-right-arrow-alt'
        ]);

        $userCreate = Menu::firstOrCreate([
            'name' => 'Add New User',
            'route' => 'users.create',
            'parent_id' => $usersMenu->id,
            'icon' => 'bx bx-right-arrow-alt'
        ]);

        // Assign menus to "admin" role
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            $adminRole->menus()->syncWithoutDetaching([
                $usersMenu->id,
                $userList->id,
                $userCreate->id,
            ]);
        }
    }
}

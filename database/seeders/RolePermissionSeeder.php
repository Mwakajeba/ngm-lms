<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Define all permissions
        $permissions = [
            // Branch permissions
            'view branches',
            'create branches',
            'edit branches',
            'delete branches',

            // User permissions
            'view users',
            'create user',
            'edit user',
            'delete user',
        ];

        // Create or update permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Create or get admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Assign all current permissions to admin role
        $adminRole->syncPermissions(Permission::all());

        // Assign admin role to user with ID 1 (if exists)
        $user = \App\Models\User::find(1);
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}




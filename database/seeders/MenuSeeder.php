<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $this->command->warn('Admin role not found.');
            return;
        }

        $entities = [
            'Users' => [
                'icon' => 'bx bx-user',
                'visibleRoutes' => [
                    ['name' => 'User List', 'route' => 'users.index'],
                    ['name' => 'Add New User', 'route' => 'users.create'],
                ],
                'hiddenRoutes' => ['users.edit', 'users.destroy'],
            ],
            'Company' => [
                'icon' => 'bx bx-building',
                'visibleRoutes' => [
                    ['name' => 'Company List', 'route' => 'companies.index'],
                    ['name' => 'Add New Company', 'route' => 'companies.create'],
                ],
                'hiddenRoutes' => ['companies.edit', 'companies.destroy'],
            ],
            'Branch' => [
                'icon' => 'bx bx-git-branch',
                'visibleRoutes' => [
                    ['name' => 'Branch List', 'route' => 'branches.index'],
                    ['name' => 'Add New Branch', 'route' => 'branches.create'],
                ],
                'hiddenRoutes' => ['branches.edit', 'branches.destroy'],
            ],
        ];

        foreach ($entities as $parentName => $data) {
            $parent = Menu::firstOrCreate([
                'name' => $parentName,
                'route' => null,
                'parent_id' => null,
                'icon' => $data['icon'],
            ]);

            $menuIds = [$parent->id];

            // Only visible menu entries
            foreach ($data['visibleRoutes'] as $child) {
                $childMenu = Menu::firstOrCreate([
                    'name' => $child['name'],
                    'route' => $child['route'],
                    'parent_id' => $parent->id,
                    'icon' => 'bx bx-right-arrow-alt',
                ]);

                $menuIds[] = $childMenu->id;
            }

            // Hidden permission-only routes (not shown in menu)
            foreach ($data['hiddenRoutes'] as $route) {
                $hiddenMenu = Menu::firstOrCreate([
                    'name' => $route, // use route name for clarity
                    'route' => $route,
                    'parent_id' => null, // not shown under any parent
                    'icon' => 'bx bx-right-arrow-alt',
                ]);

                $menuIds[] = $hiddenMenu->id;
            }

            $adminRole->menus()->syncWithoutDetaching($menuIds);
        }
    }
}

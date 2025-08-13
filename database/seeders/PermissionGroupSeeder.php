<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionGroup;

class PermissionGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'user',
                'display_name' => 'User Management',
                'description' => 'Permissions related to user and staff management',
                'color' => '#007bff',
                'icon' => 'bx bx-user',
                'sort_order' => 1,
            ],
            [
                'name' => 'customer',
                'display_name' => 'Customer Management',
                'description' => 'Permissions related to customer management and profiles',
                'color' => '#28a745',
                'icon' => 'bx bx-group',
                'sort_order' => 2,
            ],
            [
                'name' => 'loan',
                'display_name' => 'Loan Management',
                'description' => 'Permissions related to loan applications, approvals, and management',
                'color' => '#ffc107',
                'icon' => 'bx bx-money',
                'sort_order' => 3,
            ],
            [
                'name' => 'loan-product',
                'display_name' => 'Loan Product Management',
                'description' => 'Permissions related to loan product configuration and management',
                'color' => '#17a2b8',
                'icon' => 'bx bx-package',
                'sort_order' => 4,
            ],
            [
                'name' => 'loan-group',
                'display_name' => 'Loan Group Management',
                'description' => 'Permissions related to loan group management and operations',
                'color' => '#dc3545',
                'icon' => 'bx bx-layer-group',
                'sort_order' => 5,
            ],
            [
                'name' => 'cash-collateral',
                'display_name' => 'Cash Collateral Management',
                'description' => 'Permissions related to cash collateral management and transactions',
                'color' => '#6f42c1',
                'icon' => 'bx bx-wallet',
                'sort_order' => 6,
            ],
            [
                'name' => 'accounting',
                'display_name' => 'Accounting & Financial',
                'description' => 'Permissions related to accounting, journals, and financial management',
                'color' => '#20c997',
                'icon' => 'bx bx-calculator',
                'sort_order' => 7,
            ],
            [
                'name' => 'report',
                'display_name' => 'Reports & Analytics',
                'description' => 'Permissions related to reports, analytics, and data analysis',
                'color' => '#fd7e14',
                'icon' => 'bx bx-bar-chart-alt-2',
                'sort_order' => 8,
            ],
            [
                'name' => 'chat',
                'display_name' => 'Chat & Communication',
                'description' => 'Permissions related to chat features and communication',
                'color' => '#e83e8c',
                'icon' => 'bx bx-message-rounded',
                'sort_order' => 9,
            ],
            [
                'name' => 'settings',
                'display_name' => 'Settings & Configuration',
                'description' => 'Permissions related to system settings and configuration',
                'color' => '#6c757d',
                'icon' => 'bx bx-cog',
                'sort_order' => 10,
            ],
            [
                'name' => 'ai',
                'display_name' => 'AI Assistant',
                'description' => 'Permissions related to AI assistant features',
                'color' => '#6610f2',
                'icon' => 'bx bx-brain',
                'sort_order' => 11,
            ],
            [
                'name' => 'dashboard',
                'display_name' => 'Dashboard & Analytics',
                'description' => 'Permissions related to dashboard access and analytics',
                'color' => '#198754',
                'icon' => 'bx bx-tachometer',
                'sort_order' => 12,
            ],
            [
                'name' => 'menu',
                'display_name' => 'Menu Management',
                'description' => 'Permissions related to menu and navigation management',
                'color' => '#0d6efd',
                'icon' => 'bx bx-menu',
                'sort_order' => 13,
            ],
            [
                'name' => 'company',
                'display_name' => 'Company Management',
                'description' => 'Permissions related to company settings and management',
                'color' => '#0dcaf0',
                'icon' => 'bx bx-building',
                'sort_order' => 14,
            ],
            [
                'name' => 'branch',
                'display_name' => 'Branch Management',
                'description' => 'Permissions related to branch management and operations',
                'color' => '#d63384',
                'icon' => 'bx bx-map',
                'sort_order' => 15,
            ],
        ];

        foreach ($groups as $group) {
            PermissionGroup::firstOrCreate(
                ['name' => $group['name']],
                $group
            );
        }
    }
}

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
            'Dashboard' => [
                'icon' => 'bx bx-home',
                'visibleRoutes' => [
                    ['name' => 'Dashboard', 'route' => 'dashboard'],
                ],
                'hiddenRoutes' => [],
            ],
            'Users' => [
                'icon' => 'bx bx-user',
                'visibleRoutes' => [
                    ['name' => 'User List', 'route' => 'users.index'],
                    ['name' => 'Add New User', 'route' => 'users.create'],
                    ['name' => 'User Profile', 'route' => 'users.profile'],
                ],
                'hiddenRoutes' => ['users.edit', 'users.destroy', 'users.show', 'users.status', 'users.roles'],
            ],
            'Settings' => [
                'icon' => 'bx bx-cog',
                'visibleRoutes' => [
                    ['name' => 'General Settings', 'route' => 'settings.index'],
                ],
                'hiddenRoutes' => ['settings.company', 'settings.branches', 'settings.user', 'settings.system', 'settings.backup', 'settings.branches.create', 'settings.branches.edit', 'settings.branches.destroy'],
            ],
            'Customers' => [
                'icon' => 'bx bx-group',
                'visibleRoutes' => [
                    ['name' => 'Customer List', 'route' => 'customers.index'],
                    ['name' => 'Add New Customer', 'route' => 'customers.create'],
                ],
                'hiddenRoutes' => ['customers.edit', 'customers.destroy', 'customers.show'],
            ],

            'Cash Collaterals' => [
                'icon' => 'bx bx-outline',
                'visibleRoutes' => [
                    ['name' => 'Cash Collateral Types', 'route' => 'cash_collateral_types.index'],
                    ['name' => 'Cash Collaterals', 'route' => 'cash_collaterals.index'],
                ],
                'hiddenRoutes' => ['cash_collateral_types.create', 'cash_collateral_types.edit', 'cash_collateral_types.destroy', 'cash_collateral_types.show','cash_collaterals.create', 'cash_collaterals.edit', 'cash_collaterals.destroy', 'cash_collaterals.show'],
            ],

            'Accounting' => [
                'icon' => 'bx bx-calculator',
                'visibleRoutes' => [
                    ['name' => 'Charts of account - FSLI', 'route' => 'accounting.fsli-accounts'],
                    ['name' => 'Charts of account', 'route' => 'accounting.accounts'],
                    ['name' => 'Suppliers', 'route' => 'accounting.suppliers.index'],
                    ['name' => 'Manual journal entries', 'route' => 'accounting.journal-entries'],
                    ['name' => 'Payment voucher', 'route' => 'accounting.payment-vouchers'],
                    ['name' => 'Receipt voucher', 'route' => 'accounting.receipt-vouchers'],
                    ['name' => 'Bank accounts', 'route' => 'accounting.bank-accounts'],
                    ['name' => 'Bank transfer', 'route' => 'accounting.bank-transfers'],
                    ['name' => 'Bank reconciliation', 'route' => 'accounting.bank-reconciliation'],
                    ['name' => 'Bill purchases', 'route' => 'accounting.bill-purchases'],
                    ['name' => 'Budget', 'route' => 'accounting.budget'],
                    ['name' => 'Fees', 'route' => 'accounting.fees.index'],
                    ['name' => 'Penalties', 'route' => 'accounting.penalties.index'],
                ],
                'hiddenRoutes' => ['accounting.accounts.create', 'accounting.accounts.edit', 'accounting.accounts.destroy', 'accounting.journal-entries.create', 'accounting.journal-entries.edit', 'accounting.journal-entries.destroy'],
            ],
            'Accounting Reports' => [
                'icon' => 'bx bx-file',
                'visibleRoutes' => [
                    ['name' => 'Other income report', 'route' => 'accounting.reports.other-income'],
                    ['name' => 'Trial balance report', 'route' => 'accounting.reports.trial-balance'],
                    ['name' => 'Income statement report', 'route' => 'accounting.reports.income-statement'],
                    ['name' => 'Balance sheet report', 'route' => 'accounting.reports.balance-sheet'],
                    ['name' => 'Cash book report', 'route' => 'accounting.reports.cash-book'],
                    ['name' => 'Cash flow report', 'route' => 'accounting.reports.cash-flow'],
                    ['name' => 'General ledger transactions', 'route' => 'accounting.reports.general-ledger'],
                    ['name' => 'Expenses summary report', 'route' => 'accounting.reports.expenses-summary'],
                    ['name' => 'Accounting notes', 'route' => 'accounting.reports.accounting-notes'],
                    ['name' => 'Changes in equity', 'route' => 'accounting.reports.changes-equity'],
                ],
                'hiddenRoutes' => [],
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
            // These routes are for permissions only and should not be created as menu entries
            // They are handled by the permission system directly

            $adminRole->menus()->syncWithoutDetaching($menuIds);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountClassGroup;
use App\Models\AccountClass;

class AccountClassGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company
        $companyId = \App\Models\Company::first()->id ?? 1;

        $accountClassGroups = [
            [
                'class_id' => 1, // Assets
                'group_code' => 'CASH',
                'name' => 'Cash and Cash Equivalents',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 1, // Assets
                'group_code' => 'RECEIVABLES',
                'name' => 'Accounts Receivable',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 1, // Assets
                'group_code' => 'INVENTORY',
                'name' => 'Inventory',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 1, // Assets
                'group_code' => 'FIXED_ASSETS',
                'name' => 'Fixed Assets',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 2, // Liabilities
                'group_code' => 'PAYABLES',
                'name' => 'Accounts Payable',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 2, // Liabilities
                'group_code' => 'LOANS',
                'name' => 'Loans and Borrowings',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 3, // Revenue
                'group_code' => 'SALES',
                'name' => 'Sales Revenue',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 3, // Revenue
                'group_code' => 'OTHER_INCOME',
                'name' => 'Other Income',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 4, // Expenses
                'group_code' => 'COGS',
                'name' => 'Cost of Goods Sold',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 4, // Expenses
                'group_code' => 'OPERATING_EXPENSES',
                'name' => 'Operating Expenses',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 5, // Equity
                'group_code' => 'CAPITAL',
                'name' => 'Share Capital',
                'company_id' => $companyId,
            ],
            [
                'class_id' => 5, // Equity
                'group_code' => 'RETAINED_EARNINGS',
                'name' => 'Retained Earnings',
                'company_id' => $companyId,
            ],
        ];

        foreach ($accountClassGroups as $group) {
            AccountClassGroup::firstOrCreate(
                ['group_code' => $group['group_code']],
                $group
            );
        }
    }
}

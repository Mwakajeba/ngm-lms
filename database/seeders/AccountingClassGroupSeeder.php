<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountingClassGroup;

class AccountingClassGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classGroups = [
            [
                'name' => 'Assets',
                'code' => 'ASSET',
                'description' => 'Resources owned by the company that have economic value',
                'type' => 'asset',
                'is_active' => true,
            ],
            [
                'name' => 'Liabilities',
                'code' => 'LIABILITY',
                'description' => 'Obligations and debts owed by the company',
                'type' => 'liability',
                'is_active' => true,
            ],
            [
                'name' => 'Equity',
                'code' => 'EQUITY',
                'description' => 'Owner\'s investment and retained earnings',
                'type' => 'equity',
                'is_active' => true,
            ],
            [
                'name' => 'Revenue',
                'code' => 'REVENUE',
                'description' => 'Income earned from business operations',
                'type' => 'revenue',
                'is_active' => true,
            ],
            [
                'name' => 'Expenses',
                'code' => 'EXPENSE',
                'description' => 'Costs incurred in running the business',
                'type' => 'expense',
                'is_active' => true,
            ],
        ];

        foreach ($classGroups as $group) {
            AccountingClassGroup::firstOrCreate(
                ['code' => $group['code']],
                $group
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountClasses = [
            [
                'name' => 'Assets',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Liabilities',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Revenue',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Expenses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Equity',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ];

        foreach ($accountClasses as $accountClass) {
            DB::table('account_class')->insertOrIgnore($accountClass);
        }
    }
}

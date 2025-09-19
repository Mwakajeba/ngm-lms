<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Check if any user already exists
        if (User::exists()) {
            $this->command->warn('Users already exist. Skipping seeding.');
            return;
        }
        // Get all branches
        $branches = Branch::all();

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Seed branches first.');
            return;
        }

        // Seed one user per branch, but set role to 'super-admin'
        foreach ($branches as $index => $branch) {
            $user = User::create([
                'name' => 'Julius Mwakajeba ' . $index,
                'phone' => '255655577803' . $index,
                'email' => 'admin' . $index . '@safco.com',
                'password' => Hash::make('12345'),
                'branch_id' => $branch->id,
                'company_id' => $branch->company_id,
                'role' => 'super-admin',
                'is_active' => 'yes',
                'sms_verification_code' => '654321',
                'sms_verified_at' => now(),
            ]);

            // Assign super-admin role using Spatie permissions
            $user->assignRole('super-admin');
        }
    }
}

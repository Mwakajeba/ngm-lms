<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // User::factory(10)->create();

    $this->call([
      CompanySeeder::class,
      BranchSeeder::class,
      UserSeeder::class,
      RolePermissionSeeder::class,
      MenuSeeder::class,
      AccountingClassGroupSeeder::class,
      AccountClassSeeder::class,
      CashFlowCategorySeeder::class,
      EquityCategorySeeder::class,
      RegionsTableSeeder::class,
      DistrictsTableSeeder::class,
      SupplierSeeder::class,
    ]);
  }
}

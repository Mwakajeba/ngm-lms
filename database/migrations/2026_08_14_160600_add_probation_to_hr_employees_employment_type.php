<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hr_employees') || ! Schema::hasColumn('hr_employees', 'employment_type')) {
            return;
        }

        DB::statement("ALTER TABLE `hr_employees` MODIFY `employment_type` ENUM('full_time', 'part_time', 'contract', 'probation', 'casual', 'intern') NOT NULL");
    }

    public function down(): void
    {
        if (! Schema::hasTable('hr_employees') || ! Schema::hasColumn('hr_employees', 'employment_type')) {
            return;
        }

        DB::table('hr_employees')->whereIn('employment_type', ['probation', 'casual'])->update([
            'employment_type' => 'contract',
        ]);

        DB::statement("ALTER TABLE `hr_employees` MODIFY `employment_type` ENUM('full_time', 'part_time', 'contract', 'intern') NOT NULL");
    }
};

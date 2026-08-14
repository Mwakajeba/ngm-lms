<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Employee email is optional on the create form.
     */
    public function up(): void
    {
        if (! Schema::hasTable('hr_employees')) {
            return;
        }

        // Avoid doctrine/dbal dependency — native MySQL alter
        DB::statement('ALTER TABLE `hr_employees` MODIFY `email` VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('hr_employees')) {
            return;
        }

        DB::statement('ALTER TABLE `hr_employees` MODIFY `email` VARCHAR(255) NOT NULL');
    }
};

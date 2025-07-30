<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->integer('minimum_members')->default(5)->after('loan_officer');
            $table->integer('maximum_members')->default(20)->after('minimum_members');
            $table->foreignId('group_leader')->nullable()->constrained('users')->onDelete('set null')->after('maximum_members');
            $table->enum('meeting_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable()->after('group_leader');
            $table->time('meeting_time')->nullable()->after('meeting_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['group_leader']);
            $table->dropColumn(['minimum_members', 'maximum_members', 'group_leader', 'meeting_day', 'meeting_time']);
        });
    }
};

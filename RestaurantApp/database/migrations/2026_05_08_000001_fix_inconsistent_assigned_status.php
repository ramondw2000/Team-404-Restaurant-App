<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix tasks with status 'assigned' but no assignee
        DB::table('maintenance_tasks')
            ->where('status', 'assigned')
            ->whereNull('assigned_to')
            ->update(['status' => 'unassigned']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this data fix
    }
};

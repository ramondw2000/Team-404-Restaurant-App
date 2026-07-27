<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert any remaining 'pending' status to 'unassigned'
        DB::table('maintenance_tasks')
            ->where('status', 'pending')
            ->update(['status' => 'unassigned']);

        // Update default value in schema
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->string('status')->default('unassigned')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};

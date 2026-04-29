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
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('notes')
                ->constrained('users')->nullOnDelete();
            $table->json('requirements')->nullable()->after('assigned_to');
            $table->index('assigned_to');
        });

        DB::table('maintenance_tasks')
            ->where('status', 'pending')
            ->update(['status' => 'assigned']);

        DB::table('maintenance_tasks')
            ->where('status', 'completed')
            ->update(['status' => 'done']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('maintenance_tasks')
            ->where('status', 'done')
            ->update(['status' => 'completed']);

        DB::table('maintenance_tasks')
            ->whereIn('status', ['assigned', 'in_progress'])
            ->update(['status' => 'pending']);

        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['assigned_to']);
            $table->dropColumn(['assigned_to', 'requirements']);
        });
    }
};

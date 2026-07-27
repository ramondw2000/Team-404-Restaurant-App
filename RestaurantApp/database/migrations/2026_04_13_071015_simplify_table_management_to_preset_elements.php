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
        // Delete all existing floor plan elements (they reference the old image-based system)
        DB::table('floor_plan_elements')->delete();

        Schema::table('floor_plan_elements', function (Blueprint $table): void {
            $table->dropForeign(['image_id']);
            $table->dropColumn(['image_id', 'is_table']);

            $table->string('shape')->after('floor_plan_id');
            $table->unsignedTinyInteger('seat_count')->nullable(false)->change();
        });

        Schema::table('images', function (Blueprint $table): void {
            $table->dropColumn(['crop_x', 'crop_y', 'crop_w', 'crop_h']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('floor_plan_elements', function (Blueprint $table): void {
            $table->dropColumn('shape');
            $table->unsignedTinyInteger('seat_count')->nullable()->change();

            $table->foreignId('image_id')->after('floor_plan_id')->constrained('images')->restrictOnDelete();
            $table->boolean('is_table')->default(false)->after('z_index');
        });

        Schema::table('images', function (Blueprint $table): void {
            $table->float('crop_x')->default(0);
            $table->float('crop_y')->default(0);
            $table->float('crop_w')->default(100);
            $table->float('crop_h')->default(100);
        });
    }
};

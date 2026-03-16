<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table): void {
            $table->id();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->float('crop_x')->default(0);
            $table->float('crop_y')->default(0);
            $table->float('crop_w')->default(100);
            $table->float('crop_h')->default(100);
            $table->timestamps();
        });

        Schema::create('floor_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('background_image_id')->nullable()->constrained('images')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('floor_plan_elements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('floor_plan_id')->constrained('floor_plans')->cascadeOnDelete();
            $table->foreignId('image_id')->constrained('images')->restrictOnDelete();
            $table->decimal('x', 8, 4);
            $table->decimal('y', 8, 4);
            $table->decimal('width', 8, 4);
            $table->decimal('height', 8, 4);
            $table->decimal('rotation', 8, 4)->default(0);
            $table->unsignedInteger('z_index')->default(0);
            $table->boolean('is_table')->default(false);
            $table->string('table_name')->nullable();
            $table->unsignedTinyInteger('seat_count')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floor_plan_elements');
        Schema::dropIfExists('floor_plans');
        Schema::dropIfExists('images');
    }
};

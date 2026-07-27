<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('origin', 16)->default('restaurant')->after('user_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['floor_plan_element_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('floor_plan_element_id')->nullable()->change();

            $table->foreign('floor_plan_element_id')
                ->references('id')
                ->on('floor_plan_elements')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('origin');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['floor_plan_element_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('floor_plan_element_id')->nullable(false)->change();

            $table->foreign('floor_plan_element_id')
                ->references('id')
                ->on('floor_plan_elements')
                ->restrictOnDelete();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_category_dish', function (Blueprint $table) {
            $table->foreignId('menu_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->primary(['menu_category_id', 'dish_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_category_dish');
    }
};

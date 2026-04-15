<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dish_ingredient', function (Blueprint $table) {
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->primary(['dish_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_ingredient');
    }
};

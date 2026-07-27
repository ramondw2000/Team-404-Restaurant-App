<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn(['category', 'allergens', 'dietary']);
        });
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->string('category')->default('Mains');
            $table->json('allergens')->nullable();
            $table->json('dietary')->nullable();
        });
    }
};

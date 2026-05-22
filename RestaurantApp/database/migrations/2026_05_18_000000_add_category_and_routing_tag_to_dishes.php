<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table): void {
            $table->string('category', 50)->nullable()->after('is_bar_item')->index();
            $table->string('routing_tag', 20)->nullable()->after('category')->index();
        });

        // Update existing dishes with default values
        DB::table('dishes')->where('is_bar_item', true)->update([
            'category' => 'drinks',
            'routing_tag' => 'bar',
        ]);

        DB::table('dishes')->where('is_bar_item', false)->orWhereNull('is_bar_item')->update([
            'category' => 'food',
            'routing_tag' => 'kitchen',
        ]);
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table): void {
            $table->dropColumn(['category', 'routing_tag']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('guest_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('party_size');
            $table->dateTime('reservation_datetime');
            $table->string('table_number')->nullable();
            $table->string('room_number')->nullable();
            $table->string('status')->default('scheduled');
            $table->text('internal_notes')->nullable();
            $table->boolean('allergies_or_dietary')->default(false);
            $table->decimal('deposit_amount', 8, 2)->nullable();
            $table->string('deposit_status')->nullable();
            $table->timestamps();

            $table->index('reservation_datetime');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

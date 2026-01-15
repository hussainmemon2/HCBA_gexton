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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('auditorium_id')->constrained('auditoriums')->cascadeOnDelete();
            $table->foreignId('booked_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['approved', 'requested'])->default('requested');
            $table->date('booking_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

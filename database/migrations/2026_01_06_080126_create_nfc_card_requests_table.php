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
        Schema::create('nfc_card_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->enum('request_type', [
            'lost',
            'damaged'
            ]);

            $table->text('reason')->nullable();

            $table->enum('status', [
            'pending',
            'approved',
            'rejected'
            ])->default('pending');

            $table->foreignId('processed_by')
            ->nullable()
            ->references('id')
            ->on('users')
            ->nullOnDelete();

            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfc_card_requests');
    }
};

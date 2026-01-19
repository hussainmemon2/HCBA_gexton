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
        Schema::create('complaint_transfers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('complaint_id')
            ->constrained()
            ->cascadeOnDelete();
        $table->foreignId('from_committee_id')
            ->constrained('committees')
            ->cascadeOnDelete();
        $table->foreignId('to_committee_id')
            ->constrained('committees')
            ->cascadeOnDelete();
        $table->foreignId('transferred_by')
            ->constrained('users')
            ->cascadeOnDelete();
        $table->text('reason');
        $table->timestamps();
        $table->index(['complaint_id', 'to_committee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_transfers');
    }
};

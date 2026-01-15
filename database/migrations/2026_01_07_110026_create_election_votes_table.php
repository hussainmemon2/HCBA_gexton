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
        Schema::create('election_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('position_id')
                ->constrained('election_positions')
                ->cascadeOnDelete();
            $table->foreignId('candidate_id')
                ->constrained('election_candidates')
                ->cascadeOnDelete();
            $table->foreignId('voter_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['election_id', 'position_id', 'voter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_votes');
    }
};

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
        Schema::create('election_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('position_id')
                ->nullable()
                ->constrained('election_positions')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('vakalatnama')->nullable();
            $table->string('case_order')->nullable();
            $table->string('fee_challan_of_bar_card')->nullable();
            $table->string('bar_certificate')->nullable();
            $table->string('no_dues_cert_from_high_court')->nullable();
            $table->string('no_dues_cert_from_sindh_bar')->nullable();
            $table->boolean('application_fee_paid')->nullable()->default(false);
            $table->boolean('submission_fee_paid')->nullable()->default(false);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->nullable()
                    ->default('draft');
            $table->timestamps();
            $table->unique(['election_id', 'position_id', 'user_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_applications');
    }
};

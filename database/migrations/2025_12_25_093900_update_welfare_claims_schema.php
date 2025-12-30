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
        // Create welfare_claim_remarks table
        Schema::create('welfare_claim_remarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_claim_id')->constrained();
            $table->text('remark');
            $table->timestamps();
        });

        // Drop user_id from welfare_claim_attachments
        Schema::table('welfare_claim_attachments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // Drop attachments from welfare_claims
        Schema::table('welfare_claims', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add attachments back to welfare_claims
        Schema::table('welfare_claims', function (Blueprint $table) {
            $table->text('attachments')->nullable();
        });

        // Add user_id back to welfare_claim_attachments
        Schema::table('welfare_claim_attachments', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
        });

        // Drop welfare_claim_remarks table
        Schema::dropIfExists('welfare_claim_remarks');
    }
};
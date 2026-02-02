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
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('accounts');
            $table->foreignId('checkbook_id')->constrained('checkbooks');
            $table->string('cheque_no', 20)->unique();
            $table->enum('status', ['unused','reserved','used','cancelled'])->default('unused');
            $table->enum('used_for_type', ['welfare','committee','vendor','withdraw'])->nullable();
            $table->unsignedBigInteger('used_for_id')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};

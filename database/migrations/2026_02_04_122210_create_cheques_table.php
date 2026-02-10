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
        $table->foreignId('checkbook_id')->constrained('checkbooks');
        $table->integer('cheque_no');
        $table->enum('status', ['unused','reserved','used'])->default('unused');
        $table->foreignId('used_for_id')->nullable(); // voucher id
        $table->string('used_for_type')->nullable(); // voucher
        $table->timestamps();

        $table->unique(['checkbook_id', 'cheque_no']);
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

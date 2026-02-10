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
    Schema::create('checkbooks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('bank_account_id')->constrained('accounts');
        $table->string('name', 50);
        $table->integer('start_no');
        $table->integer('end_no');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkbooks');
    }
};

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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique(); // auto generated
            $table->enum('voucher_type', ['receipt','payment']);
            $table->date('date');
            $table->enum('paid_by', ['member','other'])->nullable(); // only for receipt
            $table->text('description')->nullable();
            $table->enum('status', ['draft','pending','approved','rejected'])->default('draft');
            $table->enum('payment_method', ['cash','cheque','other'])->nullable(); // only for payment
            $table->foreignId('cheque_id')->nullable()->constrained('cheques');
            $table->string('title')->nullable(); // membership fee, book fee, etc
            $table->foreignId('created_by')->constrained('users');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->unsignedBigInteger('asset_account_id')->nullable();
            $table->unsignedBigInteger('expense_account_id')->nullable();
            $table->foreign('asset_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('expense_account_id')->references('id')->on('accounts')->onDelete('set null');
            // Polymorphic link to vendor/committee/welfare/member
            $table->foreignId('entity_id')->nullable(); 
            $table->string('entity_type')->nullable(); // 'vendor','committee','welfare','member'

            $table->timestamps();
        });

    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};

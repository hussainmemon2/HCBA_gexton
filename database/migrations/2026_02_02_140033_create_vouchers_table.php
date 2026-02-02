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
            $table->string('voucher_no', 50)->unique();
            $table->foreignId('voucher_type_id')->constrained('voucher_types');
            $table->date('date');
            $table->text('description')->nullable();
            $table->enum('status', ['draft','pending','approved','rejected'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('reversal_of')->nullable()->constrained('vouchers');
            $table->enum('payment_method', ['cash','cheque','other'])->default('cash');
            $table->foreignId('cheque_id')->nullable()->constrained('cheques');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->foreignId('committee_id')->nullable()->constrained('committees');
            $table->foreignId('welfare_request_id')->nullable()->constrained('welfare_claims');
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

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
        Schema::create('finance_transactions', function (Blueprint $table) {
                $table->id();
                // Transaction nature
                $table->enum('transaction_type', ['funding', 'expense']);
                // Funding / Expense category
                $table->enum('source_type', [
                    'annual_fee',
                    'donation',
                    'other_funding',
                    'committee_expense',
                    'welfare_expense',
                    'other_expense'
                ]);
                // Optional free-text source (e.g. donor name)
                // Optional relations
                $table->foreignId('member_id')->nullable()
                        ->constrained('users')->nullOnDelete();
                $table->foreignId('committee_id')->nullable()
                        ->constrained('committees')->nullOnDelete();
                $table->foreignId('welfare_claim_id')->nullable()
                        ->constrained('welfare_claims')->nullOnDelete();
                // Description
                $table->string('title');
                $table->text('remarks')->nullable();
                // Amounts
                $table->bigInteger('amount'); // store in smallest unit if needed
                // Balance tracking
                $table->bigInteger('balance_before');
                $table->bigInteger('balance_after');
                // Meta
                $table->date('transaction_date');
                $table->foreignId('created_by')->nullable()
                        ->constrained('users')->nullOnDelete();
                $table->timestamps();
        });
    }
    /*
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};

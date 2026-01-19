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
        Schema::create('complaint_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();

            $table->enum('from_status', ['open', 'closed', 'reopened', 'rejected'])->nullable();
            $table->enum('to_status', ['open', 'closed', 'reopened', 'rejected']);

            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();

            $table->text('reason')->nullable();
            $table->string('attachment')->nullable();

            $table->timestamps();

            $table->index(['complaint_id', 'to_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_histories');
    }
};

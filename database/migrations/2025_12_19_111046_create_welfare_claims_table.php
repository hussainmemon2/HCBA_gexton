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
        Schema::disableForeignKeyConstraints();

        Schema::create('welfare_claims', function (Blueprint $table) {
            $table->id();
            // user who is claiming for another user
            $table->foreignId('claimer_id')->constrained('users');
            $table->foreignId('user_id')->constrained();
            $table->enum('type', ["medical","death","others"]);
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('reason')->comment('provided_by_user')->nullable();
            $table->date('received_date')->nullable();
            $table->date('approved_date')->nullable();
            $table->date('rejected_date')->nullable();
            $table->date('funding_date')->comment('denoting when funding started')->nullable();
            // picked or rejected it cant be made again its closed
            //  time between these date will denote for how much time funding rasise and ended
            $table->date('ready_date')->comment('denoting check ready')->nullable();
            $table->date('collected_date')->comment('denoting when user collected it')->nullable();
            $table->text('attachments')->nullable();
            $table->enum('status', ["received","approved","funding","ready","rejected","collected"])->default('received');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_claims');
    }
};

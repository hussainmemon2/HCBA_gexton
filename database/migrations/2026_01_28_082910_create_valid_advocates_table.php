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
        Schema::create('valid_advocates', function (Blueprint $table) {
            $table->id(); // bigint unsigned, primary key

            $table->integer('reg_no')->nullable();

            $table->string('advocate_name', 512)->nullable();
            $table->string('father_name', 512)->nullable();

            $table->string('subdistrict', 255)->nullable();
            $table->string('division', 255)->nullable();
            $table->string('district', 512)->nullable();

            $table->string('hc_date', 512)->nullable();
            $table->string('lc_date', 255)->nullable();

            $table->string('enroll_type', 255)->nullable();
            $table->string('gender', 255)->nullable();

            $table->string('sbc_dues', 512)->nullable();
            $table->string('hcba_dues', 512)->nullable();

            $table->string('status', 255)->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valid_advocates');
    }
};

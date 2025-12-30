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
        // type general,welfare,committee
        // title,content,type
        // if type is general everyone can see it
        // if type committee require committee_id only committee member and chairman can see it
        // if type welfare for now everyone can see it
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->enum('type', ['general', 'welfare', 'committee'])->default('general');
            $table->text('content');
            $table->foreignId('posted_by')->constrained('users');
            $table->timestamp('posted_at');
            $table->timestamps();

            $table->foreignId('committee_id')->nullable()->constrained('committees');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};

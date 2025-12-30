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

        Schema::create('library_items', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('author_name', 200);
            $table->enum('type', ["book","e-journal"]);
            $table->date('return_date')->nullable()
            ->comment('expected to be returned');
            $table->string('rfid_tag', 50)->unique()->nullable();
            $table->timestamps();
        });

        // Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_items');
    }
};

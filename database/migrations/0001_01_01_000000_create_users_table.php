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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Basic Info
            $table->string('proposer_name');
            $table->string('seconder_name');
            $table->string('name', 100);
            $table->string('guardian_name', 100);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('caste', 100);
            // Identification
            $table->string('cnic', 20)->unique();
            $table->string('bar_license_number', 50)->unique();
            $table->string('cnic_front_path', 255);
            $table->string('idcard_of_highcourt_path', 255)->nullable();
            $table->string('license_ofhighcourt_path', 255)->nullable();
            $table->string('passport_image', 255)->nullable();
            $table->text('present_address' );
            $table->text('permanent_address' );
            $table->text('office_address' )->nullable();
            $table->date('date_of_enrollment_as_advocate');
            $table->date('date_of_enrollment_as_advocate_high_court')->nullable();
            $table->string('district_bar_member' );  //Disctric bar association member
            $table->string('other_bar_member' ); //other bar association member
            // Biometrics & Media
            $table->binary('cnic_image')->nullable();
            $table->binary('fingerprint1')->nullable();
            $table->binary('fingerprint2')->nullable();
            $table->binary('fingerprint3')->nullable();
            $table->binary('fingerprint4')->nullable();
            $table->binary('face_data')->nullable();
            // Contact
            $table->string('email', 100)->unique();
            $table->string('phone', 20)->unique();
            // Authentication
            $table->string('password', 255);
            $table->unsignedBigInteger('role_id')->nullable(); 
            // Verification
            $table->boolean('is_verified_nadra')->default(false);
            $table->boolean('is_verified_hcb')->default(false);
            $table->enum('status', ['inactive', 'active', 'suspended'])->default('inactive');
            // Membership
            $table->boolean('dues_paid')->default(false);
            $table->boolean('email_verified')->default(false);
            // Email verification
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@mail.com',
            'role' => 'admin',
            'password' => bcrypt('123456'),
            'proposer_name' => "0",
            'seconder_name' => "0",
            'guardian_name' => "0",
            'date_of_birth' => "2000-01-01",
            'gender' => "male",
            'caste' => "0",
            'cnic' => "0",
            'bar_license_number' => "0",
            'cnic_front_path' => "0",
            'idcard_of_highcourt_path' => "0",
            'license_ofhighcourt_path' => "0",
            'passport_image' => "0",
            'present_address' => "0",
            'permanent_address' => "0",
            'office_address' => "0",
            'date_of_enrollment_as_advocate' => "2000-01-01",
            'district_bar_member' => "0",
            'other_bar_member' => "0",
            'phone' => "0",
            'status' => 'active',
            "email_verified_at" => now(),
            "email_verified"=> true,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Borrowing;
use App\Models\LibraryItem;
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
        // LibraryItem::factory(2)->create();
        // Borrowing::factory(1)->create([
        //     'user_id'=>1,
        //     'library_item_id'=>1,
        //     'status'=>'reserved'
        // ]);

        for ($i = 0; $i < 10; $i++) {
            User::factory()->create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'email_verified' => true,
                'email_verified_at' => now(),
                'role' => 'member',
                'password' => bcrypt('123456'),
                'proposer_name' => '0',
                'seconder_name' => '0',
                'guardian_name' => '0',
                'date_of_birth' => '2000-01-01',
                'gender' => 'male',
                'caste' => '0',
                'cnic' => fake()->unique()->numberBetween(3000000000000, 3999999999999),
                'bar_license_number' => fake()->unique()->numberBetween(100000, 999999),
                'cnic_front_path' => '0',
                'idcard_of_highcourt_path' => '0',
                'license_ofhighcourt_path' => '0',
                'passport_image' => '0',
                'present_address' => '0',
                'permanent_address' => '0',
                'office_address' => '0',
                'date_of_enrollment_as_advocate' => '2000-01-01',
                'district_bar_member' => '0',
                'other_bar_member' => '0',
                'phone' => fake()->unique()->numerify('03#########'),
                'status' => 'active',
            ]);
        }
        // insert query for live database
//         INSERT INTO `users` (
//     `name`,
//     `email`,
//     `email_verified`,
//     `email_verified_at`,
//     `role`,
//     `password`,
//     `proposer_name`,
//     `seconder_name`,
//     `guardian_name`,
//     `date_of_birth`,
//     `gender`,
//     `caste`,
//     `cnic`,
//     `bar_license_number`,
//     `cnic_front_path`,
//     `idcard_of_highcourt_path`,
//     `license_ofhighcourt_path`,
//     `passport_image`,
//     `present_address`,
//     `permanent_address`,
//     `office_address`,
//     `date_of_enrollment_as_advocate`,
//     `district_bar_member`,
//     `other_bar_member`,
//     `phone`,
//     `status`,
//     `created_at`,
//     `updated_at`
// ) VALUES
//     ('Ali Hassan',          'ali.hassan@example.com',          1, NOW(), 'member', '$2y$12$abcdefghijklmnopqrstuvwx.yzABCDEF1234567890', '0', '0', '0', '1995-03-15', 'male', '0', '3520212345671',  '100001', '0', '0', '0', '0', '0', '0', '0', '2020-06-10', '0', '0', '03120010001', 'active', NOW(), NOW()),
//     ('Sara Khan',           'sara.khan@example.com',           1, NOW(), 'member', '$2y$12$abcdefghijklmnopqrstuvwx.yzABCDEF1234567890', '0', '0', '0', '1998-07-22', 'female','0', '3520212345672',  '100002', '0', '0', '0', '0', '0', '0', '0', '2021-01-05', '0', '0', '03120010002', 'active', NOW(), NOW()),
//     ('Muhammad Ahmed',      'ahmed.m@example.com',             1, NOW(), 'member', '$2y$12$abcdefghijklmnopqrstuvwx.yzABCDEF1234567890', '0', '0', '0', '1992-11-30', 'male', '0', '3520212345673',  '100003', '0', '0', '0', '0', '0', '0', '0', '2019-09-18', '0', '0', '03120010003', 'active', NOW(), NOW()),
//     ('Ayesha Fatima',       'ayesha.fatima@example.com',       1, NOW(), 'member', '$2y$12$abcdefghijklmnopqrstuvwx.yzABCDEF1234567890', '0', '0', '0', '2000-04-12', 'female','0', '3520212345674',  '100004', '0', '0', '0', '0', '0', '0', '0', '2022-03-25', '0', '0', '03120010004', 'active', NOW(), NOW()),
//     ('Zain Abbas',          'zain.abbas@example.com',          1, NOW(), 'member', '$2y$12$abcdefghijklmnopqrstuvwx.yzABCDEF1234567890', '0', '0', '0', '1989-09-05', 'male', '0', '3520212345675',  '100005', '0', '0', '0', '0', '0', '0', '0', '2017-11-14', '0', '0', '03120010005', 'active', NOW(), NOW()),
//     ('Hina Riaz',           'hina.riaz@example.com',           1, NOW(), 'member', '$2y$12$abcdefghijklmnopqrstuvwx.yzABCDEF1234567890', '0', '0', '0', '1997-02-28', 'female','0', '3520212345676',  '100006', '0', '0', '0', '0', '0', '0', '0', '2020-08-07', '0', '0', '03120010006', 'active', NOW(), NOW()),
//     ('Omar Farooq',         'omar.farooq@example.com',         1, NOW(), 'member', '$2y$12$abcdefghijklmnopqrstuvwx.yzABCDEF1234567890', '0', '0', '0', '1994-06-19', 'male', '0', '3520212345677',  '100007', '0', '0', '0', '0', '0', '0', '0', '2018-12-03', '0', '0', '03120010007', 'active', NOW(), NOW()),
//     ('Noor ul Ain',         'noor.ain@example.com',            1, NOW(), 'member', '$2y$12$abcdefghijklmnopqrstuvwx.yzABCDEF1234567890', '0', '0', '0', '2001-10-08', 'female','0', '3520212345678',  '100008', '0', '0', '0', '0', '0', '0', '0', '2023-04-16', '0', '0', '03120010008', 'active', NOW(), NOW()),
//     ('Hamza Malik',         'hamza.malik@example.com',         1, NOW(), 'member', '$2y$12$abcdefghijklmnopqrstuvwx.yzABCDEF1234567890', '0', '0', '0', '1990-12-25', 'male', '0', '3520212345679',  '100009', '0', '0', '0', '0', '0', '0', '0', '2016-05-22', '0', '0', '03120010009', 'active', NOW(), NOW()),
//     ('Saba Javed',          'saba.javed@example.com',          1, NOW(), 'member', '$2y$12$abcdefghijklmnopqrstuvwx.yzABCDEF1234567890', '0', '0', '0', '1996-08-14', 'female','0', '3520212345680',  '100010', '0', '0', '0', '0', '0', '0', '0', '2021-10-30', '0', '0', '03120010010', 'active', NOW(), NOW());
    }
    // Enrollment Committee, Inspection Committee ,Disciplinary Committee
}

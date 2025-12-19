<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Tendai Admin',
                'email' => 'tendai.admin@hustlepreneur.co.zw',
                'phone_number' => '+263733431064',
                'role' => 'admin',
            ],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone_number' => '+263771234568', 'role' => 'user'],
            ['name' => 'Michael Johnson', 'email' => 'michael@example.com', 'phone_number' => '+263771234569', 'role' => 'user'],
            ['name' => 'Sarah Williams', 'email' => 'sarah@example.com', 'phone_number' => '+263771234570', 'role' => 'user'],
            ['name' => 'David Brown', 'email' => 'david@example.com', 'phone_number' => '+263771234571', 'role' => 'user'],
            ['name' => 'Emily Davis', 'email' => 'emily@example.com', 'phone_number' => '+263771234572', 'role' => 'user'],
            ['name' => 'James Wilson', 'email' => 'james@example.com', 'phone_number' => '+263771234573', 'role' => 'user'],
            ['name' => 'Lisa Anderson', 'email' => 'lisa@example.com', 'phone_number' => '+263771234574', 'role' => 'user'],
            ['name' => 'Robert Taylor', 'email' => 'robert@example.com', 'phone_number' => '+263771234575', 'role' => 'user'],
            ['name' => 'Jennifer Thomas', 'email' => 'jennifer@example.com', 'phone_number' => '+263771234576', 'role' => 'user'],
            ['name' => 'William Martinez', 'email' => 'william@example.com', 'phone_number' => '+263771234577', 'role' => 'user'],
            ['name' => 'Amanda Garcia', 'email' => 'amanda@example.com', 'phone_number' => '+263771234578', 'role' => 'user'],
            ['name' => 'Christopher Lee', 'email' => 'christopher@example.com', 'phone_number' => '+263771234579', 'role' => 'user'],
            ['name' => 'Michelle Rodriguez', 'email' => 'michelle@example.com', 'phone_number' => '+263771234580', 'role' => 'user'],
            ['name' => 'Daniel Hernandez', 'email' => 'daniel@example.com', 'phone_number' => '+263771234581', 'role' => 'user'],
            ['name' => 'Jessica Lopez', 'email' => 'jessica@example.com', 'phone_number' => '+263771234582', 'role' => 'user'],
            ['name' => 'Matthew Gonzalez', 'email' => 'matthew@example.com', 'phone_number' => '+263771234583', 'role' => 'user'],
            ['name' => 'Ashley Perez', 'email' => 'ashley@example.com', 'phone_number' => '+263771234584', 'role' => 'user'],
            ['name' => 'Joshua Sanchez', 'email' => 'joshua@example.com', 'phone_number' => '+263771234585', 'role' => 'user'],
            ['name' => 'Stephanie Rivera', 'email' => 'stephanie@example.com', 'phone_number' => '+263771234586', 'role' => 'user'],
        ];

        foreach ($users as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone_number' => $userData['phone_number'],
                'role' => $userData['role'],
                'password' => Hash::make('Tendai2025.Admin'),
                'email_verified_at' => now(),
            ]);
        }
    }
}

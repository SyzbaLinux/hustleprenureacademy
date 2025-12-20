<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::create([
            'name' => 'Tendai Admin',
            'email' => 'tendai.admin@hustlepreneur.co.zw',
            'phone_number' => '+263733431064',
            'role' => 'admin',
            'password' => Hash::make('Tendai2025.Admin'),
            'email_verified_at' => now(),
        ]);
    }
}

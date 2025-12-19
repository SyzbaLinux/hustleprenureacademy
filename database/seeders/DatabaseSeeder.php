<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call seeders in order (due to dependencies)
        $this->call([
            UserSeeder::class,         // Seed users first (no dependencies)
            CategorySeeder::class,     // Seed categories (no dependencies)
            InstructorSeeder::class,   // Seed instructors (no dependencies)
            EventSeeder::class,        // Seed events & courses (depends on categories and instructors)
        ]);
    }
}

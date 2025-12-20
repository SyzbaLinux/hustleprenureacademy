<?php

namespace Database\Seeders;

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
            UserSeeder::class,
            CategorySeeder::class,
            InstructorSeeder::class,
            EventSeeder::class,
        ]);
    }
}

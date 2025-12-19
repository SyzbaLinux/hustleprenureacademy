<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Event Categories
            ['name' => 'Business & Entrepreneurship', 'type' => 'event', 'icon' => 'fas fa-briefcase', 'description' => 'Business events, startup workshops, and entrepreneurship seminars', 'display_order' => 1],
            ['name' => 'Technology & Innovation', 'type' => 'event', 'icon' => 'fas fa-laptop-code', 'description' => 'Tech conferences, innovation summits, and digital transformation events', 'display_order' => 2],
            ['name' => 'Marketing & Sales', 'type' => 'event', 'icon' => 'fas fa-bullhorn', 'description' => 'Marketing conferences, sales training events, and brand workshops', 'display_order' => 3],
            ['name' => 'Leadership & Management', 'type' => 'event', 'icon' => 'fas fa-users-cog', 'description' => 'Leadership summits, management workshops, and executive forums', 'display_order' => 4],
            ['name' => 'Finance & Investment', 'type' => 'event', 'icon' => 'fas fa-chart-line', 'description' => 'Financial planning events, investment seminars, and wealth management workshops', 'display_order' => 5],
            ['name' => 'Networking Events', 'type' => 'event', 'icon' => 'fas fa-handshake', 'description' => 'Business networking, meetups, and professional gatherings', 'display_order' => 6],
            ['name' => 'Conferences & Summits', 'type' => 'event', 'icon' => 'fas fa-microphone', 'description' => 'Major conferences, summits, and industry gatherings', 'display_order' => 7],

            // Course Categories
            ['name' => 'Business Management', 'type' => 'course', 'icon' => 'fas fa-building', 'description' => 'Comprehensive business management and strategy courses', 'display_order' => 8],
            ['name' => 'Digital Marketing', 'type' => 'course', 'icon' => 'fas fa-ad', 'description' => 'Digital marketing, social media, and online advertising courses', 'display_order' => 9],
            ['name' => 'Software Development', 'type' => 'course', 'icon' => 'fas fa-code', 'description' => 'Programming, web development, and software engineering courses', 'display_order' => 10],
            ['name' => 'Data Science & Analytics', 'type' => 'course', 'icon' => 'fas fa-database', 'description' => 'Data analysis, machine learning, and business intelligence courses', 'display_order' => 11],
            ['name' => 'Project Management', 'type' => 'course', 'icon' => 'fas fa-tasks', 'description' => 'Project management methodologies, tools, and certifications', 'display_order' => 12],
            ['name' => 'Financial Management', 'type' => 'course', 'icon' => 'fas fa-money-bill-wave', 'description' => 'Accounting, financial planning, and investment courses', 'display_order' => 13],
            ['name' => 'Personal Development', 'type' => 'course', 'icon' => 'fas fa-user-graduate', 'description' => 'Personal growth, productivity, and professional development courses', 'display_order' => 14],
            ['name' => 'Communication Skills', 'type' => 'course', 'icon' => 'fas fa-comments', 'description' => 'Public speaking, presentation skills, and effective communication', 'display_order' => 15],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'type' => $category['type'],
                'icon' => $category['icon'],
                'description' => $category['description'],
                'is_active' => true,
                'display_order' => $category['display_order'],
            ]);
        }
    }
}

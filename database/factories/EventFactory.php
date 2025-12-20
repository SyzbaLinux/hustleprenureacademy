<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'slug' => $this->faker->slug(),
            'category_id' => \App\Models\Category::factory(),
            'type' => $this->faker->randomElement(['event', 'course']),
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(),
            'location' => $this->faker->city(),
            'location_type' => $this->faker->randomElement(['physical', 'online', 'hybrid']),
            'meeting_link' => $this->faker->url(),
            'capacity' => $this->faker->numberBetween(10, 100),
            'amount' => $this->faker->numberBetween(10, 100),
            'currency' => 'USD',
            'duration_hours' => $this->faker->numberBetween(1, 8),
            'is_active' => true,
            'is_featured' => false,
            'published_at' => now(),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}

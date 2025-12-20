<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'type' => $this->faker->randomElement(['event', 'course']),
            'description' => $this->faker->sentence(),
            'icon' => 'fas fa-folder',
            'is_active' => true,
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}

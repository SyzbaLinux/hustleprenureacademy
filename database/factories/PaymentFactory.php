<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => \App\Models\Event::factory(),
            'phone_number' => $this->faker->phoneNumber(),
            'amount' => $this->faker->numberBetween(10, 100),
            'currency' => 'USD',
            'payment_method' => $this->faker->randomElement(['ecocash', 'onemoney', 'telecash']),
            'reference_number' => $this->faker->uuid(),
            'status' => 'pending',
            'pesepay_response' => [],
        ];
    }
}

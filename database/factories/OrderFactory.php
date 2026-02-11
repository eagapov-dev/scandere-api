<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total' => fake()->randomFloat(2, 10, 500),
            'status' => fake()->randomElement(['pending', 'completed']),
            'payment_gateway' => 'stripe',
            'payment_id' => fake()->optional()->uuid(),
            'paid_at' => function (array $attributes) {
                return $attributes['status'] === 'completed' ? now() : null;
            },
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_id' => 'pi_' . fake()->uuid(),
            'paid_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_id' => null,
            'paid_at' => null,
        ]);
    }
}

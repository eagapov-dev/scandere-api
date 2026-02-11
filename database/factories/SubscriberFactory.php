<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscriber>
 */
class SubscriberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'source' => fake()->randomElement(['newsletter', 'contact_form', 'footer']),
            'ip_address' => fake()->ipv4(),
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ];
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'unsubscribed_at' => now(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'unsubscribed_at' => null,
        ]);
    }
}

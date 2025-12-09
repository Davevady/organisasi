<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_code' => 'MBR-' . fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'status' => fake()->randomElement(['active', 'inactive', 'suspended']),
            'join_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'exit_date' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

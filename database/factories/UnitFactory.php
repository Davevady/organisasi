<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $units = [
            ['name' => 'Pieces', 'symbol' => 'pcs'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Liter', 'symbol' => 'L'],
            ['name' => 'Dozen', 'symbol' => 'dzn'],
        ];

        $unit = fake()->randomElement($units);

        return [
            'name' => $unit['name'],
            'symbol' => $unit['symbol'] . '-' . fake()->unique()->numberBetween(1, 999),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}

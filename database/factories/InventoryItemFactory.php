<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'BRG-' . fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category_id' => \App\Models\Category::factory(),
            'unit_id' => \App\Models\Unit::factory(),
            'current_stock' => fake()->numberBetween(0, 100),
            'minimum_stock' => fake()->numberBetween(5, 20),
            'purchase_price' => fake()->numberBetween(10000, 1000000),
            'selling_price' => fake()->numberBetween(15000, 1500000),
            'location' => fake()->randomElement(['Gudang A', 'Gudang B', 'Ruang Utama']),
            'status' => fake()->randomElement(['available', 'unavailable', 'discontinued']),
            'image' => null,
        ];
    }
}

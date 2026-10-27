<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Transportasi', 'Listrik', 'Makanan', 'Sampah']).' '.fake()->unique()->numberBetween(1, 9999),
            'icon' => null,
            'color' => fake()->hexColor(),
            'base_unit' => 'km',
            'is_active' => true,
        ];
    }
}

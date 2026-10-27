<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\EmissionFactor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmissionFactor>
 */
class EmissionFactorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(2, true),
            'unit' => 'km',
            'factor_value' => 1,
            'reference_url' => null,
            'is_active' => true,
        ];
    }
}

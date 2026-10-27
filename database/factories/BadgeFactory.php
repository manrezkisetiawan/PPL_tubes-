<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Badge>
 */
class BadgeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Lencana '.fake()->unique()->word(),
            'description' => fake()->sentence(),
            'icon' => null,
            'type' => 'activities_logged',
            'required_value' => 1,
            'is_active' => true,
        ];
    }
}

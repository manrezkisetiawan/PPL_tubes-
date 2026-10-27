<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Challenge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Challenge>
 */
class ChallengeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'title' => 'Tantangan '.fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'start_date' => today(),
            'end_date' => today()->addDays(6),
            'reduction_target' => 5,
            'reward_points' => 100,
        ];
    }

    public function upcoming(): static
    {
        return $this->state(fn () => [
            'start_date' => today()->addDays(3),
            'end_date' => today()->addDays(9),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn () => [
            'start_date' => today()->subDays(10),
            'end_date' => today()->subDays(4),
        ]);
    }
}

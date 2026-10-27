<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\EmissionFactor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'emission_factor_id' => EmissionFactor::factory(),
            'amount' => 1,
            'emission' => 1,
            'activity_date' => today(),
            'note' => null,
        ];
    }
}

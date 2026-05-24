<?php

namespace Database\Factories;

use App\Models\Tier;
use Illuminate\Database\Eloquent\Factories\Factory;

class TierFactory extends Factory
{
    protected $model = Tier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'min_balance' => fake()->numberBetween(0, 100000),
            'icon' => fake()->word(),
            'color' => fake()->hexColor(),
            'order_index' => fake()->unique()->numberBetween(0, 10),
        ];
    }
}

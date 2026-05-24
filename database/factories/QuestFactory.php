<?php

namespace Database\Factories;

use App\Models\Quest;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestFactory extends Factory
{
    protected $model = Quest::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'xp_reward' => fake()->numberBetween(10, 100),
            'type' => fake()->randomElement(['deposit_count', 'savings_milestone', 'streak']),
            'criteria' => ['count' => fake()->numberBetween(3, 10)],
            'active' => true,
        ];
    }
}

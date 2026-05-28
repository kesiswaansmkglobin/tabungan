<?php

namespace Database\Factories;

use App\Models\Quest;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestFactory extends Factory
{
    protected $model = Quest::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['deposit_count', 'savings_milestone', 'streak']);

        $criteria = match ($type) {
            'savings_milestone' => ['amount' => fake()->numberBetween(50000, 500000)],
            'streak' => ['days' => fake()->numberBetween(3, 7)],
            default => ['count' => fake()->numberBetween(3, 10)],
        };

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'xp_reward' => fake()->numberBetween(10, 100),
            'type' => $type,
            'criteria' => $criteria,
            'active' => true,
        ];
    }
}

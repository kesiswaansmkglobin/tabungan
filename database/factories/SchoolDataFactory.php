<?php

namespace Database\Factories;

use App\Models\SchoolData;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolDataFactory extends Factory
{
    protected $model = SchoolData::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' '.fake()->randomElement(['SD', 'SMP', 'SMA', 'SMK']),
            'headmaster_name' => fake()->name(),
            'treasurer_name' => fake()->name(),
        ];
    }
}

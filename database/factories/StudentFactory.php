<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'nis' => fake()->unique()->numerify('##########'),
            'name' => fake()->name(),
            'class_id' => ClassRoom::factory(),
            'balance' => 0,
        ];
    }
}

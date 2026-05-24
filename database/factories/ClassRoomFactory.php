<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassRoomFactory extends Factory
{
    protected $model = ClassRoom::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' '.fake()->randomElement(['A', 'B', 'C']),
            'wali_kelas_id' => null,
        ];
    }
}

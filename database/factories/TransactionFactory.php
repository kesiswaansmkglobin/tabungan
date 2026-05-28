<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'type' => 'setor',
            'amount' => fake()->numberBetween(1000, 100000),
            'balance_after' => fn (array $attrs) => $attrs['type'] === 'setor' ? $attrs['amount'] : -$attrs['amount'],
            'transaction_date' => fake()->date(),
            'note' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function setor(): static
    {
        return $this->state(fn (array $attrs) => ['type' => 'setor']);
    }

    public function tarik(): static
    {
        return $this->state(fn () => [
            'type' => 'tarik',
            'balance_after' => -$this->attributes['amount'] ?? fake()->numberBetween(1000, 100000),
        ]);
    }
}

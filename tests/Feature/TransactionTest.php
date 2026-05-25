<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_index_displays_transactions(): void
    {
        Transaction::factory()->count(3)->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('transactions.index'));

        $response->assertOk();
    }

    public function test_store_deposit_creates_transaction(): void
    {
        $student = Student::factory()->create(['balance' => 0]);
        $mock = $this->createMock(TransactionService::class);
        $mock->expects($this->once())
            ->method('createTransaction')
            ->with('setor', $this->arrayHasKey('student_id'))
            ->willReturn(Transaction::factory()->make([
                'student_id' => $student->id,
                'type' => 'setor',
                'amount' => 50000,
            ]));

        $this->instance(TransactionService::class, $mock);

        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'student_id' => $student->id,
            'type' => 'setor',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_store_withdrawal_creates_transaction(): void
    {
        $student = Student::factory()->create(['balance' => 100000]);
        $mock = $this->createMock(TransactionService::class);
        $mock->expects($this->once())
            ->method('createTransaction')
            ->with('tarik', $this->arrayHasKey('student_id'))
            ->willReturn(Transaction::factory()->make([
                'student_id' => $student->id,
                'type' => 'tarik',
                'amount' => 30000,
            ]));

        $this->instance(TransactionService::class, $mock);

        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'student_id' => $student->id,
            'type' => 'tarik',
            'amount' => 30000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'student_id' => '',
            'type' => '',
            'amount' => '',
            'transaction_date' => '',
        ]);

        $response->assertSessionHasErrors(['student_id', 'type', 'amount', 'transaction_date']);
    }

    public function test_store_validates_invalid_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'student_id' => 1,
            'type' => 'invalid',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_store_validates_min_amount(): void
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('transactions.store'), [
            'student_id' => $student->id,
            'type' => 'setor',
            'amount' => 0,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_update_uses_service(): void
    {
        $transaction = Transaction::factory()->create(['created_by' => $this->admin->id]);
        $mock = $this->createMock(TransactionService::class);
        $mock->expects($this->once())
            ->method('updateTransaction')
            ->willReturn($transaction);

        $this->instance(TransactionService::class, $mock);

        $response = $this->actingAs($this->admin)->patch(route('transactions.update', $transaction), [
            'amount' => 75000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_destroy_uses_service(): void
    {
        $transaction = Transaction::factory()->create(['created_by' => $this->admin->id]);
        $mock = $this->createMock(TransactionService::class);
        $mock->expects($this->once())
            ->method('deleteTransaction');

        $this->instance(TransactionService::class, $mock);

        $response = $this->actingAs($this->admin)->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_get_student_returns_json(): void
    {
        $class = ClassRoom::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);

        $response = $this->actingAs($this->admin)->get(route('transactions.student', $student));

        $response->assertOk();
        $response->assertJson([
            'id' => $student->id,
            'nis' => $student->nis,
            'name' => $student->name,
        ]);
    }
}

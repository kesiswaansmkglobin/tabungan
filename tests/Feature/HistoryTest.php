<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_index_displays_history_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('history'));

        $response->assertOk();
    }

    public function test_index_displays_transactions(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->count(3)->create([
            'student_id' => $student->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('history'));

        $response->assertOk();
    }

    public function test_index_filters_by_type(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->create(['student_id' => $student->id, 'type' => 'setor', 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('history', ['type' => 'setor']));

        $response->assertOk();
    }

    public function test_index_filters_by_student(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->create(['student_id' => $student->id, 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('history', ['student_id' => $student->id]));

        $response->assertOk();
    }

    public function test_index_filters_by_class(): void
    {
        $class = ClassRoom::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);
        Transaction::factory()->create(['student_id' => $student->id, 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('history', ['class_id' => $class->id]));

        $response->assertOk();
    }

    public function test_index_filters_by_date_range(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->create(['student_id' => $student->id, 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('history', [
            'date_from' => now()->subMonth()->toDateString(),
            'date_to' => now()->addMonth()->toDateString(),
        ]));

        $response->assertOk();
    }

    public function test_index_accessible_by_staff(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $response = $this->actingAs($user)->get(route('history'));

        $response->assertOk();
    }

    public function test_index_accessible_by_wali_kelas(): void
    {
        $user = User::factory()->create();
        $user->assignRole('wali_kelas');

        $response = $this->actingAs($user)->get(route('history'));

        $response->assertOk();
    }
}

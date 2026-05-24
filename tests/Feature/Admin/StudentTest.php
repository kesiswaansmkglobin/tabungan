<?php

namespace Tests\Feature\Admin;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_index_displays_students(): void
    {
        Student::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.students'));

        $response->assertOk();
    }

    public function test_store_creates_student(): void
    {
        $class = ClassRoom::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), [
            'nis' => '1234567890',
            'name' => 'John Doe',
            'class_id' => $class->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', ['nis' => '1234567890', 'name' => 'John Doe']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), [
            'nis' => '',
            'name' => '',
            'class_id' => '',
        ]);

        $response->assertSessionHasErrors(['nis', 'name', 'class_id']);
    }

    public function test_store_validates_unique_nis(): void
    {
        $class = ClassRoom::factory()->create();
        Student::factory()->create(['nis' => '1234567890']);

        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), [
            'nis' => '1234567890',
            'name' => 'John Doe',
            'class_id' => $class->id,
        ]);

        $response->assertSessionHasErrors('nis');
    }

    public function test_update_updates_student(): void
    {
        $student = Student::factory()->create(['name' => 'John Doe']);

        $response = $this->actingAs($this->admin)->patch(route('admin.students.update', $student), [
            'nis' => $student->nis,
            'name' => 'Jane Doe',
            'class_id' => $student->class_id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', ['name' => 'Jane Doe']);
    }

    public function test_destroy_deletes_student(): void
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.students.destroy', $student));

        $response->assertRedirect();
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassRoomTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_index_displays_classes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.classes'));

        $response->assertOk();
    }

    public function test_store_creates_class(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.classes.store'), [
            'name' => 'XII RPL A',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', ['name' => 'XII RPL A']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.classes.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_validates_unique_name(): void
    {
        ClassRoom::factory()->create(['name' => 'XII RPL A']);

        $response = $this->actingAs($this->admin)->post(route('admin.classes.store'), [
            'name' => 'XII RPL A',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_updates_class(): void
    {
        $class = ClassRoom::factory()->create(['name' => 'XII RPL A']);

        $response = $this->actingAs($this->admin)->patch(route('admin.classes.update', $class), [
            'name' => 'XII RPL B',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', ['name' => 'XII RPL B']);
    }

    public function test_destroy_deletes_class(): void
    {
        $class = ClassRoom::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.classes.destroy', $class));

        $response->assertRedirect();
        $this->assertDatabaseMissing('classes', ['id' => $class->id]);
    }

    public function test_destroy_fails_when_class_has_students(): void
    {
        $class = ClassRoom::factory()->hasStudents(1)->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.classes.destroy', $class));

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', ['id' => $class->id]);
    }
}

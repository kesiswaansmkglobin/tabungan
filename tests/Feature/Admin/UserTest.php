<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_index_displays_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users'));

        $response->assertOk();
    }

    public function test_store_creates_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Staff Baru',
            'email' => 'staff@example.com',
            'password' => 'password123',
            'role' => 'staff',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'staff@example.com']);
    }

    public function test_store_assigns_role(): void
    {
        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Staff Baru',
            'email' => 'staff@example.com',
            'password' => 'password123',
            'role' => 'staff',
        ]);

        $user = User::where('email', 'staff@example.com')->first();
        $this->assertTrue($user->hasRole('staff'));
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => '',
            'email' => '',
            'password' => '',
            'role' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_store_validates_unique_email(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Duplicate',
            'email' => $this->admin->email,
            'password' => 'password123',
            'role' => 'staff',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_update_updates_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $response = $this->actingAs($this->admin)->patch(route('admin.users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'staff',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    }

    public function test_update_changes_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $this->actingAs($this->admin)->patch(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'admin',
        ]);

        $this->assertTrue($user->fresh()->hasRole('admin'));
        $this->assertFalse($user->fresh()->hasRole('staff'));
    }

    public function test_destroy_deletes_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_destroy_fails_for_self(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}

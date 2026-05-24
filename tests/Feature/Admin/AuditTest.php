<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_index_displays_audit_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit'));

        $response->assertOk();
    }

    public function test_index_displays_logs(): void
    {
        Activity::create([
            'log_name' => 'default',
            'description' => 'Created transaction',
            'event' => 'created',
            'causer_id' => $this->admin->id,
            'causer_type' => User::class,
            'subject_id' => 1,
            'subject_type' => 'App\Models\Transaction',
            'properties' => ['amount' => 50000],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit'));

        $response->assertOk();
        $response->assertSee('Created transaction');
    }

    public function test_index_filters_by_log_name(): void
    {
        Activity::create(['log_name' => 'auth', 'description' => 'User logged in', 'causer_id' => $this->admin->id, 'causer_type' => User::class, 'subject_id' => 1, 'subject_type' => User::class]);
        Activity::create(['log_name' => 'default', 'description' => 'Created transaction', 'causer_id' => $this->admin->id, 'causer_type' => User::class, 'subject_id' => 1, 'subject_type' => 'App\Models\Transaction']);

        $response = $this->actingAs($this->admin)->get(route('admin.audit', ['log_name' => 'auth']));

        $response->assertOk();
    }

    public function test_index_filters_by_event(): void
    {
        Activity::create(['log_name' => 'default', 'description' => 'Created', 'event' => 'created', 'causer_id' => $this->admin->id, 'causer_type' => User::class, 'subject_id' => 1, 'subject_type' => User::class]);
        Activity::create(['log_name' => 'default', 'description' => 'Updated', 'event' => 'updated', 'causer_id' => $this->admin->id, 'causer_type' => User::class, 'subject_id' => 1, 'subject_type' => User::class]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit', ['event' => 'created']));

        $response->assertOk();
    }

    public function test_index_filters_by_date_range(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]));

        $response->assertOk();
    }

    public function test_index_filters_by_description(): void
    {
        Activity::create(['log_name' => 'default', 'description' => 'User logged in successfully', 'causer_id' => $this->admin->id, 'causer_type' => User::class, 'subject_id' => 1, 'subject_type' => User::class]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit', ['description' => 'logged']));

        $response->assertOk();
    }
}

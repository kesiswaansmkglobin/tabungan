<?php

namespace Tests\Feature\Admin;

use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TierTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_index_displays_gamification_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.gamification'));

        $response->assertOk();
    }

    public function test_store_creates_tier(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tiers.store'), [
            'name' => 'Silver',
            'min_balance' => 50000,
            'order_index' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tiers', ['name' => 'Silver', 'min_balance' => 50000]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tiers.store'), [
            'name' => '',
            'min_balance' => '',
            'order_index' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'min_balance', 'order_index']);
    }

    public function test_update_updates_tier(): void
    {
        $tier = Tier::factory()->create(['name' => 'Bronze', 'min_balance' => 0]);

        $response = $this->actingAs($this->admin)->patch(route('admin.tiers.update', $tier), [
            'name' => 'Silver',
            'min_balance' => 50000,
            'order_index' => $tier->order_index,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tiers', ['name' => 'Silver']);
    }

    public function test_destroy_deletes_tier(): void
    {
        $tier = Tier::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.tiers.destroy', $tier));

        $response->assertRedirect();
        $this->assertDatabaseMissing('tiers', ['id' => $tier->id]);
    }
}

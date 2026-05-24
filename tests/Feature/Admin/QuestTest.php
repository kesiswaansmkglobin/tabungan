<?php

namespace Tests\Feature\Admin;

use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestTest extends TestCase
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

    public function test_store_creates_quest(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.quests.store'), [
            'title' => 'Menabung 5 Kali',
            'xp_reward' => 50,
            'type' => 'deposit_count',
            'criteria' => ['count' => 5],
            'active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quests', ['title' => 'Menabung 5 Kali', 'xp_reward' => 50]);
    }

    public function test_store_accepts_json_string_criteria(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.quests.store'), [
            'title' => 'Menabung 10 Kali',
            'xp_reward' => 100,
            'type' => 'deposit_count',
            'criteria' => '{"count":10}',
            'active' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quests', ['title' => 'Menabung 10 Kali']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.quests.store'), [
            'title' => '',
            'xp_reward' => '',
            'type' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'xp_reward', 'type']);
    }

    public function test_update_updates_quest(): void
    {
        $quest = Quest::factory()->create(['title' => 'Quest Lama']);

        $response = $this->actingAs($this->admin)->patch(route('admin.quests.update', $quest), [
            'title' => 'Quest Baru',
            'xp_reward' => 75,
            'type' => $quest->type,
            'active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quests', ['title' => 'Quest Baru']);
    }

    public function test_destroy_deletes_quest(): void
    {
        $quest = Quest::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.quests.destroy', $quest));

        $response->assertRedirect();
        $this->assertDatabaseMissing('quests', ['id' => $quest->id]);
    }
}

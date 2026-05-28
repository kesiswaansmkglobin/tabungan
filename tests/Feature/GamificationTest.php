<?php

namespace Tests\Feature;

use App\Models\Quest;
use App\Models\Student;
use App\Models\StudentQuestCompletion;
use App\Models\Tier;
use App\Models\Transaction;
use App\Services\GamificationService;
use App\Services\QuestEvaluators\BalanceMilestoneEvaluator;
use App\Services\QuestEvaluators\LoginEvaluator;
use App\Services\QuestEvaluators\StreakEvaluator;
use App\Services\QuestEvaluators\TransactionCountEvaluator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GamificationTest extends TestCase
{
    use DatabaseMigrations;

    // ─── LoginEvaluator ─────────────────────────────────────────────────

    public function test_login_evaluator_returns_true_when_student_has_logged_in(): void
    {
        $student = Student::factory()->create();
        $student->progress->update(['last_login_at' => now()]);

        $quest = Quest::factory()->create(['type' => 'login', 'criteria' => []]);
        $evaluator = new LoginEvaluator;

        $this->assertTrue($evaluator->evaluate($student, $quest, []));
    }

    public function test_login_evaluator_returns_false_when_student_has_no_progress(): void
    {
        $student = Student::factory()->create();
        $student->progress()->delete();
        $student = $student->fresh();

        $quest = Quest::factory()->create(['type' => 'login', 'criteria' => []]);
        $evaluator = new LoginEvaluator;

        $this->assertFalse($evaluator->evaluate($student, $quest, []));
    }

    // ─── BalanceMilestoneEvaluator ──────────────────────────────────────

    public function test_balance_milestone_returns_true_when_balance_meets_target(): void
    {
        $student = Student::factory()->create(['balance' => 100000]);
        $quest = Quest::factory()->create([
            'type' => 'savings_milestone',
            'criteria' => ['amount' => 50000],
        ]);
        $evaluator = new BalanceMilestoneEvaluator;

        $this->assertTrue($evaluator->evaluate($student, $quest, []));
    }

    public function test_balance_milestone_returns_false_when_balance_below_target(): void
    {
        $student = Student::factory()->create(['balance' => 10000]);
        $quest = Quest::factory()->create([
            'type' => 'savings_milestone',
            'criteria' => ['amount' => 50000],
        ]);
        $evaluator = new BalanceMilestoneEvaluator;

        $this->assertFalse($evaluator->evaluate($student, $quest, []));
    }

    // ─── TransactionCountEvaluator ──────────────────────────────────────

    public function test_transaction_count_returns_true_when_count_met(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->count(5)->create([
            'student_id' => $student->id,
            'type' => 'setor',
            'transaction_date' => now()->subDay(),
        ]);

        $quest = Quest::factory()->create([
            'type' => 'deposit_count',
            'criteria' => ['count' => 5],
        ]);
        $evaluator = new TransactionCountEvaluator;

        $this->assertTrue($evaluator->evaluate($student, $quest, []));
    }

    public function test_transaction_count_returns_false_when_count_not_met(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->count(2)->create([
            'student_id' => $student->id,
            'type' => 'setor',
            'transaction_date' => now()->subDay(),
        ]);

        $quest = Quest::factory()->create([
            'type' => 'deposit_count',
            'criteria' => ['count' => 5],
        ]);
        $evaluator = new TransactionCountEvaluator;

        $this->assertFalse($evaluator->evaluate($student, $quest, []));
    }

    public function test_transaction_count_filters_by_type(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->count(5)->create([
            'student_id' => $student->id,
            'type' => 'setor',
            'transaction_date' => now()->subDay(),
        ]);
        Transaction::factory()->count(3)->create([
            'student_id' => $student->id,
            'type' => 'tarik',
            'transaction_date' => now()->subDay(),
        ]);

        $quest = Quest::factory()->create([
            'type' => 'withdrawal',
            'criteria' => ['count' => 3],
        ]);
        $evaluator = new TransactionCountEvaluator;

        $this->assertTrue($evaluator->evaluate($student, $quest, []));
    }

    public function test_transaction_count_respects_period(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->count(5)->create([
            'student_id' => $student->id,
            'type' => 'setor',
            'transaction_date' => now()->subDays(60),
        ]);

        $quest = Quest::factory()->create([
            'type' => 'deposit_count',
            'criteria' => ['count' => 5, 'period_days' => 30],
        ]);
        $evaluator = new TransactionCountEvaluator;

        $this->assertFalse($evaluator->evaluate($student, $quest, []));
    }

    public function test_transaction_count_counts_all_types_when_no_type_filter(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->count(3)->create([
            'student_id' => $student->id,
            'type' => 'setor',
            'transaction_date' => now()->subDay(),
        ]);
        Transaction::factory()->count(2)->create([
            'student_id' => $student->id,
            'type' => 'tarik',
            'transaction_date' => now()->subDay(),
        ]);

        $quest = Quest::factory()->create([
            'type' => 'transaction',
            'criteria' => ['count' => 5],
        ]);
        $evaluator = new TransactionCountEvaluator;

        $this->assertTrue($evaluator->evaluate($student, $quest, []));
    }

    // ─── StreakEvaluator ────────────────────────────────────────────────

    public function test_streak_evaluator_returns_true_for_consecutive_days(): void
    {
        $student = Student::factory()->create();
        foreach (range(0, 2) as $i) {
            Transaction::factory()->create([
                'student_id' => $student->id,
                'type' => 'setor',
                'transaction_date' => now()->subDays(2 - $i),
            ]);
        }

        $quest = Quest::factory()->create([
            'type' => 'streak',
            'criteria' => ['days' => 3],
        ]);
        $evaluator = new StreakEvaluator;

        $this->assertTrue($evaluator->evaluate($student, $quest, []));
    }

    public function test_streak_evaluator_returns_false_for_non_consecutive_days(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->create([
            'student_id' => $student->id,
            'type' => 'setor',
            'transaction_date' => now()->subDays(3),
        ]);
        Transaction::factory()->create([
            'student_id' => $student->id,
            'type' => 'setor',
            'transaction_date' => now()->subDay(),
        ]);

        $quest = Quest::factory()->create([
            'type' => 'streak',
            'criteria' => ['days' => 3],
        ]);
        $evaluator = new StreakEvaluator;

        $this->assertFalse($evaluator->evaluate($student, $quest, []));
    }

    public function test_streak_evaluator_returns_false_when_less_than_target(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->create([
            'student_id' => $student->id,
            'type' => 'setor',
            'transaction_date' => now()->subDay(),
        ]);

        $quest = Quest::factory()->create([
            'type' => 'streak',
            'criteria' => ['days' => 3],
        ]);
        $evaluator = new StreakEvaluator;

        $this->assertFalse($evaluator->evaluate($student, $quest, []));
    }

    // ─── GamificationService: ensureProgress ────────────────────────────

    public function test_ensure_progress_creates_progress_when_not_exists(): void
    {
        $student = Student::factory()->create();
        $student->progress()->delete();
        $student = $student->fresh();

        $service = app(GamificationService::class);
        $progress = $service->ensureProgress($student);

        $this->assertDatabaseHas('student_progress', ['student_id' => $student->id, 'xp' => 0]);
        $this->assertEquals(0, $progress->xp);
    }

    public function test_ensure_progress_returns_existing_progress(): void
    {
        $student = Student::factory()->create();
        $student->progress->update(['xp' => 50]);

        $service = app(GamificationService::class);
        $progress = $service->ensureProgress($student);

        $this->assertEquals(50, $progress->xp);
    }

    // ─── GamificationService: addXp ─────────────────────────────────────

    public function test_add_xp_increments_xp(): void
    {
        $student = Student::factory()->create();
        $service = app(GamificationService::class);

        $service->addXp($student, 10);
        $service->addXp($student, 20);

        $this->assertEquals(30, $student->progress->fresh()->xp);
    }

    // ─── GamificationService: syncTier ──────────────────────────────────

    public function test_sync_tier_assigns_highest_tier_based_on_balance(): void
    {
        Tier::factory()->create(['min_balance' => 0, 'order_index' => 1]);
        $silver = Tier::factory()->create(['min_balance' => 50000, 'order_index' => 2]);
        Tier::factory()->create(['min_balance' => 200000, 'order_index' => 3]);

        $student = Student::factory()->create(['balance' => 100000]);
        $service = app(GamificationService::class);

        $service->syncTier($student);

        $this->assertEquals($silver->id, $student->progress->fresh()->tier_id);
    }

    public function test_sync_tier_assigns_lowest_when_no_match(): void
    {
        $bronze = Tier::factory()->create(['min_balance' => 0, 'order_index' => 1]);

        $student = Student::factory()->create(['balance' => 0]);
        $service = app(GamificationService::class);

        $service->syncTier($student);

        $this->assertEquals($bronze->id, $student->progress->fresh()->tier_id);
    }

    public function test_sync_tier_creates_progress_when_missing(): void
    {
        $tier = Tier::factory()->create(['min_balance' => 0]);
        $student = Student::factory()->create(['balance' => 50000]);
        $student->progress()->delete();
        $student = $student->fresh();
        $this->assertNull($student->progress);

        $service = app(GamificationService::class);
        $service->syncTier($student);

        $student->load('progress');
        $this->assertNotNull($student->progress);
        $this->assertEquals($tier->id, $student->progress->tier_id);
    }

    // ─── GamificationService: checkQuests ───────────────────────────────

    public function test_check_quests_creates_completion_when_quest_met(): void
    {
        $student = Student::factory()->create(['balance' => 100000]);
        $quest = Quest::factory()->create([
            'type' => 'savings_milestone',
            'criteria' => ['amount' => 50000],
            'active' => true,
        ]);

        $service = app(GamificationService::class);
        $service->checkQuests($student, 'savings_milestone');

        $this->assertDatabaseHas('student_quest_completions', [
            'student_id' => $student->id,
            'quest_id' => $quest->id,
        ]);
    }

    public function test_check_quests_skips_already_completed(): void
    {
        $student = Student::factory()->create(['balance' => 100000]);
        $quest = Quest::factory()->create([
            'type' => 'savings_milestone',
            'criteria' => ['amount' => 50000],
            'active' => true,
        ]);
        StudentQuestCompletion::create([
            'student_id' => $student->id,
            'quest_id' => $quest->id,
            'completed_at' => now(),
        ]);

        $service = app(GamificationService::class);
        $newCompletions = $service->checkQuests($student, 'savings_milestone');

        $this->assertEmpty($newCompletions);
    }

    public function test_check_quests_awards_xp_on_completion(): void
    {
        $student = Student::factory()->create(['balance' => 100000]);
        Quest::factory()->create([
            'type' => 'savings_milestone',
            'criteria' => ['amount' => 50000],
            'xp_reward' => 50,
            'active' => true,
        ]);

        $service = app(GamificationService::class);
        $service->checkQuests($student, 'savings_milestone');

        $this->assertEquals(50, $student->progress->fresh()->xp);
    }

    public function test_check_quests_returns_newly_completed_quests(): void
    {
        $student = Student::factory()->create(['balance' => 100000]);
        $quest = Quest::factory()->create([
            'type' => 'savings_milestone',
            'criteria' => ['amount' => 50000],
            'xp_reward' => 50,
            'active' => true,
        ]);

        $service = app(GamificationService::class);
        $newCompletions = $service->checkQuests($student, 'savings_milestone');

        $this->assertCount(1, $newCompletions);
        $this->assertEquals($quest->id, $newCompletions[0]['id']);
        $this->assertEquals($quest->title, $newCompletions[0]['title']);
        $this->assertEquals(50, $newCompletions[0]['xp_reward']);
    }

    public function test_check_quests_returns_empty_for_inactive_quests(): void
    {
        $student = Student::factory()->create(['balance' => 100000]);
        Quest::factory()->create([
            'type' => 'savings_milestone',
            'criteria' => ['amount' => 50000],
            'active' => false,
        ]);

        $service = app(GamificationService::class);
        $newCompletions = $service->checkQuests($student, 'savings_milestone');

        $this->assertEmpty($newCompletions);
    }

    public function test_check_quests_handles_deposit_event_with_streak_quests(): void
    {
        $student = Student::factory()->create(['balance' => 100000]);

        foreach (range(0, 2) as $i) {
            Transaction::factory()->create([
                'student_id' => $student->id,
                'type' => 'setor',
                'transaction_date' => now()->subDays(2 - $i),
            ]);
        }

        Quest::factory()->create([
            'title' => 'Konsisten Menabung',
            'type' => 'deposit',
            'criteria' => ['count' => 3, 'consecutive_days' => true],
            'active' => true,
        ]);

        $service = app(GamificationService::class);
        $newCompletions = $service->checkQuests($student, 'deposit');

        $this->assertCount(1, $newCompletions);
    }
}

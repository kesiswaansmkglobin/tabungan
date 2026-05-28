<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Models\StudentQuestCompletion;
use App\Models\Tier;
use App\Services\QuestEvaluators\BalanceMilestoneEvaluator;
use App\Services\QuestEvaluators\LoginEvaluator;
use App\Services\QuestEvaluators\StreakEvaluator;
use App\Services\QuestEvaluators\TransactionCountEvaluator;

class GamificationService
{
    private array $evaluators = [];

    public function __construct()
    {
        $this->evaluators = [
            'savings_milestone' => new BalanceMilestoneEvaluator,
            'balance' => new BalanceMilestoneEvaluator,
            'milestone' => new BalanceMilestoneEvaluator,
            'deposit' => new TransactionCountEvaluator,
            'withdrawal' => new TransactionCountEvaluator,
            'transaction' => new TransactionCountEvaluator,
            'deposit_count' => new TransactionCountEvaluator,
            'streak' => new StreakEvaluator,
            'login' => new LoginEvaluator,
        ];
    }

    public function syncTier(Student $student): void
    {
        $tier = Tier::where('min_balance', '<=', $student->balance)
            ->orderBy('min_balance', 'desc')
            ->first();

        $progress = $student->progress;

        if (! $progress) {
            return;
        }

        if ($tier && $progress->tier_id !== $tier->id) {
            $progress->update(['tier_id' => $tier->id]);
        }
    }

    public function addXp(Student $student, int $xp): void
    {
        $progress = $this->ensureProgress($student);
        $progress->increment('xp', $xp);
    }

    public function checkQuests(Student $student, string $event, array $context = []): array
    {
        $quests = Quest::where('active', true)
            ->where(function ($q) use ($event) {
                $q->where('type', $event)
                    ->when(in_array($event, ['deposit', 'withdrawal']), fn ($q) => $q->orWhereIn('type', [$event.'_count', 'streak']));
            })
            ->get();

        if ($quests->isEmpty()) {
            return [];
        }

        $completedIds = StudentQuestCompletion::where('student_id', $student->id)
            ->whereIn('quest_id', $quests->pluck('id'))
            ->pluck('quest_id')
            ->toArray();

        $context['transaction_count'] = $student->transactions()->count();

        $newCompletions = [];

        foreach ($quests as $quest) {
            if (in_array($quest->id, $completedIds)) {
                continue;
            }

            if ($this->evaluate($student, $quest, $context)) {
                StudentQuestCompletion::create([
                    'student_id' => $student->id,
                    'quest_id' => $quest->id,
                    'completed_at' => now(),
                ]);

                $this->addXp($student, $quest->xp_reward);

                $newCompletions[] = [
                    'id' => $quest->id,
                    'title' => $quest->title,
                    'xp_reward' => $quest->xp_reward,
                ];
            }
        }

        return $newCompletions;
    }

    private function evaluate(Student $student, Quest $quest, array $context): bool
    {
        $criteria = $quest->criteria;

        if (empty($criteria)) {
            return true;
        }

        $evaluator = $this->evaluators[$quest->type] ?? null;

        if (! $evaluator) {
            return false;
        }

        return $evaluator->evaluate($student, $quest, $context);
    }

    public function ensureProgress(Student $student): StudentProgress
    {
        return $student->progress ?? StudentProgress::create([
            'student_id' => $student->id,
            'xp' => 0,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Models\StudentQuestCompletion;
use App\Models\Tier;
use Carbon\Carbon;

class GamificationService
{
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

    public function checkQuests(Student $student, string $event, array $context = []): void
    {
        $quests = Quest::where('active', true)
            ->where(function ($q) use ($event) {
                $q->where('type', $event)
                  ->when(in_array($event, ['deposit', 'withdrawal']), fn ($q) => $q->orWhereIn('type', [$event.'_count', 'streak']));
            })
            ->get();

        if ($quests->isEmpty()) {
            return;
        }

        $completedIds = StudentQuestCompletion::where('student_id', $student->id)
            ->whereIn('quest_id', $quests->pluck('id'))
            ->pluck('quest_id')
            ->toArray();

        $transactionCount = $student->transactions()->count();
        $recentTransactions = null;

        foreach ($quests as $quest) {
            if (in_array($quest->id, $completedIds)) {
                continue;
            }

            if ($this->evaluateCriteria($student, $quest, $context, $transactionCount, $recentTransactions)) {
                StudentQuestCompletion::create([
                    'student_id' => $student->id,
                    'quest_id' => $quest->id,
                    'completed_at' => now(),
                ]);

                $this->addXp($student, $quest->xp_reward);
            }
        }
    }

    private function evaluateCriteria(Student $student, Quest $quest, array $context, int $transactionCount = 0, &$recentTransactions = null): bool
    {
        $criteria = $quest->criteria;

        if (empty($criteria)) {
            return true;
        }

        if (in_array($quest->type, ['savings_milestone', 'balance', 'milestone'])) {
            $target = $criteria['amount'] ?? 0;

            return $student->balance >= $target;
        }

        $txTypes = ['deposit', 'withdrawal', 'transaction', 'deposit_count', 'streak'];
        if (in_array($quest->type, $txTypes)) {
            $typeMap = [
                'deposit' => 'setor',
                'withdrawal' => 'tarik',
                'transaction' => null,
                'deposit_count' => 'setor',
                'streak' => 'setor',
            ];
            $transactionType = $typeMap[$quest->type] ?? null;
            $isStreak = $quest->type === 'streak' || ! empty($criteria['consecutive_days']);
            $targetDays = (int) ($criteria['days'] ?? $criteria['count'] ?? 1);

            if ($isStreak) {
                if ($recentTransactions === null) {
                    $recentTransactions = $student->transactions()
                        ->latest('transaction_date')
                        ->take($targetDays)
                        ->get()
                        ->pluck('transaction_date')
                        ->map(fn ($d) => $d instanceof Carbon ? $d->toDateString() : Carbon::parse($d)->toDateString())
                        ->unique()
                        ->sort();
                }

                if ($recentTransactions->count() < $targetDays) {
                    return false;
                }

                $dates = $recentTransactions->values();
                $first = Carbon::parse($dates->first());
                $last = Carbon::parse($dates->last());

                return $first->diffInDays($last) + 1 >= $targetDays;
            }

            $daysBack = (int) ($criteria['period_days'] ?? match($criteria['period'] ?? '') {'weekly' => 7, 'monthly' => 30, 'yearly' => 365, default => 30});

            return $student->transactions()
                ->where('transaction_date', '>=', now()->subDays($daysBack)->startOfDay())
                ->when($transactionType, fn ($q) => $q->where('type', $transactionType))
                ->count() >= (int) ($criteria['count'] ?? 0);
        }

        if ($quest->type === 'login') {
            $count = (int) ($criteria['count'] ?? 1);

            return $transactionCount >= $count && $student->progress?->last_login_at !== null;
        }

        return false;
    }

    public function ensureProgress(Student $student): StudentProgress
    {
        return $student->progress ?? StudentProgress::create([
            'student_id' => $student->id,
            'xp' => 0,
        ]);
    }
}

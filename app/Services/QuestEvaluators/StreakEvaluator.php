<?php

namespace App\Services\QuestEvaluators;

use App\Models\Quest;
use App\Models\Student;
use Carbon\Carbon;

class StreakEvaluator implements QuestEvaluatorInterface
{
    public function evaluate(Student $student, Quest $quest, array $context): bool
    {
        $criteria = $quest->criteria;
        $targetDays = (int) ($criteria['days'] ?? $criteria['count'] ?? 1);

        $dates = $student->transactions()
            ->latest('transaction_date')
            ->take($targetDays)
            ->get()
            ->pluck('transaction_date')
            ->map(fn ($d) => $d instanceof Carbon ? $d->toDateString() : Carbon::parse($d)->toDateString())
            ->unique()
            ->sort()
            ->values();

        if ($dates->count() < $targetDays) {
            return false;
        }

        $consecutive = 1;
        $maxStreak = 1;
        $prev = Carbon::parse($dates->first());

        for ($i = 1; $i < $dates->count(); $i++) {
            $curr = Carbon::parse($dates[$i]);
            if ((int) $prev->diffInDays($curr) === 1) {
                $consecutive++;
                $maxStreak = max($maxStreak, $consecutive);
            } else {
                $consecutive = 1;
            }
            $prev = $curr;
        }

        return $maxStreak >= $targetDays;
    }
}

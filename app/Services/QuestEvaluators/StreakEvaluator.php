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

        $first = Carbon::parse($dates->first());
        $last = Carbon::parse($dates->last());

        return $first->diffInDays($last) + 1 >= $targetDays;
    }
}

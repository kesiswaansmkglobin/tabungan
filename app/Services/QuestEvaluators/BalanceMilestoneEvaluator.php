<?php

namespace App\Services\QuestEvaluators;

use App\Models\Quest;
use App\Models\Student;

class BalanceMilestoneEvaluator implements QuestEvaluatorInterface
{
    public function evaluate(Student $student, Quest $quest, array $context): bool
    {
        $criteria = $quest->criteria;
        $target = $criteria['amount'] ?? 0;

        return $student->balance >= $target;
    }
}

<?php

namespace App\Services\QuestEvaluators;

use App\Models\Quest;
use App\Models\Student;

class LoginEvaluator implements QuestEvaluatorInterface
{
    public function evaluate(Student $student, Quest $quest, array $context): bool
    {
        $criteria = $quest->criteria;
        $count = (int) ($criteria['count'] ?? 1);

        return $student->progress?->last_login_at !== null
            && ($context['transaction_count'] ?? 0) >= $count;
    }
}

<?php

namespace App\Services\QuestEvaluators;

use App\Models\Quest;
use App\Models\Student;

interface QuestEvaluatorInterface
{
    public function evaluate(Student $student, Quest $quest, array $context): bool;
}

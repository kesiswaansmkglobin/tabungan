<?php

namespace App\Services\QuestEvaluators;

use App\Models\Quest;
use App\Models\Student;

class TransactionCountEvaluator implements QuestEvaluatorInterface
{
    public function evaluate(Student $student, Quest $quest, array $context): bool
    {
        $criteria = $quest->criteria;
        $transactionType = match ($quest->type) {
            'deposit', 'deposit_count' => 'setor',
            'withdrawal' => 'tarik',
            default => null,
        };

        $daysBack = match ($criteria['period'] ?? null) {
            'weekly' => 7, 'monthly' => 30, 'yearly' => 365,
            default => (int) ($criteria['period_days'] ?? 30),
        };

        return $student->transactions()
            ->where('transaction_date', '>=', now()->subDays($daysBack)->startOfDay())
            ->when($transactionType, fn ($q) => $q->where('type', $transactionType))
            ->count() >= (int) ($criteria['count'] ?? 0);
    }
}

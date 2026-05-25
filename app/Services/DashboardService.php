<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class DashboardService
{
    public function stats(?Collection $allowedClassIds = null): array
    {
        $studentQuery = Student::query();
        $transactionQuery = Transaction::query();

        if ($allowedClassIds !== null) {
            $studentQuery->whereIn('class_id', $allowedClassIds);
            $transactionQuery->whereHas('student', fn ($q) => $q->whereIn('class_id', $allowedClassIds));
        }

        $monthlyTrend = $this->buildMonthlyTrend(clone $transactionQuery);

        return [
            'totalStudents' => (clone $studentQuery)->count(),
            'totalTransactions' => (clone $transactionQuery)->count(),
            'totalBalance' => (clone $studentQuery)->sum('balance'),
            'todayTransactions' => (clone $transactionQuery)->whereDate('transaction_date', today())->count(),
            'monthlyTrend' => $monthlyTrend,
            'recentTransactions' => (clone $transactionQuery)
                ->with('student:id,nis,name')
                ->orderByDesc('transaction_date')
                ->take(5)
                ->get()
                ->each(fn ($t) => $t->setAppends(['created_by_user'])),
        ];
    }

    private function buildMonthlyTrend($transactionQuery): Collection
    {
        $now = now();
        $currentMonth = (int) $now->format('n');
        $currentYear = (int) $now->format('Y');
        $startYear = $currentMonth >= 7 ? $currentYear : $currentYear - 1;

        $monthlyData = (clone $transactionQuery)
            ->selectRaw("
                MONTH(transaction_date) as m,
                YEAR(transaction_date) as y,
                SUM(CASE WHEN type = 'setor' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'tarik' THEN amount ELSE 0 END) as expense
            ")
            ->where('transaction_date', '>=', "{$startYear}-07-01")
            ->where('transaction_date', '<=', ($startYear + 1).'-06-30')
            ->groupBy('y', 'm')
            ->orderBy('y')
            ->orderBy('m')
            ->get()
            ->keyBy(fn ($d) => $d->y.'-'.str_pad($d->m, 2, '0', STR_PAD_LEFT));

        $monthNames = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
        $monthlyTrend = collect();

        for ($i = 0; $i < 12; $i++) {
            $monthNum = $i < 6 ? $i + 7 : $i - 5;
            $year = $i < 6 ? $startYear : $startYear + 1;
            $key = $year.'-'.str_pad($monthNum, 2, '0', STR_PAD_LEFT);
            $data = $monthlyData->get($key);
            $monthlyTrend->push([
                'month' => $key,
                'label' => $monthNames[$i],
                'income' => (int) ($data->income ?? 0),
                'expense' => (int) ($data->expense ?? 0),
            ]);
        }

        return $monthlyTrend;
    }
}

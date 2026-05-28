<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Console\Command;

class RecalculateBalance extends Command
{
    protected $signature = 'tabungan:recalculate-balance {student? : Optional student ID}';

    protected $description = 'Recalculate student balances from transaction history';

    public function handle(): int
    {
        $query = Student::query();

        if ($studentId = $this->argument('student')) {
            $query->where('id', $studentId);
        }

        $students = $query->get();
        $count = 0;

        foreach ($students as $student) {
            $balance = Transaction::where('student_id', $student->id)
                ->selectRaw("SUM(CASE WHEN type = 'setor' THEN amount ELSE -amount END) as total")
                ->value('total') ?? 0;

            $student->balance = $balance;
            $student->save();

            $this->recalculateBalanceAfter($student->id);

            $this->line("Student #{$student->id} {$student->name}: saldo = {$balance}");
            $count++;
        }

        $this->info("Selesai! {$count} siswa diperbarui.");

        return self::SUCCESS;
    }

    private function recalculateBalanceAfter(int $studentId): void
    {
        $runningBalance = 0;

        $transactions = Transaction::where('student_id', $studentId)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        foreach ($transactions as $t) {
            $runningBalance += $t->type === 'setor' ? $t->amount : -$t->amount;
            if ($t->balance_after !== $runningBalance) {
                Transaction::where('id', $t->id)->update(['balance_after' => $runningBalance]);
            }
        }
    }
}

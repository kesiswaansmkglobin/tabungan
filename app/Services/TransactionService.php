<?php

namespace App\Services;

use App\Events\StudentTransactionUpdated;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TransactionService
{
    public function __construct(
        private GamificationService $gamification,
        private WhatsAppService $whatsapp,
    ) {}

    public function createTransaction(string $type, array $data): Transaction
    {
        $this->clearDashboardCache();

        return DB::transaction(function () use ($type, $data) {
            $student = Student::lockForUpdate()->findOrFail($data['student_id']);

            if ($type === 'tarik' && $student->balance < $data['amount']) {
                throw new \RuntimeException('Saldo siswa tidak mencukupi.');
            }

            $balanceAfter = $type === 'setor'
                ? $student->balance + $data['amount']
                : $student->balance - $data['amount'];

            $xpAmount = $type === 'setor' ? 10 : 5;
            $questEvent = $type === 'setor' ? 'deposit' : 'withdrawal';
            $defaultNote = $type === 'setor' ? 'Setoran harian siswa' : null;

            $transaction = Transaction::create([
                'student_id' => $student->id,
                'type' => $type,
                'amount' => $data['amount'],
                'balance_after' => $balanceAfter,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'note' => $data['note'] ?? $defaultNote,
                'created_by' => auth()->id(),
            ]);

            $student->balance = $balanceAfter;
            $student->save();

            $this->gamification->ensureProgress($student);
            $this->gamification->addXp($student, $xpAmount);
            $this->gamification->syncTier($student);
            $questsCompleted = $this->gamification->checkQuests($student, $questEvent, ['amount' => $data['amount']]);
            $this->gamification->checkQuests($student, 'savings_milestone');

            StudentTransactionUpdated::dispatch($student, $questsCompleted);

            if (Gate::allows('send-whatsapp')) {
                $this->whatsapp->sendTransactionNotification($student, $type, $data['amount'], $balanceAfter);
            }

            activity()
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'transaction_id' => $transaction->id,
                    'amount' => $data['amount'],
                    'balance_after' => $balanceAfter,
                ])
                ->log($type);

            return $transaction;
        });
    }

    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        $this->clearDashboardCache();

        return DB::transaction(function () use ($transaction, $data) {
            $student = Student::lockForUpdate()->findOrFail($transaction->student_id);

            $oldAmount = $transaction->amount;
            $oldType = $transaction->type;

            $transaction->update([
                'amount' => $data['amount'] ?? $transaction->amount,
                'transaction_date' => $data['transaction_date'] ?? $transaction->transaction_date,
                'note' => $data['note'] ?? $transaction->note,
            ]);

            $diff = $oldType === 'setor'
                ? $oldAmount - $transaction->amount
                : $transaction->amount - $oldAmount;

            $student->balance = $student->balance - $diff;

            if ($student->balance < 0) {
                throw new \RuntimeException('Saldo tidak boleh negatif setelah perubahan.');
            }

            $student->save();

            $this->recalculateBalanceAfter($student->id);

            $this->gamification->syncTier($student);
            $this->gamification->checkQuests($student, 'savings_milestone');

            StudentTransactionUpdated::dispatch($student);

            activity()
                ->performedOn($transaction)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_amount' => $oldAmount,
                    'new_amount' => $transaction->amount,
                    'old_type' => $oldType,
                    'note' => $data['note'] ?? $transaction->note,
                ])
                ->log('updated');

            return $transaction;
        });
    }

    public function deleteTransaction(Transaction $transaction): void
    {
        $this->clearDashboardCache();

        DB::transaction(function () use ($transaction) {
            $student = Student::lockForUpdate()->findOrFail($transaction->student_id);

            $balanceAdjustment = $transaction->type === 'setor'
                ? -$transaction->amount
                : $transaction->amount;

            $student->balance = $student->balance + $balanceAdjustment;

            if ($student->balance < 0) {
                throw new \RuntimeException('Penghapusan transaksi menyebabkan saldo negatif.');
            }

            $student->save();

            $this->gamification->syncTier($student);
            $this->gamification->checkQuests($student, 'savings_milestone');

            StudentTransactionUpdated::dispatch($student);

            $transaction->delete();

            $this->recalculateBalanceAfter($student->id);

            activity()
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'deleted_transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                ])
                ->log('deleted');
        });
    }

    private function clearDashboardCache(): void
    {
        Cache::forget('dashboard_stats_admin');
        Cache::forget('dashboard_stats_wk_'.auth()->id());
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

<?php

namespace App\Services;

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

    public function createDeposit(array $data): Transaction
    {
        Cache::forget('dashboard_stats_admin');
        Cache::forget('dashboard_stats_wk_'.auth()->id());

        return DB::transaction(function () use ($data) {
            $student = Student::lockForUpdate()->findOrFail($data['student_id']);

            $balanceAfter = $student->balance + $data['amount'];

            $transaction = Transaction::create([
                'student_id' => $student->id,
                'type' => 'setor',
                'amount' => $data['amount'],
                'balance_after' => $balanceAfter,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $student->update(['balance' => $balanceAfter]);

            $this->gamification->ensureProgress($student);
            $this->gamification->addXp($student, 10);
            $this->gamification->syncTier($student);
            $this->gamification->checkQuests($student, 'deposit', ['amount' => $data['amount']]);
            $this->gamification->checkQuests($student, 'savings_milestone');
            if (Gate::allows('send-whatsapp')) {
                $this->whatsapp->sendTransactionNotification($student, 'setor', $data['amount'], $balanceAfter);
            }

            activity()
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'transaction_id' => $transaction->id,
                    'amount' => $data['amount'],
                    'balance_after' => $balanceAfter,
                ])
                ->log('setor');

            return $transaction;
        });
    }

    public function createWithdrawal(array $data): Transaction
    {
        Cache::forget('dashboard_stats_admin');
        Cache::forget('dashboard_stats_wk_'.auth()->id());

        return DB::transaction(function () use ($data) {
            $student = Student::lockForUpdate()->findOrFail($data['student_id']);

            if ($student->balance < $data['amount']) {
                throw new \RuntimeException('Saldo siswa tidak mencukupi.');
            }

            $balanceAfter = $student->balance - $data['amount'];

            $transaction = Transaction::create([
                'student_id' => $student->id,
                'type' => 'tarik',
                'amount' => $data['amount'],
                'balance_after' => $balanceAfter,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $student->update(['balance' => $balanceAfter]);

            $this->gamification->ensureProgress($student);
            $this->gamification->addXp($student, 5);
            $this->gamification->syncTier($student);
            $this->gamification->checkQuests($student, 'withdrawal', ['amount' => $data['amount']]);
            $this->gamification->checkQuests($student, 'savings_milestone');
            if (Gate::allows('send-whatsapp')) {
                $this->whatsapp->sendTransactionNotification($student, 'tarik', $data['amount'], $balanceAfter);
            }

            activity()
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'transaction_id' => $transaction->id,
                    'amount' => $data['amount'],
                    'balance_after' => $balanceAfter,
                ])
                ->log('tarik');

            return $transaction;
        });
    }

    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        Cache::forget('dashboard_stats_admin');
        Cache::forget('dashboard_stats_wk_'.auth()->id());

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

            $student->update(['balance' => $student->balance - $diff]);

            if ($student->balance < 0) {
                throw new \RuntimeException('Saldo tidak boleh negatif setelah perubahan.');
            }

            $this->gamification->syncTier($student);
            $this->gamification->checkQuests($student, 'savings_milestone');

            return $transaction;
        });
    }

    public function deleteTransaction(Transaction $transaction): void
    {
        Cache::forget('dashboard_stats_admin');
        Cache::forget('dashboard_stats_wk_'.auth()->id());

        DB::transaction(function () use ($transaction) {
            $student = Student::lockForUpdate()->findOrFail($transaction->student_id);

            $balanceAdjustment = $transaction->type === 'setor'
                ? -$transaction->amount
                : $transaction->amount;

            $student->update(['balance' => $student->balance + $balanceAdjustment]);

            if ($student->balance < 0) {
                throw new \RuntimeException('Penghapusan transaksi menyebabkan saldo negatif.');
            }

            $this->gamification->syncTier($student);
            $this->gamification->checkQuests($student, 'savings_milestone');

            $transaction->delete();
        });
    }
}

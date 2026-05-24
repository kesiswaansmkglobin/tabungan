<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        activity()
            ->performedOn($transaction)
            ->causedBy(auth()->user())
            ->withProperties([
                'student_id' => $transaction->student_id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'balance_after' => $transaction->balance_after,
            ])
            ->log('transaction_created');
    }

    public function updated(Transaction $transaction): void
    {
        activity()
            ->performedOn($transaction)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $transaction->getOriginal(),
                'new' => $transaction->getChanges(),
            ])
            ->log('transaction_updated');
    }

    public function deleted(Transaction $transaction): void
    {
        activity()
            ->performedOn($transaction)
            ->causedBy(auth()->user())
            ->withProperties([
                'student_id' => $transaction->student_id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
            ])
            ->log('transaction_deleted');
    }
}

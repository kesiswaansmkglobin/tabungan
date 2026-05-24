<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    public function index(): Response
    {
        $transactions = Transaction::with(['student:id,nis,name', 'createdBy:id,name'])
            ->latest()
            ->paginate(25);

        $transactions->getCollection()->transform(function ($t) {
            $t->setAppends(['created_by_user']);

            return $t;
        });

        $classes = ClassRoom::select('id', 'name')->orderBy('name')->get();

        $students = Student::select('id', 'nis', 'name', 'class_id', 'balance')
            ->orderBy('name')
            ->get();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'students' => $students,
            'classes' => $classes,
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        try {
            if ($request->type === 'setor') {
                $this->transactionService->createDeposit($request->validated());
            } else {
                $this->transactionService->createWithdrawal($request->validated());
            }

            return back()->with('success', 'Transaksi berhasil.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        try {
            $this->transactionService->updateTransaction($transaction, $request->validated());

            return back()->with('success', 'Transaksi diperbarui.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        try {
            $this->transactionService->deleteTransaction($transaction);

            return back()->with('success', 'Transaksi dihapus.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getStudent(Student $student): JsonResponse
    {
        return response()->json($student->load('class:id,name'));
    }
}

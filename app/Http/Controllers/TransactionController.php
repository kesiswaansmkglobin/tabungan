<?php

namespace App\Http\Controllers;

use App\Exports\TransactionTemplateExport;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Imports\TransactionImport;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            $this->transactionService->createTransaction($request->type, $request->validated());

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
        $this->authorize('delete', $transaction);

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

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('create', Transaction::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new TransactionImport;
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor: '.$e->getMessage());
        }

        Cache::forget('dashboard_stats_admin');
        Cache::forget('dashboard_stats_wk_'.auth()->id());

        $imported = $import->getImportedCount();
        $errors = $import->getErrors();

        if ($imported === 0 && $errors->isEmpty()) {
            return back()->with('error', 'Tidak ada transaksi yang diimpor. Periksa format file (Tanggal, NIS, Jenis, Jumlah).');
        }

        $message = $imported.' transaksi berhasil diimpor.';
        if ($errors->isNotEmpty()) {
            $message .= ' '.$errors->count().' baris dilewati: '.$errors->implode('; ');
        }

        return back()->with('success', $message);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new TransactionTemplateExport, 'template_transaksi.xlsx');
    }
}

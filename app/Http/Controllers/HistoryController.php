<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\ClassRoom;
use App\Models\SchoolData;
use App\Models\Student;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HistoryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $filters = $request->only(['type', 'student_id', 'class_id', 'date_from', 'date_to']);

        $user = auth()->user();
        $isWaliKelas = $user->hasRole('wali_kelas');
        $allowedClassIds = $isWaliKelas ? $user->classes()->pluck('id') : null;

        $transactions = Transaction::with('student:id,nis,name,class_id', 'student.class:id,name', 'createdBy:id,name')
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['student_id'] ?? null, fn ($q, $v) => $q->where('student_id', $v))
            ->when($filters['class_id'] ?? null && ! $isWaliKelas, fn ($q, $v) => $q->whereHas('student', fn ($sq) => $sq->where('class_id', $v)))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('transaction_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('transaction_date', '<=', $v.' 23:59:59'))
            ->when($isWaliKelas, fn ($q) => $q->whereHas('student', fn ($sq) => $sq->whereIn('class_id', $allowedClassIds)))
            ->latest('transaction_date')
            ->paginate(25)
            ->through(fn ($t) => $t->setAppends(['created_by_user']));

        $studentQuery = Student::select('id', 'nis', 'name')->orderBy('name');
        if ($isWaliKelas) {
            $studentQuery->whereIn('class_id', $allowedClassIds);
        }
        $students = $studentQuery->get();

        $classQuery = ClassRoom::select('id', 'name');
        if ($isWaliKelas) {
            $classQuery->whereIn('id', $allowedClassIds);
        }
        $classes = $classQuery->get();

        return Inertia::render('History', [
            'transactions' => $transactions,
            'students' => $students,
            'classes' => $classes,
            'filters' => $filters,
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['type', 'student_id', 'class_id', 'date_from', 'date_to']);

        $user = auth()->user();
        $isWaliKelas = $user->hasRole('wali_kelas');
        $allowedClassIds = $isWaliKelas ? $user->classes()->pluck('id') : null;

        return Excel::download(
            new TransactionsExport($filters, $isWaliKelas ? $allowedClassIds->toArray() : null),
            now()->format('Ymd_His').'_riwayat.xlsx'
        );
    }

    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $filters = $request->only(['type', 'student_id', 'class_id', 'date_from', 'date_to']);

        $user = auth()->user();
        $isWaliKelas = $user->hasRole('wali_kelas');
        $allowedClassIds = $isWaliKelas ? $user->classes()->pluck('id') : null;

        $transactions = Transaction::with('student:id,nis,name,class_id', 'student.class:id,name', 'createdBy:id,name')
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['student_id'] ?? null, fn ($q, $v) => $q->where('student_id', $v))
            ->when($filters['class_id'] ?? null && ! $isWaliKelas, fn ($q, $v) => $q->whereHas('student', fn ($sq) => $sq->where('class_id', $v)))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('transaction_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('transaction_date', '<=', $v.' 23:59:59'))
            ->when($isWaliKelas, fn ($q) => $q->whereHas('student', fn ($sq) => $sq->whereIn('class_id', $allowedClassIds)))
            ->latest('transaction_date')
            ->get();

        $school = SchoolData::first();

        $pdf = Pdf::loadView('pdf.report', [
            'transactions' => $transactions,
            'filters' => $filters,
            'school' => $school,
        ]);

        return $pdf->download(now()->format('Ymd_His').'_riwayat.pdf');
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\StudentsExport;
use App\Models\ClassRoom;
use App\Models\SchoolData;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $user = auth()->user();
        $isWaliKelas = $user->hasRole('wali_kelas');
        $allowedClassIds = $isWaliKelas ? $user->classes()->pluck('id') : null;

        $classQuery = ClassRoom::select('id', 'name');
        if ($isWaliKelas) {
            $classQuery->whereIn('id', $allowedClassIds);
        }
        $classes = $classQuery->get();

        $query = Student::with('class:id,name')
            ->withCount('transactions');

        if ($request->filled('class_id') && ! $isWaliKelas) {
            $query->where('class_id', $request->class_id);
        }

        if ($isWaliKelas) {
            $query->whereIn('class_id', $allowedClassIds);
        }

        $students = $query->orderBy('class_id')->orderBy('name')->get();

        return Inertia::render('Reports', [
            'classes' => $classes,
            'students' => $students,
            'filters' => $request->only('class_id'),
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $user = auth()->user();
        $isWaliKelas = $user->hasRole('wali_kelas');
        $allowedClassIds = $isWaliKelas ? $user->classes()->pluck('id') : null;

        $filters = $request->validate([
            'class_id' => 'nullable|exists:classes,id',
        ]);

        if ($isWaliKelas) {
            $filters['class_ids'] = $allowedClassIds->toArray();
        }

        $filename = now()->format('Ymd_His').'_rekap_saldo.xlsx';

        return Excel::download(new StudentsExport($filters), $filename);
    }

    public function exportPdf(Request $request): Response
    {
        $filters = $request->validate([
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $user = auth()->user();
        $isWaliKelas = $user->hasRole('wali_kelas');
        $allowedClassIds = $isWaliKelas ? $user->classes()->pluck('id') : null;

        $query = Student::with('class:id,name');

        if ($request->filled('class_id') && ! $isWaliKelas) {
            $query->where('class_id', $request->class_id);
        }

        if ($isWaliKelas) {
            $query->whereIn('class_id', $allowedClassIds);
        }

        $students = $query->orderBy('class_id')->orderBy('name')->get();

        $school = SchoolData::first();

        $pdf = Pdf::loadView('pdf.rekap_saldo', [
            'students' => $students,
            'filters' => $filters,
            'school' => $school,
        ]);

        return $pdf->download(now()->format('Ymd_His').'_rekap_saldo.pdf');
    }

    public function bukuTabungan(Student $student): Response
    {
        $user = auth()->user();
        if ($user->hasRole('wali_kelas')) {
            $allowedClassIds = $user->classes()->pluck('id');
            abort_if(! $allowedClassIds->contains($student->class_id), 403, 'Anda tidak memiliki akses ke siswa ini.');
        }

        $student->load('class:id,name');
        $transactions = $student->transactions()
            ->with('createdBy:id,name')
            ->latest('transaction_date')
            ->get();

        $pdf = Pdf::loadView('pdf.buku_tabungan', [
            'student' => $student,
            'transactions' => $transactions,
            'school' => SchoolData::first(),
        ]);

        $pdf->setPaper('A5', 'landscape');

        return $pdf->download('buku_tabungan_'.$student->nis.'.pdf');
    }
}

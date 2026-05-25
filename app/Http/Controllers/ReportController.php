<?php

namespace App\Http\Controllers;

use App\Exports\StudentsExport;
use App\Http\Controllers\Concerns\HasWaliKelasScope;
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
    use HasWaliKelasScope;

    public function index(Request $request): \Inertia\Response
    {
        $classes = $this->scopeClassesForCurrentUser(
            ClassRoom::select('id', 'name')
        )->get();

        $students = $this->scopeStudentsByClass(
            $this->scopeStudentsForCurrentUser(
                Student::with('class:id,name')->withCount('transactions')
            ),
            $request->class_id
        )->orderBy('class_id')->orderBy('name')->get();

        return Inertia::render('Reports', [
            'classes' => $classes,
            'students' => $students,
            'filters' => $request->only('class_id'),
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $filters = $request->validate([
            'class_id' => 'nullable|exists:classes,id',
        ]);

        if ($this->isWaliKelas()) {
            $filters['class_ids'] = $this->allowedClassIds()->toArray();
        }

        return Excel::download(
            new StudentsExport($filters),
            now()->format('Ymd_His').'_rekap_saldo.xlsx'
        );
    }

    public function exportPdf(Request $request): Response
    {
        $filters = $request->validate([
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $students = $this->scopeStudentsByClass(
            $this->scopeStudentsForCurrentUser(
                Student::with('class:id,name')
            ),
            $request->class_id
        )->orderBy('class_id')->orderBy('name')->get();

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
        if ($this->isWaliKelas() && ! $this->allowedClassIds()->contains($student->class_id)) {
            abort(403, 'Anda tidak memiliki akses ke siswa ini.');
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

<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Imports\StudentImport;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Services\GamificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentController extends Controller
{
    public function __construct(
        private GamificationService $gamification,
    ) {}

    public function index(Request $request): Response
    {
        $query = Student::with('class:id,name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $sortField = $request->sort ?? 'created_at';
        $sortDir = $request->dir === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['nis', 'name', 'balance', 'class_id', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            $query->latest();
        }

        $students = $query->paginate(25);

        $classes = ClassRoom::select('id', 'name')->get();

        return Inertia::render('Admin/Students', [
            'students' => $students,
            'classes' => $classes,
            'filters' => $request->only(['search', 'sort', 'dir']),
            'canManage' => $request->user()->hasRole('admin'),
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make('smkglobin');
        Student::create($data);

        return back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());

        return back()->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        if ($student->transactions()->exists()) {
            return back()->with('error', 'Siswa memiliki riwayat transaksi dan tidak bisa dihapus.');
        }

        $student->delete();

        return back()->with('success', 'Siswa berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $before = Student::count();

        try {
            Excel::import(new StudentImport, $request->file('file'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor: '.$e->getMessage());
        }

        $imported = Student::count() - $before;

        if ($imported === 0) {
            return back()->with('error', 'Tidak ada siswa yang diimpor. Periksa format file (NIS, Nama, Kelas) dan pastikan nama kelas sesuai dengan data kelas yang sudah ada.');
        }

        return back()->with('success', $imported.' siswa berhasil diimpor.');
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new StudentTemplateExport, 'template_siswa.xlsx');
    }

    public function qrCode(Student $student): \Illuminate\Http\Response
    {
        $url = route('student.login', ['nis' => $student->nis]);

        $qrCode = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('M')
            ->generate($url);

        return response($qrCode, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qrcode-'.$student->nis.'.svg"',
        ]);
    }

    public function qrCodeDownload(Student $student): \Illuminate\Http\Response
    {
        $url = route('student.login', ['nis' => $student->nis]);

        $qrCode = QrCode::format('png')
            ->size(500)
            ->errorCorrection('M')
            ->generate($url);

        return response($qrCode, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="qrcode-'.$student->nis.'.png"',
        ]);
    }
}

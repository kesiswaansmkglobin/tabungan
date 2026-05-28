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
use Illuminate\Support\Facades\Cache;
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
        $password = $data['password'] ?? null;
        unset($data['password']);

        $student = Student::create($data);
        $student->password = $password ?: 'smkglobin';
        $student->save();
        $student->refresh();

        $this->gamification->ensureProgress($student);
        $this->gamification->syncTier($student);

        Cache::forget('dashboard_stats_admin');
        Cache::forget('dashboard_stats_wk_'.auth()->id());

        return back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $student->password = $data['password'];
            $student->save();
        }
        unset($data['password']);

        $student->update($data);

        $this->gamification->syncTier($student);

        Cache::forget('dashboard_stats_admin');
        Cache::forget('dashboard_stats_wk_'.auth()->id());

        return back()->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        if ($student->transactions()->exists()) {
            return back()->with('error', 'Siswa memiliki riwayat transaksi dan tidak bisa dihapus.');
        }

        $student->delete();

        Cache::forget('dashboard_stats_admin');
        Cache::forget('dashboard_stats_wk_'.auth()->id());

        return back()->with('success', 'Siswa berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('create', Student::class);

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
        $download = request()->route()->named('*.download');
        $url = route('student.login', ['nis' => $student->nis]);

        $format = $download ? 'png' : 'svg';
        $size = $download ? 500 : 300;
        $disposition = $download ? 'attachment' : 'inline';
        $mime = $download ? 'image/png' : 'image/svg+xml';

        $qrCode = QrCode::format($format)
            ->size($size)
            ->errorCorrection('M')
            ->generate($url);

        return response($qrCode, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition.'; filename="qrcode-'.$student->nis.'.'.$format.'"',
        ]);
    }
}

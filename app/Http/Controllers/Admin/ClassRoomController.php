<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClassRoomController extends Controller
{
    public function index(): Response
    {
        $classes = ClassRoom::with('waliKelas:id,name')
            ->withCount('students')
            ->latest()
            ->paginate(25);

        $waliKelas = User::whereHas('roles', fn ($q) => $q->where('role', 'wali_kelas'))
            ->select('id', 'name')
            ->get();

        return Inertia::render('Admin/Classes', [
            'classes' => $classes,
            'waliKelas' => $waliKelas,
        ]);
    }

    public function store(StoreClassRequest $request): RedirectResponse
    {
        ClassRoom::create($request->validated());

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(UpdateClassRequest $request, ClassRoom $class): RedirectResponse
    {
        $class->update($request->validated());

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(ClassRoom $class): RedirectResponse
    {
        if ($class->students()->exists()) {
            return back()->with('error', 'Kelas tidak bisa dihapus karena masih memiliki siswa.');
        }

        $class->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}

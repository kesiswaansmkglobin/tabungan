<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WaliKelasController extends Controller
{
    public function students(Request $request): Response
    {
        $user = $request->user();
        $classIds = $user->classes()->pluck('id');

        $query = Student::with('class:id,name')->whereIn('class_id', $classIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $sortField = $request->sort ?? 'name';
        $sortDir = $request->dir === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['nis', 'name', 'balance', 'class_id', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            $query->orderBy('name');
        }

        $students = $query->paginate(25);

        return Inertia::render('WaliKelas/Students', [
            'students' => $students,
            'filters' => $request->only(['search', 'sort', 'dir']),
        ]);
    }
}

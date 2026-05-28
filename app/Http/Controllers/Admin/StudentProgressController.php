<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Tier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentProgressController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Student::with(['class:id,name', 'progress.tier'])
            ->withCount('questCompletions as quests_completed');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('tier_id')) {
            $query->whereHas('progress', fn ($q) => $q->where('tier_id', $request->tier_id));
        }

        $students = $query->paginate(25);

        $classes = ClassRoom::select('id', 'name')->get();
        $tiers = Tier::orderBy('min_balance')->get(['id', 'name', 'icon', 'color']);

        return Inertia::render('Admin/StudentProgress', [
            'students' => $students,
            'classes' => $classes,
            'tiers' => $tiers,
            'filters' => $request->only(['search', 'class_id', 'tier_id']),
        ]);
    }
}

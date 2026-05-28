<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentAuthController extends Controller
{
    public function __construct(
        private GamificationService $gamification,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nis' => 'required|string|exists:students,nis',
            'password' => 'required|string',
        ]);

        $student = Student::where('nis', $validated['nis'])->first();

        if (! $student || ! Hash::check($validated['password'], $student->password)) {
            return response()->json(['message' => 'NIS atau password salah.'], 401);
        }

        $token = $student->createToken('student-api')->plainTextToken;

        $progress = $this->gamification->ensureProgress($student);
        $progress->update(['last_login_at' => now()]);

        $loginCompleted = $this->gamification->checkQuests($student, 'login');
        $milestoneCompleted = $this->gamification->checkQuests($student, 'savings_milestone');

        return response()->json([
            'quests_completed' => array_merge($loginCompleted, $milestoneCompleted),
            'token' => $token,
            'student' => [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->name,
                'balance' => $student->balance,
                'class' => $student->class?->name,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user()->load('class:id,name', 'progress.tier');

        return response()->json([
            'student' => [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->name,
                'balance' => $student->balance,
                'class' => $student->class?->name,
                'tier' => $student->progress?->tier?->name,
                'xp' => $student->progress?->xp ?? 0,
            ],
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $transactions = $student->transactions()
            ->latest()
            ->paginate(25);

        $transactions->getCollection()->transform(function ($t) {
            $t->setAppends(['created_by_user']);

            return $t;
        });

        return response()->json($transactions);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function dashboard(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user()->load('class:id,name', 'progress.tier');

        $recentTransactions = $student->transactions()
            ->latest()
            ->take(10)
            ->get()
            ->each(fn ($t) => $t->setAppends(['created_by_user']));

        return response()->json([
            'student' => [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->name,
                'balance' => $student->balance,
                'class' => $student->class?->name,
                'tier' => $student->progress?->tier?->name,
                'xp' => $student->progress?->xp ?? 0,
            ],
            'stats' => DB::query()->fromSub(
                fn ($q) => $q->from('transactions')
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'setor' THEN amount END), 0) as total_deposit")
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'tarik' THEN amount END), 0) as total_withdrawal")
                    ->selectRaw('COUNT(*) as transaction_count')
                    ->where('student_id', $student->id),
                'agg'
            )->first(),
            'recent_transactions' => $recentTransactions,
        ]);
    }
}

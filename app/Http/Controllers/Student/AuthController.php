<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quest;
use App\Models\Student;
use App\Models\StudentQuestCompletion;
use App\Models\Tier;
use App\Services\GamificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        private GamificationService $gamification,
    ) {}

    public function showLogin(Request $request): Response
    {
        return Inertia::render('Student/Login', [
            'prefillNis' => $request->nis,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => 'required|string|exists:students,nis',
            'password' => 'nullable|string',
        ]);

        $student = Student::where('nis', $validated['nis'])->first();

        if ($validated['password']) {
            $stored = $student->password;
            if (! $stored) {
                $stored = Hash::make('smkglobin');
                $student->update(['password' => $stored]);
            }
            if (! Hash::check($validated['password'], $stored)) {
                return back()->withErrors([
                    'password' => 'Password salah.',
                ])->onlyInput('nis');
            }
        }

        auth('student')->login($student);

        $request->session()->regenerate();

        $progress = $this->gamification->ensureProgress($student);
        $progress->update(['last_login_at' => now()]);

        $this->gamification->syncTier($student);

        $loginCompleted = $this->gamification->checkQuests($student, 'login');
        $milestoneCompleted = $this->gamification->checkQuests($student, 'savings_milestone');

        $allCompleted = array_merge($loginCompleted, $milestoneCompleted);
        if (! empty($allCompleted)) {
            session()->flash('quests_completed', $allCompleted);
        }

        return redirect()->intended(route('student.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        auth('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }

    public function dashboard(): Response
    {
        /** @var Student $student */
        $student = auth('student')->user()->load('class:id,name', 'progress.tier');

        $transactions = $student->transactions()
            ->latest()
            ->take(10)
            ->get()
            ->each(fn ($t) => $t->setAppends(['created_by_user']));

        $statsAgg = DB::query()
            ->fromSub(function ($q) use ($student) {
                $q->from('transactions')
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'setor' THEN amount END), 0) as total_deposit")
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'tarik' THEN amount END), 0) as total_withdrawal")
                    ->selectRaw('COUNT(*) as transaction_count')
                    ->where('student_id', $student->id);
            }, 'agg')
            ->first();

        $stats = [
            'total_deposit' => (int) $statsAgg->total_deposit,
            'total_withdrawal' => (int) $statsAgg->total_withdrawal,
            'transaction_count' => (int) $statsAgg->transaction_count,
        ];

        $activeQuests = Quest::where('active', true)->get();
        $completedQuestIds = StudentQuestCompletion::where('student_id', $student->id)
            ->pluck('quest_id')
            ->toArray();

        $allTiers = Tier::orderBy('min_balance')->get();
        $currentTier = $student->progress?->tier;
        $nextTier = null;
        $xpProgress = 0;
        $xpToNext = 0;

        if ($currentTier) {
            $nextTier = Tier::where('min_balance', '>', $currentTier->min_balance)
                ->orderBy('min_balance')
                ->first();
        } else {
            $nextTier = $allTiers->first();
        }

        if ($nextTier && $currentTier) {
            $range = $nextTier->min_balance - $currentTier->min_balance;
            $progress = $student->balance - $currentTier->min_balance;
            $xpProgress = $range > 0 ? min(100, max(0, round(($progress / $range) * 100))) : 100;
            $xpToNext = $nextTier->min_balance - $student->balance;
        } elseif (! $currentTier && $nextTier) {
            $xpProgress = min(100, max(0, round(($student->balance / $nextTier->min_balance) * 100)));
            $xpToNext = $nextTier->min_balance - $student->balance;
        } else {
            $xpProgress = 100;
        }

        $questsCompleted = session()->pull('quests_completed', []);

        return Inertia::render('Student/Dashboard', [
            'student' => $student,
            'transactions' => $transactions,
            'stats' => $stats,
            'quests' => $activeQuests->map(fn ($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'description' => $q->description,
                'xp_reward' => $q->xp_reward,
                'type' => $q->type,
                'completed' => in_array($q->id, $completedQuestIds),
            ]),
            'nextTier' => $nextTier ? [
                'name' => $nextTier->name,
                'icon' => $nextTier->icon,
                'color' => $nextTier->color,
                'min_balance' => $nextTier->min_balance,
            ] : null,
            'tierProgress' => $xpProgress,
            'xpToNext' => $xpToNext,
            'allTiers' => $allTiers->map(fn ($t) => [
                'name' => $t->name,
                'icon' => $t->icon,
                'color' => $t->color,
                'min_balance' => $t->min_balance,
            ]),
            'questsCompleted' => $questsCompleted,
        ]);
    }
}

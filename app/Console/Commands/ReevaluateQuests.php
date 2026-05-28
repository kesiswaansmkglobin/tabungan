<?php

namespace App\Console\Commands;

use App\Models\Quest;
use App\Models\Student;
use App\Services\GamificationService;
use Illuminate\Console\Command;

class ReevaluateQuests extends Command
{
    protected $signature = 'tabungan:reevaluate-quests {student? : Optional student ID}';

    protected $description = 'Re-evaluate all active quests for students';

    public function handle(GamificationService $gamification): int
    {
        $query = Student::with('progress');

        if ($studentId = $this->argument('student')) {
            $query->where('id', $studentId);
        }

        $students = $query->get();
        $count = 0;

        foreach ($students as $student) {
            $newCompletions = [];

            $types = Quest::where('active', true)
                ->distinct()
                ->pluck('type');

            foreach ($types as $type) {
                $event = match (true) {
                    in_array($type, ['savings_milestone', 'balance', 'milestone']) => 'savings_milestone',
                    in_array($type, ['deposit', 'deposit_count', 'streak']) => 'deposit',
                    $type === 'withdrawal' => 'withdrawal',
                    $type === 'login' => 'login',
                    default => $type,
                };

                $completed = $gamification->checkQuests($student, $event);
                $newCompletions = array_merge($newCompletions, $completed);
            }

            if (! empty($newCompletions)) {
                foreach ($newCompletions as $q) {
                    $this->line("Student #{$student->id} {$student->name}: quest '{$q['title']}' selesai (+{$q['xp_reward']} XP)");
                }
            }

            $gamification->syncTier($student);
            $count++;
        }

        $this->info("Selesai! {$count} siswa diproses.");

        return self::SUCCESS;
    }
}

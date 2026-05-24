<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\StudentProgress;

class StudentObserver
{
    public function created(Student $student): void
    {
        StudentProgress::create([
            'student_id' => $student->id,
            'xp' => 0,
        ]);
    }
}

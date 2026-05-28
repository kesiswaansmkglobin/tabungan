<?php

namespace App\Events;

use App\Models\Student;
use App\Models\Tier;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentTransactionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $studentId;

    public int $balance;

    public ?string $tierName;

    public int $tierProgress;

    public array $questsCompleted;

    public function __construct(Student $student, array $questsCompleted = [])
    {
        $this->studentId = $student->id;
        $this->balance = $student->balance;
        $this->questsCompleted = $questsCompleted;

        $tier = $student->progress?->tier;
        $this->tierName = $tier?->name;

        $nextTier = $tier
            ? Tier::where('min_balance', '>', $tier->min_balance)->orderBy('min_balance')->first()
            : Tier::orderBy('min_balance')->first();

        if ($nextTier && $tier) {
            $range = $nextTier->min_balance - $tier->min_balance;
            $progress = $student->balance - $tier->min_balance;
            $this->tierProgress = $range > 0 ? min(100, max(0, (int) (($progress / $range) * 100))) : 100;
        } elseif ($nextTier) {
            $this->tierProgress = min(100, max(0, (int) (($student->balance / $nextTier->min_balance) * 100)));
        } else {
            $this->tierProgress = 100;
        }
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('student.'.$this->studentId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.updated';
    }
}

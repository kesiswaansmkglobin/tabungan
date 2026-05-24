<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'type',
        'amount',
        'balance_after',
        'transaction_date',
        'note',
        'created_by',
    ];

    protected $appends = ['created_by_user'];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCreatedByUserAttribute(): ?User
    {
        return $this->relationLoaded('createdBy') ? $this->createdBy : null;
    }
}

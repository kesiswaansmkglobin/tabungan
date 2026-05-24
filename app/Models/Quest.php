<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'xp_reward',
        'type',
        'criteria',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'active' => 'boolean',
        ];
    }

    public function completions(): HasMany
    {
        return $this->hasMany(StudentQuestCompletion::class);
    }
}

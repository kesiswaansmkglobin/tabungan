<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_balance',
        'icon',
        'color',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'min_balance' => 'integer',
            'order_index' => 'integer',
        ];
    }

    public function studentProgresses(): HasMany
    {
        return $this->hasMany(StudentProgress::class);
    }

    public function nextTier(): ?Tier
    {
        return Tier::where('min_balance', '>', $this->min_balance)
            ->orderBy('min_balance')
            ->first();
    }
}

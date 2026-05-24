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

    public function studentProgresses(): HasMany
    {
        return $this->hasMany(StudentProgress::class);
    }
}

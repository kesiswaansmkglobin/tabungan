<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait HasWaliKelasScope
{
    private ?Collection $cachedAllowedClassIds = null;

    protected function allowedClassIds(): ?Collection
    {
        if ($this->cachedAllowedClassIds === null) {
            $user = auth()->user();
            $this->cachedAllowedClassIds = $user->hasRole('wali_kelas')
                ? $user->classes()->pluck('id')
                : null;
        }

        return $this->cachedAllowedClassIds;
    }

    protected function isWaliKelas(): bool
    {
        return auth()->user()->hasRole('wali_kelas');
    }

    protected function scopeStudentsForCurrentUser(Builder $query): Builder
    {
        if ($this->isWaliKelas()) {
            $query->whereIn('class_id', $this->allowedClassIds());
        }

        return $query;
    }

    protected function scopeClassesForCurrentUser(Builder $query): Builder
    {
        if ($this->isWaliKelas()) {
            $query->whereIn('id', $this->allowedClassIds());
        }

        return $query;
    }

    protected function scopeTransactionsForCurrentUser(Builder $query): Builder
    {
        if ($this->isWaliKelas()) {
            $query->whereHas('student', fn ($q) => $q->whereIn('class_id', $this->allowedClassIds()));
        }

        return $query;
    }

    protected function scopeStudentsByClass(Builder $query, ?string $classId): Builder
    {
        if ($classId && ! $this->isWaliKelas()) {
            $query->where('class_id', $classId);
        }

        return $query;
    }

    protected function scopeTransactionsByClass(Builder $query, ?string $classId): Builder
    {
        if ($classId && ! $this->isWaliKelas()) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $classId));
        }

        return $query;
    }
}

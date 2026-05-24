<?php

namespace App\Traits;

use App\Models\UserRole;

trait HasRoles
{
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('role', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('role', $roles)->exists();
    }

    public function assignRole(string $role): void
    {
        if (! $this->hasRole($role)) {
            UserRole::create([
                'user_id' => $this->id,
                'role' => $role,
            ]);
        }
    }

    public function removeRole(string $role): void
    {
        $this->roles()->where('role', $role)->delete();
    }

    public function syncRoles(array $roles): void
    {
        $this->roles()->delete();
        foreach ($roles as $role) {
            $this->assignRole($role);
        }
    }

    public function getRoleNamesAttribute(): array
    {
        return $this->roles->pluck('role')->toArray();
    }

    public function getPrimaryRoleAttribute(): ?string
    {
        return $this->roles->first()?->role;
    }
}

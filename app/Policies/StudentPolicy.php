<?php

namespace App\Policies;

use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function update(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('admin');
    }
}

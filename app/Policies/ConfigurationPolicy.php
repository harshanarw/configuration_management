<?php

namespace App\Policies;

use App\Models\{User, Configuration};

class ConfigurationPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All roles can view — FR1.3 (Auditor read-only)
    }

    public function view(User $user, Configuration $configuration): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['Admin', 'Developer']);
    }

    public function update(User $user, Configuration $configuration): bool
    {
        return in_array($user->role, ['Admin', 'Developer']);
    }

    public function delete(User $user, Configuration $configuration): bool
    {
        return $user->role === 'Admin';
    }
}

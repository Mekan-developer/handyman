<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator() || $user->isManager();
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator() || $user->isManager();
    }

    public function update(User $user, User $target): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->isManager()) {
            return ! $target->isAdministrator();
        }

        return false;
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->isManager()) {
            return ! $target->isAdministrator();
        }

        return false;
    }
}

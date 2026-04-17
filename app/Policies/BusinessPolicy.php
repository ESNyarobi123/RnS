<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    public function view(User $user, Business $business): bool
    {
        return $user->id === $business->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Business $business): bool
    {
        return $user->id === $business->user_id;
    }

    public function delete(User $user, Business $business): bool
    {
        return $user->id === $business->user_id;
    }

    public function restore(User $user, Business $business): bool
    {
        return $user->id === $business->user_id;
    }

    public function forceDelete(User $user, Business $business): bool
    {
        return false;
    }
}

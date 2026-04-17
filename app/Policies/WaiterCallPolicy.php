<?php

namespace App\Policies;

use App\Models\WaiterCall;
use App\Models\User;

class WaiterCallPolicy
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
        return $user->isManager() || $user->isWorker();
    }

    public function view(User $user, WaiterCall $waiterCall): bool
    {
        if ($user->isManager()) {
            return $user->businesses()->whereKey($waiterCall->business_id)->exists();
        }

        return $user->activeBusiness()?->id === $waiterCall->business_id;
    }

    public function update(User $user, WaiterCall $waiterCall): bool
    {
        return $this->view($user, $waiterCall);
    }
}

<?php

namespace App\Policies;

use App\Models\Tip;
use App\Models\User;

class TipPolicy
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

    public function view(User $user, Tip $tip): bool
    {
        if ($user->isManager()) {
            return $user->businesses()->whereKey($tip->business_id)->exists();
        }

        return $user->id === $tip->worker_id;
    }
}

<?php

namespace App\Policies;

use App\Models\BusinessWorker;
use App\Models\User;

class BusinessWorkerPolicy
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

    public function view(User $user, BusinessWorker $businessWorker): bool
    {
        return $user->businesses()->whereKey($businessWorker->business_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, BusinessWorker $businessWorker): bool
    {
        return $user->businesses()->whereKey($businessWorker->business_id)->exists();
    }

    public function delete(User $user, BusinessWorker $businessWorker): bool
    {
        return $user->businesses()->whereKey($businessWorker->business_id)->exists();
    }
}

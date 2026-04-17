<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;

class PayrollPolicy
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

    public function view(User $user, Payroll $payroll): bool
    {
        if ($user->isManager()) {
            return $user->businesses()->where('id', $payroll->business_id)->exists();
        }

        return $user->id === $payroll->worker_id;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Payroll $payroll): bool
    {
        return $user->businesses()->where('id', $payroll->business_id)->exists();
    }

    public function delete(User $user, Payroll $payroll): bool
    {
        return false;
    }
}

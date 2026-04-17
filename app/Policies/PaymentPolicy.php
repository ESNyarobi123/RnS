<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
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

    public function view(User $user, Payment $payment): bool
    {
        return $user->businesses()->where('id', $payment->business_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->businesses()->where('id', $payment->business_id)->exists();
    }

    public function delete(User $user, Payment $payment): bool
    {
        return false;
    }
}

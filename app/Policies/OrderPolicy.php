<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
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

    public function view(User $user, Order $order): bool
    {
        if ($user->isManager()) {
            return $user->businesses()->where('id', $order->business_id)->exists();
        }

        return $user->id === $order->worker_id;
    }

    public function create(User $user): bool
    {
        return $user->isManager() || $user->isWorker();
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->isManager()) {
            return $user->businesses()->where('id', $order->business_id)->exists();
        }

        return $user->id === $order->worker_id;
    }

    public function delete(User $user, Order $order): bool
    {
        if ($user->isManager()) {
            return $user->businesses()->where('id', $order->business_id)->exists();
        }

        return false;
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->isManager() && $user->businesses()->where('id', $order->business_id)->exists();
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\Stock;
use App\Models\User;

class StockPolicy
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

    public function view(User $user, Stock $stock): bool
    {
        return $user->businesses()->where('id', $stock->business_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Stock $stock): bool
    {
        return $user->businesses()->where('id', $stock->business_id)->exists();
    }

    public function delete(User $user, Stock $stock): bool
    {
        return $user->businesses()->where('id', $stock->business_id)->exists();
    }
}

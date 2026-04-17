<?php

namespace App\Policies;

use App\Models\Table;
use App\Models\User;

class TablePolicy
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

    public function view(User $user, Table $table): bool
    {
        return $user->businesses()->whereKey($table->business_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Table $table): bool
    {
        return $user->businesses()->whereKey($table->business_id)->exists();
    }

    public function delete(User $user, Table $table): bool
    {
        return $user->businesses()->whereKey($table->business_id)->exists();
    }
}

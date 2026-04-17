<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
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

    public function view(User $user, Category $category): bool
    {
        return $user->businesses()->where('id', $category->business_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->businesses()->where('id', $category->business_id)->exists();
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->businesses()->where('id', $category->business_id)->exists();
    }
}

<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
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

    public function view(User $user, Product $product): bool
    {
        return $user->businesses()->where('id', $product->business_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->businesses()->where('id', $product->business_id)->exists();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->businesses()->where('id', $product->business_id)->exists();
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->businesses()->where('id', $product->business_id)->exists();
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return false;
    }
}

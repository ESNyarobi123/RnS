<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
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

    public function view(User $user, Feedback $feedback): bool
    {
        if ($user->isManager()) {
            return $user->businesses()->where('id', $feedback->business_id)->exists();
        }

        return $user->id === $feedback->worker_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Feedback $feedback): bool
    {
        return $user->businesses()->where('id', $feedback->business_id)->exists();
    }
}

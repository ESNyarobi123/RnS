<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match (true) {
            $user->isAdmin() => redirect()->route('admin.dashboard'),
            $user->isManager() => redirect()->route('manager.dashboard'),
            $user->isWorker() => redirect()->route('worker.dashboard'),
            default => redirect()->route('home'),
        };
    }
}

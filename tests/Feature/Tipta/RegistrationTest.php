<?php

use App\Enums\UserRole;
use App\Models\User;

test('user can register as a worker', function () {
    $response = $this->post('/register', [
        'name' => 'Test Worker',
        'email' => 'testworker@tipta.com',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
        'role' => 'worker',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'testworker@tipta.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Worker)
        ->and($user->global_number)->toStartWith('TIP-');
});

test('user can register as a manager', function () {
    $response = $this->post('/register', [
        'name' => 'Test Manager',
        'email' => 'testmanager@tipta.com',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
        'role' => 'manager',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'testmanager@tipta.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Manager)
        ->and($user->global_number)->toBeNull();
});

test('user cannot register as admin', function () {
    $response = $this->post('/register', [
        'name' => 'Hacker Admin',
        'email' => 'hacker@tipta.com',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors('role');
    expect(User::where('email', 'hacker@tipta.com')->exists())->toBeFalse();
});

test('registration requires a role', function () {
    $response = $this->post('/register', [
        'name' => 'No Role User',
        'email' => 'norole@tipta.com',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ]);

    $response->assertSessionHasErrors('role');
});

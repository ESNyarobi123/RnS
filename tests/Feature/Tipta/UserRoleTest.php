<?php

use App\Enums\UserRole;
use App\Models\User;

test('worker gets global number on creation', function () {
    $worker = User::factory()->worker()->create();

    expect($worker->global_number)->not->toBeNull()
        ->and($worker->global_number)->toStartWith('TIP-')
        ->and(strlen($worker->global_number))->toBe(10);
});

test('manager does not get global number', function () {
    $manager = User::factory()->manager()->create();

    expect($manager->global_number)->toBeNull();
});

test('admin does not get global number', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->global_number)->toBeNull();
});

test('user role helpers work correctly', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $worker = User::factory()->worker()->create();

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->isManager())->toBeFalse()
        ->and($manager->isManager())->toBeTrue()
        ->and($manager->isWorker())->toBeFalse()
        ->and($worker->isWorker())->toBeTrue()
        ->and($worker->isAdmin())->toBeFalse();
});

test('global numbers are unique', function () {
    $workers = User::factory()->worker()->count(10)->create();

    $globalNumbers = $workers->pluck('global_number')->toArray();

    expect($globalNumbers)->toHaveCount(10)
        ->and(array_unique($globalNumbers))->toHaveCount(10);
});

test('role middleware blocks unauthorized access', function () {
    $worker = User::factory()->worker()->create();

    $this->actingAs($worker)
        ->get('/admin/dashboard')
        ->assertStatus(403);
});

test('role middleware allows authorized access', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk();
});

test('dashboard redirects admin to admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertRedirect(route('admin.dashboard'));
});

test('dashboard redirects manager to manager dashboard', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get('/dashboard')
        ->assertRedirect(route('manager.dashboard'));
});

test('dashboard redirects worker to worker dashboard', function () {
    $worker = User::factory()->worker()->create();

    $this->actingAs($worker)
        ->get('/dashboard')
        ->assertRedirect(route('worker.dashboard'));
});

test('manager cannot access admin routes', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get('/admin/dashboard')
        ->assertStatus(403);
});

test('worker cannot access manager routes', function () {
    $worker = User::factory()->worker()->create();

    $this->actingAs($worker)
        ->get('/manager/dashboard')
        ->assertStatus(403);
});

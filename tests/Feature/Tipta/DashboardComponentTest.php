<?php

use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\User;

test('manager dashboard shows create business prompt when no business', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get(route('manager.dashboard'))
        ->assertOk()
        ->assertSee('Create Your Business');
});

test('manager dashboard shows business stats when business exists', function () {
    $manager = User::factory()->manager()->create();
    $business = Business::factory()->restaurant()->create(['user_id' => $manager->id]);

    $this->actingAs($manager)
        ->get(route('manager.dashboard'))
        ->assertOk()
        ->assertSee($business->name)
        ->assertSee('Waiters');
});

test('salon manager dashboard shows stylists label', function () {
    $manager = User::factory()->manager()->create();
    Business::factory()->salon()->create(['user_id' => $manager->id]);

    $this->actingAs($manager)
        ->get(route('manager.dashboard'))
        ->assertOk()
        ->assertSee('Stylists');
});

test('worker dashboard shows global number', function () {
    $worker = User::factory()->worker()->create();

    $this->actingAs($worker)
        ->get(route('worker.dashboard'))
        ->assertOk()
        ->assertSee($worker->global_number)
        ->assertSee('Not Linked to a Business');
});

test('linked worker dashboard shows business info', function () {
    $worker = User::factory()->worker()->create();
    $business = Business::factory()->restaurant()->create();
    BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
    ]);

    $this->actingAs($worker)
        ->get(route('worker.dashboard'))
        ->assertOk()
        ->assertSee($business->name)
        ->assertSee('Waiter');
});

test('manager can access worker management page', function () {
    $manager = User::factory()->manager()->create();
    Business::factory()->restaurant()->create(['user_id' => $manager->id]);

    $this->actingAs($manager)
        ->get(route('manager.workers.index'))
        ->assertOk()
        ->assertSee('Waiters');
});

test('manager can access business creation page', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get(route('manager.business.create'))
        ->assertOk()
        ->assertSee('Create Your Business');
});

test('worker cannot access manager routes', function () {
    $worker = User::factory()->worker()->create();

    $this->actingAs($worker)
        ->get(route('manager.dashboard'))
        ->assertStatus(403);
});

test('manager cannot access worker routes', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get(route('worker.dashboard'))
        ->assertStatus(403);
});

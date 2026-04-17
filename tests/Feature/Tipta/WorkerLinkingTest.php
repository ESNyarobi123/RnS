<?php

use App\Enums\BusinessType;
use App\Enums\LinkType;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\User;

test('worker can be linked to a business permanently', function () {
    $business = Business::factory()->restaurant()->create();
    $worker = User::factory()->worker()->create();

    $link = BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
        'link_type' => LinkType::Permanent,
    ]);

    expect($worker->isLinkedToBusiness())->toBeTrue()
        ->and($worker->activeBusiness()->id)->toBe($business->id)
        ->and($link->isExpired())->toBeFalse();
});

test('worker can be linked temporarily', function () {
    $business = Business::factory()->salon()->create();
    $worker = User::factory()->worker()->create();

    $link = BusinessWorker::factory()->temporary(30)->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
    ]);

    expect($link->link_type)->toBe(LinkType::Temporary)
        ->and($link->expires_at)->not->toBeNull()
        ->and($link->isExpired())->toBeFalse();
});

test('temporary link can expire', function () {
    $link = BusinessWorker::factory()->create([
        'link_type' => LinkType::Temporary,
        'expires_at' => now()->subDay(),
    ]);

    expect($link->isExpired())->toBeTrue();
});

test('worker can be unlinked from business', function () {
    $business = Business::factory()->create();
    $worker = User::factory()->worker()->create();

    $link = BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
    ]);

    $link->unlink();

    expect($link->fresh()->is_active)->toBeFalse()
        ->and($link->fresh()->unlinked_at)->not->toBeNull()
        ->and($worker->fresh()->isLinkedToBusiness())->toBeFalse();
});

test('worker title changes based on linked business type', function () {
    $restaurant = Business::factory()->restaurant()->create();
    $salon = Business::factory()->salon()->create();
    $worker = User::factory()->worker()->create();

    expect($worker->workerTitle())->toBe('Worker');

    BusinessWorker::factory()->create([
        'business_id' => $restaurant->id,
        'worker_id' => $worker->id,
    ]);

    expect($worker->fresh()->workerTitle())->toBe('Waiter');

    // Unlink from restaurant
    $worker->activeBusinessLink()->unlink();

    // Link to salon
    BusinessWorker::factory()->create([
        'business_id' => $salon->id,
        'worker_id' => $worker->id,
    ]);

    expect($worker->fresh()->workerTitle())->toBe('Stylist');
});

test('unlinked worker reverts to generic worker title', function () {
    $business = Business::factory()->restaurant()->create();
    $worker = User::factory()->worker()->create();

    $link = BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
    ]);

    expect($worker->fresh()->workerTitle())->toBe('Waiter');

    $link->unlink();

    expect($worker->fresh()->workerTitle())->toBe('Worker');
});

test('cannot link worker twice to same business while active', function () {
    $business = Business::factory()->create();
    $worker = User::factory()->worker()->create();

    BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
    ]);

    expect(fn () => BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
    ]))->toThrow(\RuntimeException::class, 'Worker is already actively linked to this business.');
});

test('can re-link worker after unlinking', function () {
    $business = Business::factory()->create();
    $worker = User::factory()->worker()->create();

    $link = BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
    ]);
    $link->unlink();

    $newLink = BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
    ]);

    expect($newLink->is_active)->toBeTrue();
});

test('business shows active worker links', function () {
    $business = Business::factory()->create();
    BusinessWorker::factory()->count(3)->create(['business_id' => $business->id]);
    BusinessWorker::factory()->unlinked()->count(2)->create(['business_id' => $business->id]);

    expect($business->workerLinks)->toHaveCount(5)
        ->and($business->activeWorkerLinks)->toHaveCount(3);
});

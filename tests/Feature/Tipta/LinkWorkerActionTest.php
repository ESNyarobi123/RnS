<?php

use App\Actions\Business\LinkWorker;
use App\Actions\Business\UnlinkWorker;
use App\Enums\LinkType;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\User;
use Illuminate\Validation\ValidationException;

test('link worker by global number', function () {
    $business = Business::factory()->restaurant()->create();
    $worker = User::factory()->worker()->create();

    $link = (new LinkWorker)->execute($business, [
        'global_number' => $worker->global_number,
    ]);

    expect($link)->toBeInstanceOf(BusinessWorker::class)
        ->and($link->worker_id)->toBe($worker->id)
        ->and($link->business_id)->toBe($business->id)
        ->and($link->is_active)->toBeTrue()
        ->and($link->link_type)->toBe(LinkType::Permanent);
});

test('link worker temporarily', function () {
    $business = Business::factory()->salon()->create();
    $worker = User::factory()->worker()->create();

    $link = (new LinkWorker)->execute($business, [
        'global_number' => $worker->global_number,
        'link_type' => 'temporary',
        'expires_at' => now()->addDays(14)->toDateString(),
    ]);

    expect($link->link_type)->toBe(LinkType::Temporary)
        ->and($link->expires_at)->not->toBeNull();
});

test('link worker fails with invalid global number', function () {
    $business = Business::factory()->create();

    (new LinkWorker)->execute($business, [
        'global_number' => 'TIP-999999',
    ]);
})->throws(ValidationException::class);

test('link worker fails for non-worker user', function () {
    $business = Business::factory()->create();
    $manager = User::factory()->manager()->create();

    (new LinkWorker)->execute($business, [
        'global_number' => $manager->global_number ?? 'TIP-000000',
    ]);
})->throws(ValidationException::class);

test('link worker fails if already actively linked', function () {
    $business = Business::factory()->create();
    $worker = User::factory()->worker()->create();

    (new LinkWorker)->execute($business, [
        'global_number' => $worker->global_number,
    ]);

    (new LinkWorker)->execute($business, [
        'global_number' => $worker->global_number,
    ]);
})->throws(ValidationException::class);

test('unlink worker successfully', function () {
    $business = Business::factory()->create();
    $worker = User::factory()->worker()->create();

    BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
    ]);

    $link = (new UnlinkWorker)->execute($business, $worker->id);

    expect($link->is_active)->toBeFalse()
        ->and($link->unlinked_at)->not->toBeNull();
});

test('unlink worker fails if not linked', function () {
    $business = Business::factory()->create();
    $worker = User::factory()->worker()->create();

    (new UnlinkWorker)->execute($business, $worker->id);
})->throws(ValidationException::class);

test('linked worker title changes to waiter for restaurant', function () {
    $business = Business::factory()->restaurant()->create();
    $worker = User::factory()->worker()->create();

    expect($worker->workerTitle())->toBe('Worker');

    (new LinkWorker)->execute($business, [
        'global_number' => $worker->global_number,
    ]);

    expect($worker->fresh()->workerTitle())->toBe('Waiter');
});

test('linked worker title changes to stylist for salon', function () {
    $business = Business::factory()->salon()->create();
    $worker = User::factory()->worker()->create();

    (new LinkWorker)->execute($business, [
        'global_number' => $worker->global_number,
    ]);

    expect($worker->fresh()->workerTitle())->toBe('Stylist');
});

test('manager can link then unlink then re-link worker', function () {
    $business = Business::factory()->restaurant()->create();
    $worker = User::factory()->worker()->create();

    $link = (new LinkWorker)->execute($business, [
        'global_number' => $worker->global_number,
    ]);
    expect($worker->fresh()->workerTitle())->toBe('Waiter');

    (new UnlinkWorker)->execute($business, $worker->id);
    expect($worker->fresh()->workerTitle())->toBe('Worker');

    $newLink = (new LinkWorker)->execute($business, [
        'global_number' => $worker->global_number,
    ]);
    expect($worker->fresh()->workerTitle())->toBe('Waiter')
        ->and($newLink->id)->not->toBe($link->id);
});

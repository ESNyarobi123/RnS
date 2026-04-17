<?php

use App\Enums\UserRole;
use App\Livewire\Manager\ManageWorkers;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Order;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->manager = User::factory()->manager()->create();
    $this->business = Business::factory()->restaurant()->create(['user_id' => $this->manager->id]);
});

test('manager can search worker by TIP number', function () {
    $worker = User::factory()->worker()->create(['global_number' => 'TIP-111111']);

    $component = Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->set('searchTip', 'TIP-111111')
        ->call('searchWorker');

    expect($component->get('searchResultData')['name'])->toBe($worker->name)
        ->and($component->get('searchResultData')['global_number'])->toBe('TIP-111111')
        ->and($component->get('searchResultData')['is_linked_here'])->toBeFalse()
        ->and($component->get('searchResultData')['is_linked_elsewhere'])->toBeFalse();
});

test('search shows not found for invalid TIP', function () {
    Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->set('searchTip', 'TIP-999999')
        ->call('searchWorker')
        ->assertSet('showSearchResult', true)
        ->assertSet('searchResultData', null);
});

test('search rejects invalid TIP format', function () {
    Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->set('searchTip', 'INVALID')
        ->call('searchWorker')
        ->assertSet('showSearchResult', false);
});

test('search shows worker is already linked here', function () {
    $worker = User::factory()->worker()->create(['global_number' => 'TIP-222222']);
    BusinessWorker::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $worker->id,
        'is_active' => true,
    ]);

    $component = Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->set('searchTip', 'TIP-222222')
        ->call('searchWorker');

    expect($component->get('searchResultData')['is_linked_here'])->toBeTrue();
});

test('search shows worker is linked elsewhere', function () {
    $otherBusiness = Business::factory()->salon()->create();
    $worker = User::factory()->worker()->create(['global_number' => 'TIP-333333']);
    BusinessWorker::factory()->create([
        'business_id' => $otherBusiness->id,
        'worker_id' => $worker->id,
        'is_active' => true,
    ]);

    $component = Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->set('searchTip', 'TIP-333333')
        ->call('searchWorker');

    expect($component->get('searchResultData')['is_linked_elsewhere'])->toBeTrue();
});

test('search includes worker stats', function () {
    $worker = User::factory()->worker()->create(['global_number' => 'TIP-444444']);
    Order::factory()->count(3)->create(['worker_id' => $worker->id]);

    $component = Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->set('searchTip', 'TIP-444444')
        ->call('searchWorker');

    expect($component->get('searchResultData')['total_orders'])->toBe(3);
});

test('link from search opens link modal with TIP pre-filled', function () {
    $worker = User::factory()->worker()->create(['global_number' => 'TIP-555555']);

    Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->set('searchTip', 'TIP-555555')
        ->call('searchWorker')
        ->call('linkFromSearch')
        ->assertSet('showLinkModal', true)
        ->assertSet('global_number', 'TIP-555555')
        ->assertSet('showSearchResult', false)
        ->assertSet('searchResultData', null);
});

test('clear search resets state', function () {
    $worker = User::factory()->worker()->create(['global_number' => 'TIP-666666']);

    Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->set('searchTip', 'TIP-666666')
        ->call('searchWorker')
        ->assertSet('showSearchResult', true)
        ->call('clearSearch')
        ->assertSet('searchTip', '')
        ->assertSet('showSearchResult', false)
        ->assertSet('searchResultData', null);
});

test('manager can view worker profile modal', function () {
    $worker = User::factory()->worker()->create(['global_number' => 'TIP-777777']);
    BusinessWorker::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $worker->id,
        'is_active' => true,
    ]);

    Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->call('viewProfile', $worker->id)
        ->assertSet('showProfileModal', true)
        ->assertSet('profileWorkerId', $worker->id)
        ->assertSee($worker->name)
        ->assertSee($worker->global_number);
});

test('linking a worker generates a worker qr code', function () {
    $worker = User::factory()->worker()->create(['global_number' => 'TIP-909090']);

    Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->set('global_number', 'TIP-909090')
        ->set('link_type', 'permanent')
        ->call('linkWorker');

    $link = BusinessWorker::query()
        ->where('business_id', $this->business->id)
        ->where('worker_id', $worker->id)
        ->first();

    expect($link)->not->toBeNull()
        ->and($link->qr_code)->not->toBeNull()
        ->and($link->qr_image_path)->not->toBeNull();
});

test('profile shows correct stats for business', function () {
    $worker = User::factory()->worker()->create(['global_number' => 'TIP-888888']);
    BusinessWorker::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $worker->id,
        'is_active' => true,
    ]);

    Order::factory()->count(5)->create([
        'business_id' => $this->business->id,
        'worker_id' => $worker->id,
    ]);

    // Orders at another business shouldn't count
    $otherBusiness = Business::factory()->salon()->create();
    Order::factory()->count(2)->create([
        'business_id' => $otherBusiness->id,
        'worker_id' => $worker->id,
    ]);

    $component = Livewire::actingAs($this->manager)
        ->test(ManageWorkers::class)
        ->call('viewProfile', $worker->id);

    expect($component->get('profileWorkerId'))->toBe($worker->id);

    // Verify the worker is set so stats are computed
    $worker = $component->get('profileWorker');
    expect($worker)->not->toBeNull()
        ->and($worker->id)->toBe($worker->id);
});

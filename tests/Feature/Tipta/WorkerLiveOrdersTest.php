<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Livewire\Worker\LiveOrders;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->worker = User::factory()->worker()->create();
    $this->business = Business::factory()->restaurant()->create();
    BusinessWorker::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'is_active' => true,
    ]);
});

test('worker can access live orders page', function () {
    $this->actingAs($this->worker)
        ->get(route('worker.live-orders'))
        ->assertOk();
});

test('worker sees live orders kanban board', function () {
    Livewire::actingAs($this->worker)
        ->test(LiveOrders::class)
        ->assertOk()
        ->assertSee('Live Orders');
});

test('worker sees only their assigned orders in columns', function () {
    $myOrder = Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Pending,
        'customer_name' => 'My Customer',
    ]);

    $otherWorker = User::factory()->worker()->create();
    $otherOrder = Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $otherWorker->id,
        'status' => OrderStatus::Pending,
        'customer_name' => 'Not My Customer',
    ]);

    Livewire::actingAs($this->worker)
        ->test(LiveOrders::class)
        ->assertSee('My Customer')
        ->assertDontSee('Not My Customer');
});

test('orders appear in correct kanban columns', function () {
    $pending = Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Pending,
    ]);

    $preparing = Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Preparing,
    ]);

    $completed = Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Completed,
        'completed_at' => now(),
    ]);

    $component = Livewire::actingAs($this->worker)->test(LiveOrders::class);

    expect($component->get('pendingOrders'))->toHaveCount(1)
        ->and($component->get('preparingOrders'))->toHaveCount(1)
        ->and($component->get('completedOrders'))->toHaveCount(1);
});

test('today stats show correct counts', function () {
    Order::factory()->count(3)->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Pending,
    ]);

    Order::factory()->count(2)->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Completed,
        'completed_at' => now(),
        'total' => 10000,
    ]);

    $component = Livewire::actingAs($this->worker)->test(LiveOrders::class);

    expect($component->get('todayStats')['total'])->toBe(5)
        ->and($component->get('todayStats')['completed'])->toBe(2)
        ->and($component->get('todayStats')['revenue'])->toBe(20000.0);
});

test('worker can view order details', function () {
    $order = Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Pending,
        'customer_name' => 'Detail Customer',
    ]);

    Livewire::actingAs($this->worker)
        ->test(LiveOrders::class)
        ->call('viewOrder', $order->id)
        ->assertSet('showOrderDetail', true)
        ->assertSet('selectedOrderId', $order->id)
        ->assertSee('Detail Customer');
});

test('worker cannot view other workers order detail', function () {
    $otherWorker = User::factory()->worker()->create();
    $order = Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $otherWorker->id,
        'status' => OrderStatus::Pending,
    ]);

    $component = Livewire::actingAs($this->worker)
        ->test(LiveOrders::class)
        ->call('viewOrder', $order->id);

    expect($component->get('selectedOrder'))->toBeNull();
});

test('salon uses alternative column labels', function () {
    $salon = Business::factory()->salon()->create();
    BusinessWorker::factory()->create([
        'business_id' => $salon->id,
        'worker_id' => $this->worker->id,
        'is_active' => true,
    ]);

    // Unlink from restaurant first
    BusinessWorker::where('business_id', $this->business->id)
        ->where('worker_id', $this->worker->id)
        ->update(['is_active' => false]);

    Livewire::actingAs($this->worker)
        ->test(LiveOrders::class)
        ->assertSee('In Progress')
        ->assertSee('Ready');
});

test('unlinked worker sees empty state', function () {
    $unlinked = User::factory()->worker()->create();

    Livewire::actingAs($unlinked)
        ->test(LiveOrders::class)
        ->assertSee('Not Linked to a Business');
});

test('non-worker cannot access worker live orders', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get(route('worker.live-orders'))
        ->assertForbidden();
});

test('completed orders only show todays', function () {
    // Yesterday's completed order
    Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Completed,
        'completed_at' => now()->subDay(),
    ]);

    // Today's completed order
    Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Completed,
        'completed_at' => now(),
    ]);

    $component = Livewire::actingAs($this->worker)->test(LiveOrders::class);

    expect($component->get('completedOrders'))->toHaveCount(1);
});

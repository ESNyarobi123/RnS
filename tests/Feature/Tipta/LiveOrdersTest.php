<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Livewire\Manager\LiveOrders;
use App\Models\Business;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->manager = User::factory()->manager()->create();
    $this->business = Business::factory()->restaurant()->create(['user_id' => $this->manager->id]);
    $this->category = Category::factory()->create(['business_id' => $this->business->id]);
    $this->product = Product::factory()->create([
        'business_id' => $this->business->id,
        'category_id' => $this->category->id,
        'price' => 5000,
        'is_active' => true,
    ]);
});

test('manager can view live orders page', function () {
    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->assertOk()
        ->assertSee('Live Orders');
});

test('manager can open create order modal', function () {
    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('openCreateOrder')
        ->assertSet('showCreateModal', true)
        ->assertSet('cartItems', []);
});

test('manager can add items to cart', function () {
    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('openCreateOrder')
        ->call('addToCart', $this->product->id)
        ->assertCount('cartItems', 1);
});

test('adding same product increments quantity', function () {
    $component = Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('openCreateOrder')
        ->call('addToCart', $this->product->id)
        ->call('addToCart', $this->product->id);

    expect($component->get('cartItems')[0]['quantity'])->toBe(2);
});

test('manager can remove item from cart', function () {
    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('openCreateOrder')
        ->call('addToCart', $this->product->id)
        ->call('removeFromCart', 0)
        ->assertCount('cartItems', 0);
});

test('manager can create order', function () {
    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('openCreateOrder')
        ->set('customerName', 'John Doe')
        ->set('customerPhone', '255700000000')
        ->call('addToCart', $this->product->id)
        ->call('createOrder')
        ->assertSet('showCreateModal', false);

    $order = $this->business->orders()->latest()->first();
    expect($order)->not->toBeNull()
        ->and($order->customer_name)->toBe('John Doe')
        ->and($order->customer_phone)->toBe('255700000000')
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->items)->toHaveCount(1)
        ->and((float) $order->total)->toBe(5000.0);
});

test('customer name is required to create order', function () {
    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('openCreateOrder')
        ->set('customerName', '')
        ->call('addToCart', $this->product->id)
        ->call('createOrder')
        ->assertHasErrors(['customerName']);
});

test('cart must have items to create order', function () {
    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('openCreateOrder')
        ->set('customerName', 'Test')
        ->call('createOrder')
        ->assertHasErrors(['cartItems']);
});

test('manager can move order from pending to preparing', function () {
    $order = Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Pending,
    ]);

    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('moveToStatus', $order->id, 'preparing');

    expect($order->fresh()->status)->toBe(OrderStatus::Preparing);
});

test('manager can move order from preparing to served', function () {
    $order = Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Preparing,
    ]);

    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('moveToStatus', $order->id, 'served');

    expect($order->fresh()->status)->toBe(OrderStatus::Served);
});

test('manager can complete order', function () {
    $order = Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Served,
    ]);

    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('moveToStatus', $order->id, 'completed');

    expect($order->fresh()->status)->toBe(OrderStatus::Completed)
        ->and($order->fresh()->completed_at)->not->toBeNull();
});

test('manager can cancel order', function () {
    $order = Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Pending,
    ]);

    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('cancelOrder', $order->id);

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('manager can process cash payment', function () {
    $order = Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Served,
        'total' => 10000,
    ]);

    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('openPayment', $order->id)
        ->assertSet('showPaymentModal', true)
        ->set('paymentMethod', 'cash')
        ->call('processPayment');

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->payments)->toHaveCount(1)
        ->and($order->payments->first()->method)->toBe(PaymentMethod::Cash)
        ->and($order->payments->first()->status)->toBe(PaymentStatus::Completed);
});

test('manager can mark order as paid manually', function () {
    $order = Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Served,
        'total' => 8000,
    ]);

    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->call('markPaidManual', $order->id);

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->payments)->toHaveCount(1);
});

test('kanban shows orders in correct columns', function () {
    Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Pending,
        'customer_name' => 'Pending Customer',
    ]);
    Order::factory()->create([
        'business_id' => $this->business->id,
        'status' => OrderStatus::Preparing,
        'customer_name' => 'Preparing Customer',
    ]);

    Livewire::actingAs($this->manager)
        ->test(LiveOrders::class)
        ->assertSee('Pending Customer')
        ->assertSee('Preparing Customer');
});

test('salon shows correct live labels', function () {
    $salonManager = User::factory()->manager()->create();
    $salon = Business::factory()->salon()->create(['user_id' => $salonManager->id]);

    Livewire::actingAs($salonManager)
        ->test(LiveOrders::class)
        ->assertSee('In Progress')
        ->assertSee('Ready');
});

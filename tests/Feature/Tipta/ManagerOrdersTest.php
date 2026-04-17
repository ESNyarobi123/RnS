<?php

use App\Enums\OrderStatus;
use App\Livewire\Manager\ManageOrders;
use App\Models\Business;
use App\Models\Order;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->manager = User::factory()->manager()->create();
    $this->business = Business::factory()->restaurant()->create(['user_id' => $this->manager->id]);
});

test('manager can view orders page', function () {
    Livewire::actingAs($this->manager)
        ->test(ManageOrders::class)
        ->assertOk()
        ->assertSee('Orders');
});

test('manager sees their business orders', function () {
    $order = Order::factory()->create(['business_id' => $this->business->id]);
    $otherOrder = Order::factory()->create();

    Livewire::actingAs($this->manager)
        ->test(ManageOrders::class)
        ->assertSee($order->order_number)
        ->assertDontSee($otherOrder->order_number);
});

test('manager can search orders', function () {
    $order = Order::factory()->create(['business_id' => $this->business->id, 'customer_name' => 'John Doe']);
    $other = Order::factory()->create(['business_id' => $this->business->id, 'customer_name' => 'Jane Smith']);

    Livewire::actingAs($this->manager)
        ->test(ManageOrders::class)
        ->set('search', 'John')
        ->assertSee($order->order_number)
        ->assertDontSee($other->order_number);
});

test('manager can filter orders by status', function () {
    $pending = Order::factory()->create(['business_id' => $this->business->id, 'status' => OrderStatus::Pending]);
    $completed = Order::factory()->completed()->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->manager)
        ->test(ManageOrders::class)
        ->set('status', 'completed')
        ->assertSee($completed->order_number)
        ->assertDontSee($pending->order_number);
});

test('manager can update order status', function () {
    $order = Order::factory()->create(['business_id' => $this->business->id, 'status' => OrderStatus::Pending]);

    Livewire::actingAs($this->manager)
        ->test(ManageOrders::class)
        ->call('updateOrderStatus', $order->id, 'confirmed');

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('manager can complete order', function () {
    $order = Order::factory()->create(['business_id' => $this->business->id, 'status' => OrderStatus::InProgress]);

    Livewire::actingAs($this->manager)
        ->test(ManageOrders::class)
        ->call('updateOrderStatus', $order->id, 'completed');

    expect($order->fresh()->status)->toBe(OrderStatus::Completed)
        ->and($order->fresh()->completed_at)->not->toBeNull();
});

test('manager can view order details', function () {
    $order = Order::factory()->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->manager)
        ->test(ManageOrders::class)
        ->call('viewOrder', $order->id)
        ->assertSet('selectedOrderId', $order->id)
        ->assertSet('showDetailModal', true);
});

test('order summary shows correct counts', function () {
    Order::factory()->count(2)->create(['business_id' => $this->business->id, 'status' => OrderStatus::Pending]);
    Order::factory()->create(['business_id' => $this->business->id, 'status' => OrderStatus::InProgress]);
    Order::factory()->completed()->create(['business_id' => $this->business->id]);

    $component = Livewire::actingAs($this->manager)->test(ManageOrders::class);

    expect($component->get('orderSummary')['total'])->toBe(4)
        ->and($component->get('orderSummary')['pending'])->toBe(2)
        ->and($component->get('orderSummary')['in_progress'])->toBe(1);
});

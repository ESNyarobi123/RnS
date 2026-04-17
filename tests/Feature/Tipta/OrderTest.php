<?php

use App\Enums\OrderStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

test('order gets auto-generated order number', function () {
    $order = Order::factory()->create();

    expect($order->order_number)->not->toBeNull()
        ->and($order->order_number)->toStartWith('ORD-');
});

test('order can have items', function () {
    $business = Business::factory()->restaurant()->create();
    $category = Category::factory()->create(['business_id' => $business->id]);
    $product = Product::factory()->create([
        'business_id' => $business->id,
        'category_id' => $category->id,
        'price' => 5000,
    ]);

    $order = Order::factory()->create(['business_id' => $business->id]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 5000,
    ]);

    expect($order->items)->toHaveCount(1)
        ->and($order->items->first()->total_price)->toBe('10000.00');
});

test('order total recalculates when items change', function () {
    $business = Business::factory()->restaurant()->create();
    $category = Category::factory()->create(['business_id' => $business->id]);
    $product = Product::factory()->create([
        'business_id' => $business->id,
        'category_id' => $category->id,
        'price' => 3000,
    ]);

    $order = Order::factory()->create([
        'business_id' => $business->id,
        'subtotal' => 0,
        'total' => 0,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 3000,
    ]);

    $order->refresh();
    expect($order->subtotal)->toBe('9000.00')
        ->and($order->total)->toBe('9000.00');
});

test('order can be marked as completed', function () {
    $order = Order::factory()->create();

    $order->markCompleted();

    expect($order->fresh()->status)->toBe(OrderStatus::Completed)
        ->and($order->fresh()->completed_at)->not->toBeNull();
});

test('order status defaults to pending', function () {
    $order = Order::factory()->create();

    expect($order->status)->toBe(OrderStatus::Pending);
});

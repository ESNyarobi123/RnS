<?php

use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

test('admin can view any business', function () {
    $admin = User::factory()->admin()->create();
    $business = Business::factory()->create();

    expect($admin->can('view', $business))->toBeTrue();
});

test('manager can only view own business', function () {
    $manager = User::factory()->manager()->create();
    $ownBusiness = Business::factory()->create(['user_id' => $manager->id]);
    $otherBusiness = Business::factory()->create();

    expect($manager->can('view', $ownBusiness))->toBeTrue()
        ->and($manager->can('view', $otherBusiness))->toBeFalse();
});

test('worker cannot view businesses', function () {
    $worker = User::factory()->worker()->create();
    $business = Business::factory()->create();

    expect($worker->can('view', $business))->toBeFalse();
});

test('manager can view orders of own business', function () {
    $manager = User::factory()->manager()->create();
    $business = Business::factory()->create(['user_id' => $manager->id]);
    $order = Order::factory()->create(['business_id' => $business->id]);
    $otherOrder = Order::factory()->create();

    expect($manager->can('view', $order))->toBeTrue()
        ->and($manager->can('view', $otherOrder))->toBeFalse();
});

test('worker can view own assigned orders', function () {
    $worker = User::factory()->worker()->create();
    $order = Order::factory()->create(['worker_id' => $worker->id]);
    $otherOrder = Order::factory()->create();

    expect($worker->can('view', $order))->toBeTrue()
        ->and($worker->can('view', $otherOrder))->toBeFalse();
});

test('manager can manage products of own business', function () {
    $manager = User::factory()->manager()->create();
    $business = Business::factory()->create(['user_id' => $manager->id]);
    $product = Product::factory()->create(['business_id' => $business->id]);
    $otherProduct = Product::factory()->create();

    expect($manager->can('update', $product))->toBeTrue()
        ->and($manager->can('delete', $product))->toBeTrue()
        ->and($manager->can('update', $otherProduct))->toBeFalse();
});

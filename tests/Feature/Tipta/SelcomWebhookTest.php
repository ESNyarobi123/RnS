<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Business;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Tip;
use App\Models\User;

test('webhook completes payment and order on COMPLETED status', function () {
    $business = Business::factory()->restaurant()->create();
    $order = Order::factory()->create([
        'business_id' => $business->id,
        'status' => OrderStatus::Served,
        'order_number' => 'ORD-TEST-0001',
    ]);
    Payment::create([
        'business_id' => $business->id,
        'order_id' => $order->id,
        'amount' => $order->total,
        'method' => PaymentMethod::MobileMoney,
        'status' => PaymentStatus::Pending,
        'reference' => $order->order_number,
    ]);

    $this->post(route('webhooks.selcom'), [
        'order_id' => 'ORD-TEST-0001',
        'payment_status' => 'COMPLETED',
        'reference' => 'SELCOM-REF-123',
    ])->assertOk();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->completed_at)->not->toBeNull();

    $payment = $order->payments()->first();
    expect($payment->status)->toBe(PaymentStatus::Completed)
        ->and($payment->reference)->toBe('SELCOM-REF-123')
        ->and($payment->paid_at)->not->toBeNull();
});

test('webhook returns ok for unknown order', function () {
    $this->post(route('webhooks.selcom'), [
        'order_id' => 'NONEXISTENT-ORDER',
        'payment_status' => 'COMPLETED',
    ])->assertOk()
        ->assertJson(['status' => 'not_found']);
});

test('webhook ignores non-COMPLETED status', function () {
    $business = Business::factory()->restaurant()->create();
    $order = Order::factory()->create([
        'business_id' => $business->id,
        'status' => OrderStatus::Served,
        'order_number' => 'ORD-TEST-0002',
    ]);

    $this->post(route('webhooks.selcom'), [
        'order_id' => 'ORD-TEST-0002',
        'payment_status' => 'PENDING',
    ])->assertOk();

    expect($order->fresh()->status)->toBe(OrderStatus::Served);
});

test('webhook is idempotent on double call', function () {
    $business = Business::factory()->restaurant()->create();
    $order = Order::factory()->create([
        'business_id' => $business->id,
        'status' => OrderStatus::Served,
        'order_number' => 'ORD-TEST-0003',
    ]);
    Payment::create([
        'business_id' => $business->id,
        'order_id' => $order->id,
        'amount' => $order->total,
        'method' => PaymentMethod::MobileMoney,
        'status' => PaymentStatus::Pending,
        'reference' => $order->order_number,
    ]);

    $this->post(route('webhooks.selcom'), [
        'order_id' => 'ORD-TEST-0003',
        'payment_status' => 'COMPLETED',
    ])->assertOk();

    $this->post(route('webhooks.selcom'), [
        'order_id' => 'ORD-TEST-0003',
        'payment_status' => 'COMPLETED',
    ])->assertOk();

    expect($order->payments()->count())->toBe(1)
        ->and($order->fresh()->status)->toBe(OrderStatus::Completed);
});

test('webhook completes tip payment and creates tip record', function () {
    $business = Business::factory()->restaurant()->create();
    $worker = User::factory()->worker()->create();

    $payment = Payment::factory()->create([
        'business_id' => $business->id,
        'order_id' => null,
        'amount' => 5000,
        'method' => PaymentMethod::MobileMoney,
        'status' => PaymentStatus::Pending,
        'provider' => 'selcom',
        'provider_order_id' => 'TIP-WEBHOOK-0001',
        'reference' => 'TIP-WEBHOOK-0001',
        'customer_phone' => '255700999888',
        'customer_name' => 'Webhook Customer',
        'metadata' => [
            'purpose' => 'tip',
            'worker_user_id' => $worker->id,
            'source' => 'whatsapp',
        ],
    ]);

    $this->post(route('webhooks.selcom'), [
        'order_id' => 'TIP-WEBHOOK-0001',
        'payment_status' => 'COMPLETED',
        'reference' => 'SELCOM-TIP-WEBHOOK',
    ])->assertOk();

    $tip = Tip::query()->latest('id')->first();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Completed)
        ->and($payment->fresh()->reference)->toBe('SELCOM-TIP-WEBHOOK')
        ->and($tip)->not->toBeNull()
        ->and($tip->payment_id)->toBe($payment->id)
        ->and($tip->worker_id)->toBe($worker->id)
        ->and((float) $tip->amount)->toBe(5000.0);
});

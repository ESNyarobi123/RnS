<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\BotSetting;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function botHeaders(string $secret): array
{
    return [
        'X-Bot-Secret' => $secret,
    ];
}

function fakeSelcomSuccessfulPush(): void
{
    Http::fake([
        'https://apigw.selcommobile.com/sandbox/v1/checkout/create-order-minimal' => Http::response([
            'resultcode' => '000',
            'data' => [
                'message' => 'created',
            ],
        ], 200),
        'https://apigw.selcommobile.com/sandbox/v1/checkout/wallet-payment' => Http::response([
            'result' => 'SUCCESS',
            'data' => [
                'message' => 'pushed',
            ],
        ], 200),
    ]);
}

beforeEach(function () {
    $this->secret = str_repeat('b', 64);

    BotSetting::factory()->create([
        'phone_number' => '255700000000',
        'secret_key' => $this->secret,
        'is_active' => true,
    ]);
});

test('bot can initiate mobile money payment for an order using business credentials', function () {
    fakeSelcomSuccessfulPush();

    $business = Business::factory()->restaurant()->create();
    PaymentSetting::factory()->create([
        'business_id' => $business->id,
        'provider' => 'selcom',
        'api_key' => 'test-key',
        'api_secret' => 'test-secret',
        'config' => ['vendor' => 'VENDOR123', 'environment' => 'sandbox'],
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'business_id' => $business->id,
        'order_number' => 'ORD-BOT-1001',
        'total' => 12000,
        'status' => OrderStatus::Served,
        'customer_name' => 'Bot Customer',
        'customer_phone' => '255700111222',
    ]);

    $this->withHeaders(botHeaders($this->secret))
        ->postJson('/api/bot/payments/initiate', [
            'type' => 'order',
            'order_id' => $order->id,
            'amount' => 12000,
            'method' => 'mobile_money',
            'customer_phone' => '255700111222',
            'customer_name' => 'Bot Customer',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('provider', 'selcom');

    $payment = Payment::query()->latest('id')->first();

    expect($payment)->not->toBeNull()
        ->and($payment->business_id)->toBe($business->id)
        ->and($payment->order_id)->toBe($order->id)
        ->and($payment->method)->toBe(PaymentMethod::MobileMoney)
        ->and($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->provider)->toBe('selcom')
        ->and($payment->provider_order_id)->not->toBeNull()
        ->and($payment->customer_phone)->toBe('255700111222')
        ->and(data_get($payment->metadata, 'purpose'))->toBe('order');
});

test('bot can initiate mobile money tip payment without creating tip before success', function () {
    fakeSelcomSuccessfulPush();

    $business = Business::factory()->restaurant()->create();
    PaymentSetting::factory()->create([
        'business_id' => $business->id,
        'provider' => 'selcom',
        'api_key' => 'test-key',
        'api_secret' => 'test-secret',
        'config' => ['vendor' => 'VENDOR123', 'environment' => 'sandbox'],
        'is_active' => true,
    ]);

    $worker = User::factory()->worker()->create();
    $workerLink = BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
        'is_active' => true,
    ]);

    $this->withHeaders(botHeaders($this->secret))
        ->postJson('/api/bot/payments/initiate', [
            'type' => 'tip',
            'business_id' => $business->id,
            'worker_id' => $workerLink->id,
            'amount' => 7000,
            'method' => 'mobile_money',
            'customer_phone' => '255700222333',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('provider', 'selcom');

    $payment = Payment::query()->latest('id')->first();

    expect($payment)->not->toBeNull()
        ->and($payment->order_id)->toBeNull()
        ->and($payment->status)->toBe(PaymentStatus::Pending)
        ->and(data_get($payment->metadata, 'purpose'))->toBe('tip')
        ->and(data_get($payment->metadata, 'worker_user_id'))->toBe($worker->id);

    expect(Tip::query()->count())->toBe(0);
});

test('checking payment status completes order payment and updates order when fully paid', function () {
    $business = Business::factory()->restaurant()->create();
    PaymentSetting::factory()->create([
        'business_id' => $business->id,
        'provider' => 'selcom',
        'api_key' => 'test-key',
        'api_secret' => 'test-secret',
        'config' => ['vendor' => 'VENDOR123', 'environment' => 'sandbox'],
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'business_id' => $business->id,
        'order_number' => 'ORD-BOT-2001',
        'total' => 9000,
        'status' => OrderStatus::Served,
    ]);

    $payment = Payment::factory()->create([
        'business_id' => $business->id,
        'order_id' => $order->id,
        'amount' => 9000,
        'method' => PaymentMethod::MobileMoney,
        'status' => PaymentStatus::Pending,
        'provider' => 'selcom',
        'provider_order_id' => 'PAY-ORDER-2001',
        'reference' => 'ORD-BOT-2001',
        'metadata' => ['purpose' => 'order'],
    ]);

    Http::fake([
        'https://apigw.selcommobile.com/sandbox/v1/checkout/order-status*' => Http::response([
            'result' => 'SUCCESS',
            'data' => [
                'payment_status' => 'COMPLETED',
                'reference' => 'SELCOM-ORDER-123',
            ],
        ], 200),
    ]);

    $this->withHeaders(botHeaders($this->secret))
        ->getJson("/api/bot/payments/{$payment->id}/status")
        ->assertOk()
        ->assertJsonPath('status', 'completed')
        ->assertJsonPath('remaining_amount', 0);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Completed)
        ->and($payment->fresh()->reference)->toBe('SELCOM-ORDER-123')
        ->and($order->fresh()->status)->toBe(OrderStatus::Completed);
});

test('checking payment status completes tip payment and creates the tip record', function () {
    $business = Business::factory()->restaurant()->create();
    PaymentSetting::factory()->create([
        'business_id' => $business->id,
        'provider' => 'selcom',
        'api_key' => 'test-key',
        'api_secret' => 'test-secret',
        'config' => ['vendor' => 'VENDOR123', 'environment' => 'sandbox'],
        'is_active' => true,
    ]);

    $worker = User::factory()->worker()->create();

    $payment = Payment::factory()->create([
        'business_id' => $business->id,
        'order_id' => null,
        'amount' => 6500,
        'method' => PaymentMethod::MobileMoney,
        'status' => PaymentStatus::Pending,
        'provider' => 'selcom',
        'provider_order_id' => 'TIP-ORDER-3001',
        'reference' => 'TIP-ORDER-3001',
        'customer_phone' => '255700444555',
        'customer_name' => 'Tip Customer',
        'metadata' => [
            'purpose' => 'tip',
            'worker_user_id' => $worker->id,
            'source' => 'whatsapp',
        ],
    ]);

    Http::fake([
        'https://apigw.selcommobile.com/sandbox/v1/checkout/order-status*' => Http::response([
            'result' => 'SUCCESS',
            'data' => [
                'payment_status' => 'COMPLETED',
                'reference' => 'SELCOM-TIP-123',
            ],
        ], 200),
    ]);

    $this->withHeaders(botHeaders($this->secret))
        ->getJson("/api/bot/payments/{$payment->id}/status")
        ->assertOk()
        ->assertJsonPath('status', 'completed');

    $tip = Tip::query()->latest('id')->first();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Completed)
        ->and($tip)->not->toBeNull()
        ->and($tip->payment_id)->toBe($payment->id)
        ->and($tip->worker_id)->toBe($worker->id)
        ->and((float) $tip->amount)->toBe(6500.0)
        ->and($tip->customer_phone)->toBe('255700444555');
});

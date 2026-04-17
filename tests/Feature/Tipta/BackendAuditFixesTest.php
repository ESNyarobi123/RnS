<?php

use App\Actions\Business\CreateBusiness;
use App\Enums\LinkType;
use App\Enums\StockStatus;
use App\Models\BotSetting;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Stock;
use App\Models\Table;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Validation\ValidationException;

// === Expired Links Deactivation Command ===

test('deactivate expired links command deactivates expired temporary links', function () {
    $link = BusinessWorker::factory()->create([
        'link_type' => LinkType::Temporary,
        'expires_at' => now()->subDay(),
        'is_active' => true,
    ]);

    $this->artisan('app:deactivate-expired-links')
        ->expectsOutputToContain('Deactivated 1 expired link(s)')
        ->assertExitCode(0);

    expect($link->fresh()->is_active)->toBeFalse()
        ->and($link->fresh()->unlinked_at)->not->toBeNull();
});

test('deactivate expired links ignores permanent links', function () {
    $link = BusinessWorker::factory()->create([
        'link_type' => LinkType::Permanent,
        'is_active' => true,
    ]);

    $this->artisan('app:deactivate-expired-links')
        ->expectsOutputToContain('Deactivated 0 expired link(s)')
        ->assertExitCode(0);

    expect($link->fresh()->is_active)->toBeTrue();
});

test('deactivate expired links ignores non-expired temporary links', function () {
    $link = BusinessWorker::factory()->temporary(30)->create([
        'is_active' => true,
    ]);

    $this->artisan('app:deactivate-expired-links')
        ->expectsOutputToContain('Deactivated 0 expired link(s)')
        ->assertExitCode(0);

    expect($link->fresh()->is_active)->toBeTrue();
});

// === Stock Auto-Update Status ===

test('stock status auto-updates to out of stock when quantity is zero', function () {
    $stock = Stock::factory()->create(['quantity' => 10, 'reorder_level' => 5]);
    expect($stock->status)->toBe(StockStatus::InStock);

    $stock->update(['quantity' => 0]);
    expect($stock->fresh()->status)->toBe(StockStatus::OutOfStock);
});

test('stock status auto-updates to low stock', function () {
    $stock = Stock::factory()->create(['quantity' => 10, 'reorder_level' => 5]);

    $stock->update(['quantity' => 3]);
    expect($stock->fresh()->status)->toBe(StockStatus::LowStock);
});

test('stock status auto-updates to in stock when quantity goes above reorder level', function () {
    $stock = Stock::factory()->create(['quantity' => 0, 'reorder_level' => 5]);

    $stock->update(['quantity' => 20]);
    expect($stock->fresh()->status)->toBe(StockStatus::InStock);
});

// === Business Limit per Manager ===

test('manager cannot create second business via action', function () {
    $manager = User::factory()->manager()->create();
    Business::factory()->create(['user_id' => $manager->id]);

    (new CreateBusiness)->execute($manager, [
        'name' => 'Second Business',
        'type' => 'salon',
    ]);
})->throws(ValidationException::class);

// === Category Policy ===

test('admin can manage any category', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    expect($admin->can('view', $category))->toBeTrue()
        ->and($admin->can('update', $category))->toBeTrue()
        ->and($admin->can('delete', $category))->toBeTrue();
});

test('manager can manage own business categories', function () {
    $manager = User::factory()->manager()->create();
    $business = Business::factory()->create(['user_id' => $manager->id]);
    $ownCategory = Category::factory()->create(['business_id' => $business->id]);
    $otherCategory = Category::factory()->create();

    expect($manager->can('view', $ownCategory))->toBeTrue()
        ->and($manager->can('update', $ownCategory))->toBeTrue()
        ->and($manager->can('view', $otherCategory))->toBeFalse();
});

test('worker cannot manage categories', function () {
    $worker = User::factory()->worker()->create();
    $category = Category::factory()->create();

    expect($worker->can('view', $category))->toBeFalse()
        ->and($worker->can('create', Category::class))->toBeFalse();
});

// === Payment Policy ===

test('manager can view own business payments', function () {
    $manager = User::factory()->manager()->create();
    $business = Business::factory()->create(['user_id' => $manager->id]);
    $payment = Payment::factory()->create(['business_id' => $business->id]);
    $otherPayment = Payment::factory()->create();

    expect($manager->can('view', $payment))->toBeTrue()
        ->and($manager->can('view', $otherPayment))->toBeFalse();
});

// === Payroll Policy ===

test('worker can view own payroll', function () {
    $worker = User::factory()->worker()->create();
    $payroll = Payroll::factory()->create(['worker_id' => $worker->id]);
    $otherPayroll = Payroll::factory()->create();

    expect($worker->can('view', $payroll))->toBeTrue()
        ->and($worker->can('view', $otherPayroll))->toBeFalse();
});

test('manager can manage own business payrolls', function () {
    $manager = User::factory()->manager()->create();
    $business = Business::factory()->create(['user_id' => $manager->id]);
    $payroll = Payroll::factory()->create(['business_id' => $business->id]);

    expect($manager->can('view', $payroll))->toBeTrue()
        ->and($manager->can('update', $payroll))->toBeTrue();
});

// === Feedback Policy ===

test('anyone can create feedback', function () {
    $worker = User::factory()->worker()->create();
    $manager = User::factory()->manager()->create();

    expect($worker->can('create', Feedback::class))->toBeTrue()
        ->and($manager->can('create', Feedback::class))->toBeTrue();
});

test('manager can delete own business feedback', function () {
    $manager = User::factory()->manager()->create();
    $business = Business::factory()->create(['user_id' => $manager->id]);
    $feedback = Feedback::factory()->create(['business_id' => $business->id]);

    expect($manager->can('delete', $feedback))->toBeTrue();
});

// === Bot & QR Flow ===

test('bot health endpoint is available', function () {
    $this->getJson('/api/bot/health')
        ->assertOk()
        ->assertJsonPath('status', 'ok');
});

test('qr scan redirects to whatsapp with business code', function () {
    BotSetting::factory()->create([
        'phone_number' => '255700000000',
        'secret_key' => str_repeat('x', 64),
        'is_active' => true,
    ]);

    $business = Business::factory()->restaurant()->create([
        'bot_code' => 'BIZ-SKY123',
        'qr_code' => 'BIZ-SKY123',
    ]);

    $this->get(route('qr.scan', ['code' => $business->bot_code]))
        ->assertRedirect('https://wa.me/255700000000?text=BIZ-SKY123');
});

test('authenticated bot can fetch business by code', function () {
    $secret = str_repeat('k', 64);

    BotSetting::factory()->create([
        'phone_number' => '255700000000',
        'secret_key' => $secret,
        'is_active' => true,
    ]);

    $business = Business::factory()->salon()->create([
        'name' => 'Glow Studio',
        'bot_code' => 'BIZ-GLOW01',
        'qr_code' => 'BIZ-GLOW01',
    ]);

    $this->withHeaders([
        'X-Bot-Secret' => $secret,
    ])->getJson("/api/bot/business/{$business->bot_code}")
        ->assertOk()
        ->assertJsonPath('name', 'Glow Studio')
        ->assertJsonPath('type', 'salon')
        ->assertJsonPath('worker_title', 'Stylist')
        ->assertJsonPath('table_label', 'Station');
});

test('authenticated bot can fetch worker by code with restaurant and salon context labels', function () {
    $secret = str_repeat('w', 64);

    BotSetting::factory()->create([
        'phone_number' => '255700000000',
        'secret_key' => $secret,
        'is_active' => true,
    ]);

    $restaurantWorker = User::factory()->worker()->create();
    $restaurant = Business::factory()->restaurant()->create();
    $waiterLink = BusinessWorker::factory()->create([
        'business_id' => $restaurant->id,
        'worker_id' => $restaurantWorker->id,
        'qr_code' => 'WAITER-123',
        'is_active' => true,
    ]);

    $salonWorker = User::factory()->worker()->create();
    $salon = Business::factory()->salon()->create();
    $stylistLink = BusinessWorker::factory()->create([
        'business_id' => $salon->id,
        'worker_id' => $salonWorker->id,
        'qr_code' => 'STYLIST-123',
        'is_active' => true,
    ]);

    $this->withHeaders([
        'X-Bot-Secret' => $secret,
    ])->getJson("/api/bot/worker/{$waiterLink->qr_code}")
        ->assertOk()
        ->assertJsonPath('business_type', 'restaurant')
        ->assertJsonPath('worker_title', 'Waiter')
        ->assertJsonPath('table_label', 'Table');

    $this->withHeaders([
        'X-Bot-Secret' => $secret,
    ])->getJson("/api/bot/worker/{$stylistLink->qr_code}")
        ->assertOk()
        ->assertJsonPath('business_type', 'salon')
        ->assertJsonPath('worker_title', 'Stylist')
        ->assertJsonPath('table_label', 'Station');
});

test('authenticated bot can fetch table by code with salon station label', function () {
    $secret = str_repeat('s', 64);

    BotSetting::factory()->create([
        'phone_number' => '255700000000',
        'secret_key' => $secret,
        'is_active' => true,
    ]);

    $salon = Business::factory()->salon()->create(['name' => 'Glow Studio']);
    $station = Table::query()->create([
        'business_id' => $salon->id,
        'name' => '2',
        'qr_code' => 'STATION-QR-2',
        'capacity' => 1,
        'status' => 'available',
    ]);

    $this->withHeaders([
        'X-Bot-Secret' => $secret,
    ])->getJson("/api/bot/table/{$station->qr_code}")
        ->assertOk()
        ->assertJsonPath('business_name', 'Glow Studio')
        ->assertJsonPath('business_type', 'salon')
        ->assertJsonPath('table_label', 'Station')
        ->assertJsonPath('display_name', 'Station 2');
});

test('authenticated bot can submit a worker tip', function () {
    $secret = str_repeat('t', 64);

    BotSetting::factory()->create([
        'phone_number' => '255700000000',
        'secret_key' => $secret,
        'is_active' => true,
    ]);

    $worker = User::factory()->worker()->create();
    $business = Business::factory()->restaurant()->create();
    $link = BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
        'is_active' => true,
    ]);

    $this->withHeaders([
        'X-Bot-Secret' => $secret,
    ])->postJson('/api/bot/tips', [
        'business_id' => $business->id,
        'worker_id' => $link->id,
        'amount' => 7500,
        'customer_phone' => '255700123456',
        'source' => 'whatsapp',
    ])
        ->assertOk()
        ->assertJson(fn ($json) => $json->where('amount', 7500)->etc());

    $tip = Tip::query()->latest('id')->first();

    expect($tip)->not->toBeNull()
        ->and($tip->business_id)->toBe($business->id)
        ->and($tip->worker_id)->toBe($worker->id)
        ->and((float) $tip->amount)->toBe(7500.0)
        ->and($tip->source)->toBe('whatsapp');
});

// === Stock Policy ===

test('manager can manage own business stock', function () {
    $manager = User::factory()->manager()->create();
    $business = Business::factory()->create(['user_id' => $manager->id]);
    $stock = Stock::factory()->create(['business_id' => $business->id]);
    $otherStock = Stock::factory()->create();

    expect($manager->can('view', $stock))->toBeTrue()
        ->and($manager->can('update', $stock))->toBeTrue()
        ->and($manager->can('view', $otherStock))->toBeFalse();
});

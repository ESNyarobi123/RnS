<?php

use App\Livewire\Manager\PaymentSettings;
use App\Models\Business;
use App\Models\PaymentSetting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->manager = User::factory()->manager()->create();
    $this->business = Business::factory()->restaurant()->create(['user_id' => $this->manager->id]);
});

test('manager can view payment settings page', function () {
    Livewire::actingAs($this->manager)
        ->test(PaymentSettings::class)
        ->assertOk()
        ->assertSee('Payment Settings')
        ->assertSee('Selcom API Credentials');
});

test('manager can save selcom credentials', function () {
    Livewire::actingAs($this->manager)
        ->test(PaymentSettings::class)
        ->set('apiKey', 'test-api-key-123')
        ->set('apiSecret', 'test-api-secret-456')
        ->set('vendor', 'VENDOR001')
        ->set('environment', 'sandbox')
        ->set('isActive', true)
        ->call('save');

    $setting = $this->business->paymentSettings()->where('provider', 'selcom')->first();
    expect($setting)->not->toBeNull()
        ->and($setting->api_key)->toBe('test-api-key-123')
        ->and($setting->api_secret)->toBe('test-api-secret-456')
        ->and($setting->config['vendor'])->toBe('VENDOR001')
        ->and($setting->config['environment'])->toBe('sandbox')
        ->and($setting->is_active)->toBeTrue();
});

test('manager can update existing credentials', function () {
    PaymentSetting::create([
        'business_id' => $this->business->id,
        'provider' => 'selcom',
        'api_key' => 'old-key',
        'api_secret' => 'old-secret',
        'config' => ['vendor' => 'OLD', 'environment' => 'sandbox'],
        'is_active' => false,
    ]);

    Livewire::actingAs($this->manager)
        ->test(PaymentSettings::class)
        ->assertSet('vendor', 'OLD')
        ->set('apiKey', 'new-key')
        ->set('vendor', 'NEW')
        ->set('isActive', true)
        ->call('save');

    $settings = $this->business->paymentSettings()->where('provider', 'selcom')->get();
    expect($settings)->toHaveCount(1)
        ->and($settings->first()->api_key)->toBe('new-key')
        ->and($settings->first()->config['vendor'])->toBe('NEW')
        ->and($settings->first()->is_active)->toBeTrue();
});

test('credentials are required', function () {
    Livewire::actingAs($this->manager)
        ->test(PaymentSettings::class)
        ->set('apiKey', '')
        ->set('apiSecret', '')
        ->set('vendor', '')
        ->call('save')
        ->assertHasErrors(['apiKey', 'apiSecret', 'vendor']);
});

test('existing credentials are loaded on mount', function () {
    PaymentSetting::create([
        'business_id' => $this->business->id,
        'provider' => 'selcom',
        'api_key' => 'loaded-key',
        'api_secret' => 'loaded-secret',
        'config' => ['vendor' => 'LOADED', 'environment' => 'production'],
        'is_active' => true,
    ]);

    Livewire::actingAs($this->manager)
        ->test(PaymentSettings::class)
        ->assertSet('apiKey', 'loaded-key')
        ->assertSet('apiSecret', 'loaded-secret')
        ->assertSet('vendor', 'LOADED')
        ->assertSet('environment', 'production')
        ->assertSet('isActive', true);
});

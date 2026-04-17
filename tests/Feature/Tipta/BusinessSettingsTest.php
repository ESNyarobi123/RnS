<?php

use App\Livewire\Manager\BusinessSettings;
use App\Models\BotSetting;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->manager = User::factory()->manager()->create();
    $this->business = Business::factory()->restaurant()->create([
        'user_id' => $this->manager->id,
        'name' => 'Original Name',
        'description' => 'Original description',
        'address' => '123 Main St',
        'phone' => '+255700000000',
    ]);
});

test('manager can view business settings page', function () {
    Livewire::actingAs($this->manager)
        ->test(BusinessSettings::class)
        ->assertOk()
        ->assertSee('Business Settings')
        ->assertSet('name', 'Original Name')
        ->assertSet('description', 'Original description')
        ->assertSet('address', '123 Main St')
        ->assertSet('phone', '+255700000000');
});

test('manager can update business name', function () {
    Livewire::actingAs($this->manager)
        ->test(BusinessSettings::class)
        ->set('name', 'Updated Business')
        ->call('save');

    expect($this->business->fresh()->name)->toBe('Updated Business');
});

test('manager can update all business fields', function () {
    Livewire::actingAs($this->manager)
        ->test(BusinessSettings::class)
        ->set('name', 'New Name')
        ->set('description', 'New description')
        ->set('address', '456 New St')
        ->set('phone', '+255711111111')
        ->call('save');

    $business = $this->business->fresh();
    expect($business->name)->toBe('New Name')
        ->and($business->description)->toBe('New description')
        ->and($business->address)->toBe('456 New St')
        ->and($business->phone)->toBe('+255711111111');
});

test('business name is required', function () {
    Livewire::actingAs($this->manager)
        ->test(BusinessSettings::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('settings page shows business type', function () {
    Livewire::actingAs($this->manager)
        ->test(BusinessSettings::class)
        ->assertSee('Restaurant');
});

test('settings page shows business stats', function () {
    Livewire::actingAs($this->manager)
        ->test(BusinessSettings::class)
        ->assertSee('Total Orders')
        ->assertSee('Status');
});

test('manager can generate business whatsapp qr assets', function () {
    Storage::fake('public');
    Http::fake(fn () => throw new RuntimeException('offline'));

    BotSetting::factory()->create([
        'phone_number' => '255700000000',
        'secret_key' => str_repeat('a', 64),
        'is_active' => true,
    ]);

    Livewire::actingAs($this->manager)
        ->test(BusinessSettings::class)
        ->call('generateBotAssets');

    $business = $this->business->fresh();

    expect($business->bot_code)->not->toBeNull()
        ->and($business->qr_code)->toBe($business->bot_code)
        ->and($business->qr_image_path)->not->toBeNull();

    $qrSvg = Storage::disk('public')->get($business->qr_image_path);

    expect($qrSvg)->toContain('https://wa.me/255700000000?text='.$business->bot_code);
});

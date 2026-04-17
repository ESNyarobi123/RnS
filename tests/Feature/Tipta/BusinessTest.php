<?php

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

test('manager can own a business', function () {
    $manager = User::factory()->manager()->create();
    $business = Business::factory()->create(['user_id' => $manager->id]);

    expect($business->owner->id)->toBe($manager->id)
        ->and($manager->businesses)->toHaveCount(1);
});

test('business has correct type helpers', function () {
    $restaurant = Business::factory()->restaurant()->create();
    $salon = Business::factory()->salon()->create();

    expect($restaurant->isRestaurant())->toBeTrue()
        ->and($restaurant->isSalon())->toBeFalse()
        ->and($salon->isSalon())->toBeTrue()
        ->and($salon->isRestaurant())->toBeFalse();
});

test('business type returns correct worker titles', function () {
    expect(BusinessType::Restaurant->workerTitle())->toBe('Waiter')
        ->and(BusinessType::Salon->workerTitle())->toBe('Stylist')
        ->and(BusinessType::Restaurant->workerTitlePlural())->toBe('Waiters')
        ->and(BusinessType::Salon->workerTitlePlural())->toBe('Stylists');
});

test('business has categories and products', function () {
    $business = Business::factory()->restaurant()->create();
    $category = Category::factory()->create(['business_id' => $business->id]);
    Product::factory()->count(3)->create([
        'business_id' => $business->id,
        'category_id' => $category->id,
    ]);

    expect($business->categories)->toHaveCount(1)
        ->and($business->products)->toHaveCount(3)
        ->and($category->products)->toHaveCount(3);
});

test('business status defaults to active', function () {
    $business = Business::factory()->create();

    expect($business->isActive())->toBeTrue()
        ->and($business->status)->toBe(BusinessStatus::Active);
});

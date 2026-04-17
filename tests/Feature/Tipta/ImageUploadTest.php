<?php

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('category can upload an image', function () {
    $category = Category::factory()->create();
    $file = UploadedFile::fake()->image('food.jpg', 400, 400);

    $path = $category->uploadImage($file);

    expect($category->fresh()->image)->toBe($path)
        ->and($category->fresh()->hasImage())->toBeTrue()
        ->and($category->fresh()->imageUrl())->toContain($path);

    Storage::disk('public')->assertExists($path);
});

test('product can upload an image', function () {
    $product = Product::factory()->create();
    $file = UploadedFile::fake()->image('haircut.png', 600, 400);

    $path = $product->uploadImage($file);

    expect($product->fresh()->image)->toBe($path)
        ->and($product->fresh()->hasImage())->toBeTrue();

    Storage::disk('public')->assertExists($path);
});

test('business can upload a logo', function () {
    $business = Business::factory()->create();
    $file = UploadedFile::fake()->image('logo.png', 200, 200);

    $path = $business->uploadImage($file);

    expect($business->fresh()->logo)->toBe($path)
        ->and($business->fresh()->imageColumn())->toBe('logo')
        ->and($business->fresh()->hasImage())->toBeTrue();

    Storage::disk('public')->assertExists($path);
});

test('user can upload an avatar', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg', 150, 150);

    $path = $user->uploadImage($file);

    expect($user->fresh()->avatar)->toBe($path)
        ->and($user->fresh()->imageColumn())->toBe('avatar')
        ->and($user->fresh()->hasImage())->toBeTrue();

    Storage::disk('public')->assertExists($path);
});

test('uploading new image deletes old one', function () {
    $category = Category::factory()->create();

    $oldFile = UploadedFile::fake()->image('old.jpg');
    $oldPath = $category->uploadImage($oldFile);
    Storage::disk('public')->assertExists($oldPath);

    $newFile = UploadedFile::fake()->image('new.jpg');
    $newPath = $category->uploadImage($newFile);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($newPath);
    expect($category->fresh()->image)->toBe($newPath);
});

test('deleting model without soft deletes removes image', function () {
    $category = Category::factory()->create();
    $file = UploadedFile::fake()->image('food.jpg');
    $path = $category->uploadImage($file);

    Storage::disk('public')->assertExists($path);

    $category->delete();

    Storage::disk('public')->assertMissing($path);
});

test('soft deleting model keeps image', function () {
    $product = Product::factory()->create();
    $file = UploadedFile::fake()->image('service.jpg');
    $path = $product->uploadImage($file);

    Storage::disk('public')->assertExists($path);

    $product->delete();

    Storage::disk('public')->assertExists($path);
    expect($product->trashed())->toBeTrue();
});

test('force deleting model removes image', function () {
    $product = Product::factory()->create();
    $file = UploadedFile::fake()->image('service.jpg');
    $path = $product->uploadImage($file);

    Storage::disk('public')->assertExists($path);

    $product->forceDelete();

    Storage::disk('public')->assertMissing($path);
});

test('model without image returns null imageUrl', function () {
    $category = Category::factory()->create(['image' => null]);

    expect($category->imageUrl())->toBeNull()
        ->and($category->hasImage())->toBeFalse();
});

test('images are stored in correct directories', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create();
    $business = Business::factory()->create();

    $category->uploadImage(UploadedFile::fake()->image('c.jpg'));
    $product->uploadImage(UploadedFile::fake()->image('p.jpg'));
    $business->uploadImage(UploadedFile::fake()->image('b.jpg'));

    expect($category->fresh()->image)->toStartWith('categories/')
        ->and($product->fresh()->image)->toStartWith('products/')
        ->and($business->fresh()->logo)->toStartWith('businesses/');
});

<?php

use App\Livewire\Manager\ManageProducts;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->manager = User::factory()->manager()->create();
    $this->business = Business::factory()->restaurant()->create(['user_id' => $this->manager->id]);
});

// === Category Tests ===

test('manager can view manage products page', function () {
    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->assertOk()
        ->assertSee($this->business->type->itemLabelPlural());
});

test('manager can create a category', function () {
    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('openCreateCategory')
        ->set('categoryName', 'Breakfast')
        ->set('categoryDescription', 'Morning meals')
        ->call('saveCategory');

    expect($this->business->categories)->toHaveCount(1)
        ->and($this->business->categories->first()->name)->toBe('Breakfast');
});

test('manager can edit a category', function () {
    $category = Category::factory()->create(['business_id' => $this->business->id, 'name' => 'Old Name']);

    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('openEditCategory', $category->id)
        ->set('categoryName', 'New Name')
        ->call('saveCategory');

    expect($category->fresh()->name)->toBe('New Name');
});

test('manager can delete an empty category', function () {
    $category = Category::factory()->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('deleteCategory', $category->id);

    expect(Category::find($category->id))->toBeNull();
});

test('category name is required', function () {
    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('openCreateCategory')
        ->set('categoryName', '')
        ->call('saveCategory')
        ->assertHasErrors(['categoryName' => 'required']);
});

// === Product Tests ===

test('manager can create a product for restaurant', function () {
    $category = Category::factory()->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('openCreateProduct', $category->id)
        ->set('productName', 'Chicken Rice')
        ->set('productPrice', '15000')
        ->call('saveProduct');

    expect($category->products)->toHaveCount(1)
        ->and($category->products->first()->name)->toBe('Chicken Rice')
        ->and($category->products->first()->duration_minutes)->toBeNull();
});

test('manager can create a service with duration for salon', function () {
    $salonManager = User::factory()->manager()->create();
    $salon = Business::factory()->salon()->create(['user_id' => $salonManager->id]);
    $category = Category::factory()->create(['business_id' => $salon->id]);

    Livewire::actingAs($salonManager)
        ->test(ManageProducts::class)
        ->call('openCreateProduct', $category->id)
        ->set('productName', "Men's Cut")
        ->set('productPrice', '20000')
        ->set('productDuration', 30)
        ->call('saveProduct');

    $product = $category->products->first();
    expect($product->name)->toBe("Men's Cut")
        ->and($product->duration_minutes)->toBe(30);
});

test('manager can edit a product', function () {
    $category = Category::factory()->create(['business_id' => $this->business->id]);
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'category_id' => $category->id,
        'name' => 'Old Product',
        'price' => 10000,
    ]);

    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('openEditProduct', $product->id)
        ->set('productName', 'Updated Product')
        ->set('productPrice', '25000')
        ->call('saveProduct');

    expect($product->fresh()->name)->toBe('Updated Product')
        ->and((float) $product->fresh()->price)->toBe(25000.0);
});

test('manager can toggle product active status', function () {
    $category = Category::factory()->create(['business_id' => $this->business->id]);
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('toggleProduct', $product->id);

    expect($product->fresh()->is_active)->toBeFalse();

    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('toggleProduct', $product->id);

    expect($product->fresh()->is_active)->toBeTrue();
});

test('manager can delete a product', function () {
    $category = Category::factory()->create(['business_id' => $this->business->id]);
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'category_id' => $category->id,
    ]);

    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('deleteProduct', $product->id);

    expect($product->fresh()->trashed())->toBeTrue();
});

test('product name and price are required', function () {
    $category = Category::factory()->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('openCreateProduct', $category->id)
        ->set('productName', '')
        ->set('productPrice', '')
        ->call('saveProduct')
        ->assertHasErrors(['productName', 'productPrice']);
});

test('product category is required', function () {
    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('openCreateProduct')
        ->set('productName', 'Test')
        ->set('productPrice', '1000')
        ->set('productCategoryId', null)
        ->call('saveProduct')
        ->assertHasErrors(['productCategoryId']);
});

// === Stock Tests ===

test('manager can create a product with stock quantity', function () {
    $category = Category::factory()->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('openCreateProduct', $category->id)
        ->set('productName', 'Water Bottle')
        ->set('productDescription', 'Mineral water 500ml')
        ->set('productPrice', '1500')
        ->set('productStock', 50)
        ->call('saveProduct');

    $product = $category->products->first();
    expect($product->name)->toBe('Water Bottle')
        ->and($product->description)->toBe('Mineral water 500ml')
        ->and($product->stock_quantity)->toBe(50);
});

test('manager can update stock quantity on existing product', function () {
    $category = Category::factory()->create(['business_id' => $this->business->id]);
    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'category_id' => $category->id,
        'stock_quantity' => 10,
    ]);

    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->call('openEditProduct', $product->id)
        ->assertSet('productStock', 10)
        ->set('productStock', 25)
        ->call('saveProduct');

    expect($product->fresh()->stock_quantity)->toBe(25);
});

// === Dynamic Labels ===

test('restaurant shows correct labels', function () {
    Livewire::actingAs($this->manager)
        ->test(ManageProducts::class)
        ->assertSee('Menu Items')
        ->assertSee('Food Category');
});

test('salon shows correct labels', function () {
    $salonManager = User::factory()->manager()->create();
    Business::factory()->salon()->create(['user_id' => $salonManager->id]);

    Livewire::actingAs($salonManager)
        ->test(ManageProducts::class)
        ->assertSee('Services')
        ->assertSee('Service Category');
});

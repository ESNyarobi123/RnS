<?php

namespace App\Livewire\Manager;

use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Manage Products')]
class ManageProducts extends Component
{
    use WithFileUploads;

    // Category form
    public bool $showCategoryModal = false;

    public ?int $editingCategoryId = null;

    public string $categoryName = '';

    public string $categoryDescription = '';

    public $categoryImage = null;

    // Product form
    public bool $showProductModal = false;

    public ?int $editingProductId = null;

    public ?int $productCategoryId = null;

    public string $productName = '';

    public string $productDescription = '';

    public string $productPrice = '';

    public ?int $productDuration = null;

    public ?int $productStock = null;

    public $productImage = null;

    #[Computed]
    public function business()
    {
        return Auth::user()->businesses()->first();
    }

    #[Computed]
    public function categories()
    {
        return $this->business?->categories()->with('products')->orderBy('sort_order')->get() ?? collect();
    }

    #[Computed]
    public function itemLabel(): string
    {
        return $this->business?->type->itemLabelPlural() ?? 'Products';
    }

    #[Computed]
    public function itemLabelSingular(): string
    {
        return $this->business?->type->itemLabel() ?? 'Product';
    }

    #[Computed]
    public function categoryLabel(): string
    {
        return $this->business?->type->categoryLabel() ?? 'Category';
    }

    #[Computed]
    public function isSalon(): bool
    {
        return $this->business?->isSalon() ?? false;
    }

    // === Category CRUD ===

    public function openCreateCategory(): void
    {
        $this->resetCategoryForm();
        $this->showCategoryModal = true;
    }

    public function openEditCategory(int $categoryId): void
    {
        $category = $this->business->categories()->findOrFail($categoryId);
        $this->editingCategoryId = $category->id;
        $this->categoryName = $category->name;
        $this->categoryDescription = $category->description ?? '';
        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryName' => 'required|string|max:255',
            'categoryDescription' => 'nullable|string|max:1000',
            'categoryImage' => 'nullable|image|max:2048',
        ]);

        if ($this->editingCategoryId) {
            $category = $this->business->categories()->findOrFail($this->editingCategoryId);
            $category->update([
                'name' => $this->categoryName,
                'description' => $this->categoryDescription ?: null,
            ]);
            if ($this->categoryImage) {
                $category->uploadImage($this->categoryImage);
            }
            $message = __('Category updated.');
        } else {
            $maxSort = $this->business->categories()->max('sort_order') ?? -1;
            $category = $this->business->categories()->create([
                'name' => $this->categoryName,
                'description' => $this->categoryDescription ?: null,
                'sort_order' => $maxSort + 1,
            ]);
            if ($this->categoryImage) {
                $category->uploadImage($this->categoryImage);
            }
            $message = __('Category created.');
        }

        $this->showCategoryModal = false;
        $this->resetCategoryForm();
        unset($this->categories);

        Flux::toast(variant: 'success', text: $message);
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = $this->business->categories()->findOrFail($categoryId);
        $category->delete();

        unset($this->categories);

        Flux::toast(variant: 'success', text: __('Category deleted.'));
    }

    // === Product CRUD ===

    public function openCreateProduct(?int $categoryId = null): void
    {
        $this->resetProductForm();
        $this->productCategoryId = $categoryId;
        $this->showProductModal = true;
    }

    public function openEditProduct(int $productId): void
    {
        $product = $this->business->products()->findOrFail($productId);
        $this->editingProductId = $product->id;
        $this->productCategoryId = $product->category_id;
        $this->productName = $product->name;
        $this->productDescription = $product->description ?? '';
        $this->productPrice = (string) $product->price;
        $this->productDuration = $product->duration_minutes;
        $this->productStock = $product->stock_quantity;
        $this->productImage = null;
        $this->showProductModal = true;
    }

    public function saveProduct(): void
    {
        $this->validate([
            'productCategoryId' => 'required|exists:categories,id',
            'productName' => 'required|string|max:255',
            'productDescription' => 'nullable|string|max:1000',
            'productPrice' => 'required|numeric|min:0',
            'productDuration' => 'nullable|integer|min:1',
            'productStock' => 'nullable|integer|min:0',
            'productImage' => 'nullable|image|max:2048',
        ]);

        $data = [
            'category_id' => $this->productCategoryId,
            'name' => $this->productName,
            'description' => $this->productDescription ?: null,
            'price' => $this->productPrice,
            'duration_minutes' => $this->isSalon ? $this->productDuration : null,
            'stock_quantity' => $this->productStock,
        ];

        if ($this->editingProductId) {
            $product = $this->business->products()->findOrFail($this->editingProductId);
            $product->update($data);
            if ($this->productImage) {
                $product->uploadImage($this->productImage);
            }
            $message = __(':item updated.', ['item' => $this->itemLabelSingular]);
        } else {
            $data['business_id'] = $this->business->id;
            $product = Product::create($data);
            if ($this->productImage) {
                $product->uploadImage($this->productImage);
            }
            $message = __(':item created.', ['item' => $this->itemLabelSingular]);
        }

        $this->showProductModal = false;
        $this->resetProductForm();
        unset($this->categories);

        Flux::toast(variant: 'success', text: $message);
    }

    public function toggleProduct(int $productId): void
    {
        $product = $this->business->products()->findOrFail($productId);
        $product->update(['is_active' => ! $product->is_active]);

        unset($this->categories);

        $status = $product->is_active ? __('activated') : __('deactivated');
        Flux::toast(variant: 'success', text: __(':item :status.', ['item' => $product->name, 'status' => $status]));
    }

    public function deleteProduct(int $productId): void
    {
        $product = $this->business->products()->findOrFail($productId);
        $product->delete();

        unset($this->categories);

        Flux::toast(variant: 'success', text: __(':item deleted.', ['item' => $this->itemLabelSingular]));
    }

    private function resetCategoryForm(): void
    {
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryDescription = '';
        $this->categoryImage = null;
    }

    private function resetProductForm(): void
    {
        $this->editingProductId = null;
        $this->productCategoryId = null;
        $this->productName = '';
        $this->productDescription = '';
        $this->productPrice = '';
        $this->productDuration = null;
        $this->productStock = null;
        $this->productImage = null;
    }
}

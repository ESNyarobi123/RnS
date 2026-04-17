<div class="flex h-full w-full flex-1 flex-col gap-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-smoke">{{ $this->itemLabel }}</h1>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('Manage your :categories and :items', ['categories' => strtolower($this->categoryLabel), 'items' => strtolower($this->itemLabel)]) }}</p>
        </div>
        <div class="flex items-center gap-2">
            <flux:button size="sm" wire:click="openCreateCategory" class="!bg-smoke !text-ivory hover:!bg-smoke-light">
                {{ __('+ :label', ['label' => $this->categoryLabel]) }}
            </flux:button>
            @if ($this->categories->isNotEmpty())
                <flux:button size="sm" wire:click="openCreateProduct" class="!bg-terra !text-white hover:!bg-terra-dark">
                    {{ __('+ :label', ['label' => $this->itemLabelSingular]) }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Empty State --}}
    @if ($this->categories->isEmpty())
        <div class="rounded-2xl border-2 border-dashed border-ivory-dark/50 bg-ivory-light p-10 text-center sm:p-14">
            <div class="mx-auto flex size-20 items-center justify-center rounded-3xl bg-smoke/5">
                <flux:icon.squares-2x2 class="size-10 text-smoke-muted" />
            </div>
            <h2 class="mt-6 text-xl font-bold text-smoke">{{ __('No :categories Yet', ['categories' => strtolower($this->categoryLabel) . 's']) }}</h2>
            <p class="mx-auto mt-3 max-w-sm text-sm text-smoke-muted">
                {{ __('Create a :category first, then add :items to it.', ['category' => strtolower($this->categoryLabel), 'items' => strtolower($this->itemLabel)]) }}
            </p>
            <flux:button wire:click="openCreateCategory" class="mt-8 !bg-terra !text-white hover:!bg-terra-dark">
                {{ __('Create :label', ['label' => $this->categoryLabel]) }}
            </flux:button>
        </div>
    @else
        {{-- Categories & Products --}}
        @foreach ($this->categories as $category)
            <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
                {{-- Category Header --}}
                <div class="border-b border-ivory-dark/30 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if ($category->hasImage())
                                <img src="{{ $category->imageUrl() }}" class="size-9 rounded-lg object-cover" />
                            @else
                                <div class="flex size-9 items-center justify-center rounded-lg bg-terra/10">
                                    <flux:icon.folder class="size-4 text-terra" />
                                </div>
                            @endif
                            <div>
                                <h2 class="font-semibold text-smoke">{{ $category->name }}</h2>
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm">{{ $category->products->count() }} {{ strtolower($this->itemLabel) }}</flux:badge>
                                    @if ($category->description)
                                        <span class="hidden text-xs text-smoke-muted sm:inline">{{ $category->description }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" wire:click="openCreateProduct({{ $category->id }})" title="{{ __('Add :item', ['item' => $this->itemLabelSingular]) }}">
                                <flux:icon.plus class="size-4 text-terra" />
                            </flux:button>
                            <flux:button size="sm" variant="ghost" wire:click="openEditCategory({{ $category->id }})" title="{{ __('Edit Category') }}">
                                <flux:icon.pencil-square class="size-4 text-smoke-muted" />
                            </flux:button>
                            @if ($category->products->isEmpty())
                                <flux:button size="sm" variant="ghost" wire:click="deleteCategory({{ $category->id }})" wire:confirm="{{ __('Delete this category?') }}" title="{{ __('Delete Category') }}">
                                    <flux:icon.trash class="size-4 text-red-500" />
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Products List --}}
                @if ($category->products->isNotEmpty())
                    <div class="divide-y divide-ivory-dark/30">
                        @foreach ($category->products as $product)
                            <div class="flex items-center justify-between px-6 py-3 transition hover:bg-ivory-light {{ ! $product->is_active ? 'opacity-50' : '' }}">
                                <div class="flex items-center gap-3">
                                    @if ($product->hasImage())
                                        <img src="{{ $product->imageUrl() }}" class="size-12 rounded-lg object-cover" />
                                    @else
                                        <div class="flex size-12 items-center justify-center rounded-lg bg-ivory-light">
                                            <flux:icon.cube class="size-5 text-smoke-muted/40" />
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-smoke">{{ $product->name }}</p>
                                        @if ($product->description)
                                            <p class="max-w-xs truncate text-xs text-smoke-muted">{{ $product->description }}</p>
                                        @endif
                                        <div class="mt-0.5 flex items-center gap-2">
                                            <span class="text-sm font-semibold text-terra">{{ number_format($product->price, 0) }} TZS</span>
                                            @if ($product->duration_minutes)
                                                <span class="text-xs text-smoke-muted">· {{ $product->duration_minutes }} {{ __('min') }}</span>
                                            @endif
                                            @if (! is_null($product->stock_quantity))
                                                <span class="text-xs {{ $product->stock_quantity <= 0 ? 'font-semibold text-red-500' : ($product->stock_quantity <= 5 ? 'text-amber-600' : 'text-smoke-muted') }}">
                                                    · {{ __('Stock: :qty', ['qty' => $product->stock_quantity]) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <flux:button size="sm" variant="ghost" wire:click="toggleProduct({{ $product->id }})" title="{{ $product->is_active ? __('Deactivate') : __('Activate') }}">
                                        @if ($product->is_active)
                                            <flux:icon.eye class="size-4 text-emerald-600" />
                                        @else
                                            <flux:icon.eye-slash class="size-4 text-smoke-muted" />
                                        @endif
                                    </flux:button>
                                    <flux:button size="sm" variant="ghost" wire:click="openEditProduct({{ $product->id }})" title="{{ __('Edit') }}">
                                        <flux:icon.pencil-square class="size-4 text-smoke-muted" />
                                    </flux:button>
                                    <flux:button size="sm" variant="ghost" wire:click="deleteProduct({{ $product->id }})" wire:confirm="{{ __('Delete :name?', ['name' => $product->name]) }}" title="{{ __('Delete') }}">
                                        <flux:icon.trash class="size-4 text-red-500" />
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <p class="text-sm text-smoke-muted">{{ __('No :items in this category.', ['items' => strtolower($this->itemLabel)]) }}</p>
                        <flux:button size="sm" wire:click="openCreateProduct({{ $category->id }})" class="mt-3 !bg-terra !text-white hover:!bg-terra-dark">
                            {{ __('Add :item', ['item' => $this->itemLabelSingular]) }}
                        </flux:button>
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- Category Modal --}}
    <flux:modal wire:model="showCategoryModal" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingCategoryId ? __('Edit :label', ['label' => $this->categoryLabel]) : __('New :label', ['label' => $this->categoryLabel]) }}</flux:heading>
                <flux:text class="mt-1 text-smoke-muted">{{ $editingCategoryId ? __('Update category details.') : __('Add a new category to organize your :items.', ['items' => strtolower($this->itemLabel)]) }}</flux:text>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="categoryName" label="{{ __('Name') }}" placeholder="{{ __('e.g. Drinks, Haircuts, Mafuta...') }}" />
                <flux:textarea wire:model="categoryDescription" label="{{ __('Description') }}" placeholder="{{ __('Optional description...') }}" rows="2" />

                {{-- Category Image --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-smoke">{{ __('Image') }}</label>
                    <div class="flex items-center gap-3">
                        @if ($categoryImage)
                            <img src="{{ $categoryImage->temporaryUrl() }}" class="size-14 rounded-lg object-cover ring-2 ring-terra/30" />
                        @else
                            <div class="flex size-14 items-center justify-center rounded-lg bg-ivory-light">
                                <flux:icon.photo class="size-6 text-smoke-muted/40" />
                            </div>
                        @endif
                        <label class="cursor-pointer rounded-lg bg-ivory-light px-4 py-2 text-sm font-medium text-smoke transition hover:bg-ivory-dark/30">
                            {{ __('Choose Image') }}
                            <input type="file" wire:model="categoryImage" accept="image/*" class="hidden" />
                        </label>
                    </div>
                    @error('categoryImage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$toggle('showCategoryModal')">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="saveCategory" class="!bg-terra !text-white hover:!bg-terra-dark">
                    {{ $editingCategoryId ? __('Update') : __('Create') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Product Modal --}}
    <flux:modal wire:model="showProductModal" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingProductId ? __('Edit :label', ['label' => $this->itemLabelSingular]) : __('New :label', ['label' => $this->itemLabelSingular]) }}</flux:heading>
                <flux:text class="mt-1 text-smoke-muted">{{ $editingProductId ? __('Update :item details.', ['item' => strtolower($this->itemLabelSingular)]) : __('Add a new :item.', ['item' => strtolower($this->itemLabelSingular)]) }}</flux:text>
            </div>

            <div class="space-y-4">
                {{-- Product Image --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-smoke">{{ __('Image') }}</label>
                    <div class="flex items-center gap-3">
                        @if ($productImage)
                            <img src="{{ $productImage->temporaryUrl() }}" class="size-16 rounded-lg object-cover ring-2 ring-terra/30" />
                        @else
                            <div class="flex size-16 items-center justify-center rounded-lg bg-ivory-light">
                                <flux:icon.photo class="size-7 text-smoke-muted/40" />
                            </div>
                        @endif
                        <label class="cursor-pointer rounded-lg bg-ivory-light px-4 py-2 text-sm font-medium text-smoke transition hover:bg-ivory-dark/30">
                            {{ __('Choose Image') }}
                            <input type="file" wire:model="productImage" accept="image/*" class="hidden" />
                        </label>
                    </div>
                    @error('productImage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <flux:select wire:model="productCategoryId" label="{{ __('Category') }}">
                    <option value="">{{ __('Select a category...') }}</option>
                    @foreach ($this->categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="productName" label="{{ __('Name') }}" placeholder="{{ __('e.g. Water, Coconut Oil, Men\'s Cut...') }}" />

                <flux:textarea wire:model="productDescription" label="{{ __('Description') }}" placeholder="{{ __('Describe this item...') }}" rows="2" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="productPrice" type="number" step="1" min="0" label="{{ __('Price (TZS)') }}" placeholder="0" />
                    <flux:input wire:model="productStock" type="number" min="0" label="{{ __('Stock Quantity') }}" placeholder="{{ __('e.g. 50') }}" />
                </div>

                @if ($this->isSalon)
                    <flux:input wire:model="productDuration" type="number" min="1" label="{{ __('Duration (minutes)') }}" placeholder="30" />
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$toggle('showProductModal')">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="saveProduct" class="!bg-terra !text-white hover:!bg-terra-dark">
                    {{ $editingProductId ? __('Update') : __('Create') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

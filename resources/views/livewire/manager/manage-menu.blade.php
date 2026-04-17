<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ $title }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Upload the image customers see first on WhatsApp, then keep your structured items updated from the products page.') }}</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr,1.1fr]">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <h2 class="text-lg font-semibold text-smoke">{{ __('WhatsApp Menu Image') }}</h2>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('This image is sent when a customer taps "View Menu" or "View Services" in the WhatsApp bot.') }}</p>

            <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center">
                @if ($menuImage)
                    <img src="{{ $menuImage->temporaryUrl() }}" class="h-44 w-full rounded-2xl object-cover ring-2 ring-terra/20 sm:w-60" />
                @elseif ($business->hasMenuImage())
                    <img src="{{ $business->menuImageUrl() }}" class="h-44 w-full rounded-2xl object-cover ring-2 ring-ivory-dark/20 sm:w-60" />
                @else
                    <div class="flex h-44 w-full items-center justify-center rounded-2xl bg-ivory-light text-sm text-smoke-muted sm:w-60">
                        {{ __('No menu image yet') }}
                    </div>
                @endif

                <div class="space-y-3">
                    <label class="inline-flex cursor-pointer items-center rounded-lg bg-ivory-light px-4 py-2 text-sm font-medium text-smoke transition hover:bg-ivory-dark/30">
                        {{ __('Choose Image') }}
                        <input type="file" wire:model="menuImage" accept="image/*" class="hidden" />
                    </label>
                    <div class="flex gap-2">
                        <flux:button wire:click="uploadMenuImage" class="!bg-terra !text-white hover:!bg-terra-dark">{{ __('Save Image') }}</flux:button>
                        @if ($business->hasMenuImage())
                            <flux:button wire:click="removeMenuImage" variant="ghost" wire:confirm="{{ __('Remove the current menu image?') }}">{{ __('Remove') }}</flux:button>
                        @endif
                    </div>
                    @error('menuImage') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <h2 class="text-lg font-semibold text-smoke">{{ __('Current Catalog Snapshot') }}</h2>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="rounded-2xl bg-ivory-light p-4">
                    <p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Categories') }}</p>
                    <p class="mt-2 text-2xl font-bold text-smoke">{{ $categories->count() }}</p>
                </div>
                <div class="rounded-2xl bg-ivory-light p-4">
                    <p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Active Items') }}</p>
                    <p class="mt-2 text-2xl font-bold text-smoke">{{ $products->count() }}</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($categories as $category)
                    <div class="rounded-2xl border border-ivory-dark/30 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-smoke">{{ $category->name }}</p>
                                <p class="text-xs text-smoke-muted">{{ $category->products_count }} {{ strtolower($business->type->itemLabelPlural()) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-ivory-dark/40 p-6 text-center text-sm text-smoke-muted">
                        {{ __('No categories yet. Add categories and items from the products page.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-smoke">{{ __('Latest Active Items') }}</h2>
                <p class="mt-1 text-sm text-smoke-muted">{{ __('These are the items the bot can read from the API right now.') }}</p>
            </div>
            <flux:button :href="route('manager.products.index')" wire:navigate class="!bg-smoke !text-ivory hover:!bg-smoke-light">
                {{ __('Open Products') }}
            </flux:button>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($products as $product)
                <div class="rounded-2xl border border-ivory-dark/30 p-4">
                    <p class="font-semibold text-smoke">{{ $product->name }}</p>
                    <p class="mt-1 text-sm text-smoke-muted">{{ $product->category?->name ?? __('Uncategorized') }}</p>
                    <p class="mt-2 text-lg font-bold text-terra">{{ number_format($product->price, 0) }} TZS</p>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-ivory-dark/40 p-6 text-sm text-smoke-muted sm:col-span-2 xl:col-span-3">
                    {{ __('No active items yet. Add them from the products page so WhatsApp customers can browse them.') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

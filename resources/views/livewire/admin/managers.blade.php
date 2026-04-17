<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('All Managers') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('View and manage all manager accounts and their businesses') }}</p>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search by name or email...')" />
        </div>
        <flux:select wire:model.live="hasBusiness" class="w-48">
            <flux:select.option value="">{{ __('All Managers') }}</flux:select.option>
            <flux:select.option value="yes">{{ __('With Business') }}</flux:select.option>
            <flux:select.option value="no">{{ __('No Business') }}</flux:select.option>
        </flux:select>
    </div>

    {{-- Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($this->managers as $manager)
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5">
                <div class="flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-full bg-terra/10 text-sm font-bold text-terra">
                        {{ $manager->initials() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <flux:text class="truncate font-medium">{{ $manager->name }}</flux:text>
                        <flux:text size="sm" class="truncate text-smoke-muted">{{ $manager->email }}</flux:text>
                    </div>
                </div>

                @if ($manager->businesses->isNotEmpty())
                    @foreach ($manager->businesses as $business)
                        <div class="mt-4 rounded-xl bg-ivory-light p-3">
                            <div class="flex items-center justify-between">
                                <flux:text class="font-medium">{{ $business->name }}</flux:text>
                                <flux:badge size="sm" :color="$business->type === \App\Enums\BusinessType::Restaurant ? 'amber' : 'violet'">
                                    {{ $business->type->label() }}
                                </flux:badge>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2 text-center">
                                <div class="rounded-lg bg-white p-2">
                                    <flux:text size="sm" class="text-smoke-muted">{{ __('Workers') }}</flux:text>
                                    <flux:text class="font-semibold">{{ $business->active_worker_links_count }}</flux:text>
                                </div>
                                <div class="rounded-lg bg-white p-2">
                                    <flux:text size="sm" class="text-smoke-muted">{{ __('Products') }}</flux:text>
                                    <flux:text class="font-semibold">{{ $business->products_count }}</flux:text>
                                </div>
                                <div class="rounded-lg bg-white p-2">
                                    <flux:text size="sm" class="text-smoke-muted">{{ __('Orders') }}</flux:text>
                                    <flux:text class="font-semibold">{{ $business->orders_count }}</flux:text>
                                </div>
                                <div class="rounded-lg bg-white p-2">
                                    <flux:text size="sm" class="text-smoke-muted">{{ __('Revenue') }}</flux:text>
                                    <flux:text class="font-semibold">{{ number_format($business->total_revenue ?? 0, 0) }}</flux:text>
                                </div>
                            </div>
                            <div class="mt-2">
                                <flux:badge size="sm" :color="$business->status->color()">{{ $business->status->label() }}</flux:badge>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="mt-4 rounded-xl border border-dashed border-ivory-dark/50 p-4 text-center">
                        <p class="text-sm text-smoke-muted">{{ __('No business created yet') }}</p>
                    </div>
                @endif

                <div class="mt-3 flex items-center justify-between text-xs text-smoke-muted">
                    <span>{{ __('Joined') }}: {{ $manager->created_at->format('M d, Y') }}</span>
                    @if ($manager->phone)
                        <span>{{ $manager->phone }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-ivory-dark/40 bg-white p-12 text-center">
                <flux:icon.users class="mx-auto size-10 text-smoke-muted/40" />
                <p class="mt-2 text-sm text-smoke-muted">{{ __('No managers found.') }}</p>
            </div>
        @endforelse
    </div>

    {{ $this->managers->links() }}
</div>

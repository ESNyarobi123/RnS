<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-smoke">{{ __('All Businesses') }}</h1>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('Manage all restaurants and salons on the platform') }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search business or owner...')" />
        </div>
        <flux:select wire:model.live="type" class="w-40">
            <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
            @foreach ($this->typeOptions as $opt)
                <flux:select.option :value="$opt['value']">{{ $opt['label'] }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="status" class="w-40">
            <flux:select.option value="">{{ __('All Status') }}</flux:select.option>
            @foreach ($this->statusOptions as $opt)
                <flux:select.option :value="$opt['value']">{{ $opt['label'] }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-ivory-dark/30 bg-ivory-light text-xs uppercase text-smoke-muted">
                    <tr>
                        <th class="px-6 py-3">{{ __('Business') }}</th>
                        <th class="px-6 py-3">{{ __('Owner') }}</th>
                        <th class="px-6 py-3">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-center">{{ __('Workers') }}</th>
                        <th class="px-6 py-3 text-center">{{ __('Products') }}</th>
                        <th class="px-6 py-3 text-center">{{ __('Orders') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Revenue') }}</th>
                        <th class="px-6 py-3">{{ __('Status') }}</th>
                        <th class="px-6 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ivory-dark/30">
                    @forelse ($this->businesses as $business)
                        <tr class="hover:bg-ivory-light">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($business->hasImage())
                                        <img src="{{ $business->imageUrl() }}" class="size-9 rounded-lg object-cover" />
                                    @else
                                        <div class="flex size-9 items-center justify-center rounded-lg bg-ivory">
                                            <flux:icon.building-storefront class="size-4 text-smoke-muted" />
                                        </div>
                                    @endif
                                    <div>
                                        <button wire:click="viewBusiness({{ $business->id }})" class="font-medium text-terra hover:text-terra-dark hover:underline">{{ $business->name }}</button>
                                        <flux:text size="sm" class="text-smoke-muted">{{ $business->phone ?? '-' }}</flux:text>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text>{{ $business->owner->name }}</flux:text>
                                <flux:text size="sm" class="text-smoke-muted">{{ $business->owner->email }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:badge size="sm" :color="$business->type === \App\Enums\BusinessType::Restaurant ? 'amber' : 'violet'">
                                    {{ $business->type->label() }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 text-center">{{ $business->active_worker_links_count }}</td>
                            <td class="px-6 py-4 text-center">{{ $business->products_count }}</td>
                            <td class="px-6 py-4 text-center">{{ $business->orders_count }}</td>
                            <td class="px-6 py-4 text-right font-semibold">{{ number_format($business->total_revenue ?? 0, 0) }}</td>
                            <td class="px-6 py-4">
                                <flux:badge size="sm" :color="$business->status->color()">{{ $business->status->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-4">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item wire:click="viewBusiness({{ $business->id }})" icon="eye">{{ __('View Details') }}</flux:menu.item>
                                        <flux:menu.separator />
                                        @if ($business->status !== \App\Enums\BusinessStatus::Active)
                                            <flux:menu.item wire:click="toggleStatus({{ $business->id }}, 'active')" icon="check-circle">{{ __('Activate') }}</flux:menu.item>
                                        @endif
                                        @if ($business->status !== \App\Enums\BusinessStatus::Suspended)
                                            <flux:menu.item wire:click="toggleStatus({{ $business->id }}, 'suspended')" wire:confirm="{{ __('Are you sure you want to suspend this business?') }}" icon="no-symbol" variant="danger">{{ __('Suspend') }}</flux:menu.item>
                                        @endif
                                        @if ($business->status !== \App\Enums\BusinessStatus::Inactive)
                                            <flux:menu.item wire:click="toggleStatus({{ $business->id }}, 'inactive')" icon="pause-circle">{{ __('Deactivate') }}</flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <flux:icon.building-storefront class="mx-auto size-10 text-smoke-muted/40" />
                                <p class="mt-2 text-sm text-smoke-muted">{{ __('No businesses found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->businesses->links() }}

    {{-- Business Detail Modal --}}
    <flux:modal wire:model="showDetailModal" class="max-w-3xl">
        @if ($this->selectedBusiness)
            @php $biz = $this->selectedBusiness; @endphp
            <div class="space-y-6">
                {{-- Header --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        @if ($biz->hasImage())
                            <img src="{{ $biz->imageUrl() }}" class="size-14 rounded-xl object-cover" />
                        @else
                            <div class="flex size-14 items-center justify-center rounded-xl bg-ivory">
                                <flux:icon.building-storefront class="size-7 text-smoke-muted" />
                            </div>
                        @endif
                        <div>
                            <flux:heading size="lg">{{ $biz->name }}</flux:heading>
                            <div class="mt-1 flex items-center gap-2">
                                <flux:badge size="sm" :color="$biz->type === \App\Enums\BusinessType::Restaurant ? 'amber' : 'violet'">{{ $biz->type->label() }}</flux:badge>
                                <flux:badge size="sm" :color="$biz->status->color()">{{ $biz->status->label() }}</flux:badge>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-6">
                    <div class="rounded-xl bg-ivory-light p-3 text-center">
                        <flux:heading size="lg">{{ $biz->orders_count }}</flux:heading>
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Orders') }}</flux:text>
                    </div>
                    <div class="rounded-xl bg-ivory-light p-3 text-center">
                        <flux:heading size="lg">{{ $biz->products_count }}</flux:heading>
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Products') }}</flux:text>
                    </div>
                    <div class="rounded-xl bg-ivory-light p-3 text-center">
                        <flux:heading size="lg">{{ $biz->categories_count }}</flux:heading>
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Categories') }}</flux:text>
                    </div>
                    <div class="rounded-xl bg-ivory-light p-3 text-center">
                        <flux:heading size="lg">{{ $biz->active_worker_links_count }}</flux:heading>
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Workers') }}</flux:text>
                    </div>
                    <div class="rounded-xl bg-ivory-light p-3 text-center">
                        <flux:heading size="lg">{{ $biz->feedbacks_count }}</flux:heading>
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Reviews') }}</flux:text>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-3 text-center">
                        <flux:heading size="lg" class="text-emerald-700">{{ number_format($biz->total_revenue ?? 0, 0) }}</flux:heading>
                        <flux:text size="sm" class="text-emerald-600">{{ __('Revenue') }}</flux:text>
                    </div>
                </div>

                {{-- Owner Info --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-ivory-light p-3">
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Owner') }}</flux:text>
                        <flux:text class="font-medium">{{ $biz->owner->name }}</flux:text>
                        <flux:text size="sm" class="text-smoke-muted">{{ $biz->owner->email }}</flux:text>
                    </div>
                    <div class="rounded-xl bg-ivory-light p-3">
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Details') }}</flux:text>
                        <flux:text>{{ $biz->phone ?? __('No phone') }}</flux:text>
                        <flux:text size="sm" class="text-smoke-muted">{{ $biz->address ?? __('No address') }}</flux:text>
                    </div>
                </div>

                @if ($biz->feedbacks_count > 0)
                    <div class="rounded-xl bg-amber-50 p-3">
                        <flux:text size="sm" class="text-amber-700">{{ __('Avg Rating') }}</flux:text>
                        <div class="flex items-center gap-2">
                            <flux:heading size="lg">{{ number_format($biz->feedbacks_avg_rating, 1) }}</flux:heading>
                            <flux:text size="sm" class="text-amber-600">/5 ({{ $biz->feedbacks_count }} {{ __('reviews') }})</flux:text>
                        </div>
                    </div>
                @endif

                {{-- Active Workers --}}
                @if ($biz->activeWorkerLinks->isNotEmpty())
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Active Workers') }}</flux:heading>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($biz->activeWorkerLinks as $link)
                                <div class="flex items-center gap-3 rounded-xl bg-ivory-light p-3">
                                    <div class="flex size-8 items-center justify-center rounded-full bg-terra/10 text-xs font-bold text-terra">
                                        {{ $link->worker?->initials() }}
                                    </div>
                                    <div>
                                        <flux:text class="font-medium">{{ $link->worker?->name }}</flux:text>
                                        <div class="flex items-center gap-1">
                                            <flux:text size="sm" class="font-mono text-smoke-muted">{{ $link->worker?->global_number }}</flux:text>
                                            <flux:badge size="sm" :color="$link->link_type === \App\Enums\LinkType::Permanent ? 'blue' : 'amber'">{{ $link->link_type->label() }}</flux:badge>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Recent Orders --}}
                @if ($biz->orders->isNotEmpty())
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Recent Orders') }}</flux:heading>
                        <div class="overflow-hidden rounded-xl border border-ivory-dark/40">
                            <table class="w-full text-sm">
                                <thead class="bg-ivory-light text-xs text-smoke-muted">
                                    <tr>
                                        <th class="px-4 py-2 text-left">{{ __('Order #') }}</th>
                                        <th class="px-4 py-2 text-left">{{ __('Worker') }}</th>
                                        <th class="px-4 py-2 text-right">{{ __('Total') }}</th>
                                        <th class="px-4 py-2">{{ __('Status') }}</th>
                                        <th class="px-4 py-2 text-right">{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ivory-dark/30">
                                    @foreach ($biz->orders as $order)
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-sm">{{ $order->order_number }}</td>
                                            <td class="px-4 py-2">{{ $order->worker?->name ?? '-' }}</td>
                                            <td class="px-4 py-2 text-right font-medium">{{ number_format($order->total, 0) }}</td>
                                            <td class="px-4 py-2">
                                                <flux:badge size="sm" :color="$order->status->color()">{{ $order->status->label() }}</flux:badge>
                                            </td>
                                            <td class="px-4 py-2 text-right text-sm text-smoke-muted">{{ $order->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end">
                    <flux:button wire:click="$set('showDetailModal', false)">{{ __('Close') }}</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>

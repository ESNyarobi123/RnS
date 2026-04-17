<div class="flex h-full w-full flex-1 flex-col gap-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-smoke">{{ __('Orders') }}</h1>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('Track and manage all orders for your business') }}</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-4">
            <div class="flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-smoke/5">
                    <flux:icon.clipboard-document-list class="size-4 text-smoke-muted" />
                </div>
                <p class="text-xs font-medium text-smoke-muted">{{ __('Total') }}</p>
            </div>
            <p class="mt-2 text-2xl font-bold text-smoke">{{ $this->orderSummary['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-terra-200 bg-terra-50 p-4">
            <div class="flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-terra/10">
                    <flux:icon.clock class="size-4 text-terra" />
                </div>
                <p class="text-xs font-medium text-terra-dark">{{ __('Pending') }}</p>
            </div>
            <p class="mt-2 text-2xl font-bold text-smoke">{{ $this->orderSummary['pending'] }}</p>
        </div>
        <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4">
            <div class="flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-100">
                    <flux:icon.arrow-path class="size-4 text-indigo-600" />
                </div>
                <p class="text-xs font-medium text-indigo-700">{{ __('In Progress') }}</p>
            </div>
            <p class="mt-2 text-2xl font-bold text-smoke">{{ $this->orderSummary['in_progress'] }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100">
                    <flux:icon.check-circle class="size-4 text-emerald-600" />
                </div>
                <p class="text-xs font-medium text-emerald-700">{{ __('Done Today') }}</p>
            </div>
            <p class="mt-2 text-2xl font-bold text-smoke">{{ $this->orderSummary['completed_today'] }}</p>
        </div>
        <div class="col-span-2 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 sm:col-span-1">
            <div class="flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100">
                    <flux:icon.currency-dollar class="size-4 text-emerald-600" />
                </div>
                <p class="text-xs font-medium text-emerald-700">{{ __('Revenue Today') }}</p>
            </div>
            <p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($this->orderSummary['revenue_today'], 0) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search orders, customers, workers...') }}" icon="magnifying-glass" size="sm" />
        </div>
        <flux:select wire:model.live="status" size="sm" class="w-40">
            <option value="">{{ __('All Statuses') }}</option>
            @foreach ($this->statusOptions as $opt)
                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
            @endforeach
        </flux:select>
        @if ($search || $status)
            <flux:button size="sm" variant="ghost" wire:click="resetFilters">{{ __('Clear') }}</flux:button>
        @endif
    </div>

    {{-- Orders Table --}}
    <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-ivory-dark/30 bg-ivory-light text-xs uppercase text-smoke-muted">
                    <tr>
                        <th class="px-5 py-3">{{ __('Order') }}</th>
                        <th class="px-5 py-3">{{ __('Worker') }}</th>
                        <th class="px-5 py-3 text-center">{{ __('Items') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Total') }}</th>
                        <th class="px-5 py-3">{{ __('Status') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Time') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ivory-dark/30">
                    @forelse ($this->orders as $order)
                        <tr class="hover:bg-ivory-light">
                            <td class="px-5 py-3">
                                <button wire:click="viewOrder({{ $order->id }})" class="text-left">
                                    <span class="font-mono text-sm font-semibold text-terra hover:text-terra-dark">{{ $order->order_number }}</span>
                                    @if ($order->customer_name)
                                        <p class="text-xs text-smoke-muted">{{ $order->customer_name }}</p>
                                    @endif
                                </button>
                            </td>
                            <td class="px-5 py-3">
                                @if ($order->worker)
                                    <div class="flex items-center gap-2">
                                        <div class="flex size-7 items-center justify-center rounded-full bg-terra/10 text-xs font-bold text-terra">
                                            {{ $order->worker->initials() }}
                                        </div>
                                        <span class="text-sm text-smoke">{{ $order->worker->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-smoke-muted">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex size-6 items-center justify-center rounded-full bg-ivory-light text-xs font-semibold text-smoke">{{ $order->items->count() }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-smoke">{{ number_format($order->total, 0) }}</td>
                            <td class="px-5 py-3">
                                <flux:badge size="sm" :color="$order->status->color()">{{ $order->status->label() }}</flux:badge>
                            </td>
                            <td class="px-5 py-3 text-right text-xs text-smoke-muted">{{ $order->created_at->diffForHumans() }}</td>
                            <td class="px-5 py-3 text-right">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button size="sm" variant="ghost" icon="ellipsis-vertical" />
                                    <flux:menu>
                                        <flux:menu.item wire:click="viewOrder({{ $order->id }})" icon="eye">{{ __('View Details') }}</flux:menu.item>
                                        @if ($order->status === \App\Enums\OrderStatus::Pending)
                                            <flux:menu.item wire:click="updateOrderStatus({{ $order->id }}, 'confirmed')" icon="check">{{ __('Confirm') }}</flux:menu.item>
                                        @endif
                                        @if ($order->status === \App\Enums\OrderStatus::Confirmed)
                                            <flux:menu.item wire:click="updateOrderStatus({{ $order->id }}, 'in_progress')" icon="arrow-path">{{ __('Start') }}</flux:menu.item>
                                        @endif
                                        @if (in_array($order->status, [\App\Enums\OrderStatus::Confirmed, \App\Enums\OrderStatus::InProgress]))
                                            <flux:menu.item wire:click="updateOrderStatus({{ $order->id }}, 'completed')" icon="check-circle">{{ __('Complete') }}</flux:menu.item>
                                        @endif
                                        @if ($order->status !== \App\Enums\OrderStatus::Completed && $order->status !== \App\Enums\OrderStatus::Cancelled)
                                            <flux:menu.separator />
                                            <flux:menu.item wire:click="updateOrderStatus({{ $order->id }}, 'cancelled')" icon="x-circle" variant="danger">{{ __('Cancel') }}</flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <flux:icon.clipboard-document-list class="mx-auto size-8 text-smoke-muted/40" />
                                <p class="mt-3 text-sm text-smoke-muted">{{ __('No orders found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($this->orders->hasPages())
            <div class="border-t border-ivory-dark/30 px-5 py-3">
                {{ $this->orders->links() }}
            </div>
        @endif
    </div>

    {{-- Order Detail Modal --}}
    <flux:modal wire:model="showDetailModal" class="w-full max-w-2xl">
        @if ($this->selectedOrder)
            <div class="space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ $this->selectedOrder->order_number }}</flux:heading>
                        <flux:text class="text-smoke-muted">{{ $this->selectedOrder->created_at->format('M d, Y H:i') }}</flux:text>
                    </div>
                    <flux:badge size="sm" :color="$this->selectedOrder->status->color()">{{ $this->selectedOrder->status->label() }}</flux:badge>
                </div>

                @if ($this->selectedOrder->customer_name || $this->selectedOrder->worker)
                    <div class="grid gap-4 rounded-xl bg-ivory-light p-4 sm:grid-cols-2">
                        @if ($this->selectedOrder->customer_name)
                            <div>
                                <p class="text-xs font-medium text-smoke-muted">{{ __('Customer') }}</p>
                                <p class="text-sm font-semibold text-smoke">{{ $this->selectedOrder->customer_name }}</p>
                            </div>
                        @endif
                        @if ($this->selectedOrder->worker)
                            <div>
                                <p class="text-xs font-medium text-smoke-muted">{{ __('Worker') }}</p>
                                <p class="text-sm font-semibold text-smoke">{{ $this->selectedOrder->worker->name }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Items --}}
                <div class="overflow-hidden rounded-xl border border-ivory-dark/30">
                    <table class="w-full text-sm">
                        <thead class="bg-ivory-light text-xs text-smoke-muted">
                            <tr>
                                <th class="px-4 py-2 text-left">{{ __('Item') }}</th>
                                <th class="px-4 py-2 text-center">{{ __('Qty') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Price') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ivory-dark/30">
                            @foreach ($this->selectedOrder->items as $item)
                                <tr>
                                    <td class="px-4 py-2 font-medium text-smoke">{{ $item->product?->name ?? __('Deleted') }}</td>
                                    <td class="px-4 py-2 text-center text-smoke-muted">{{ $item->quantity }}</td>
                                    <td class="px-4 py-2 text-right text-smoke-muted">{{ number_format($item->unit_price, 0) }}</td>
                                    <td class="px-4 py-2 text-right font-semibold text-smoke">{{ number_format($item->total_price, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end rounded-xl bg-ivory-light px-4 py-3">
                    <div class="text-right">
                        <p class="text-xs text-smoke-muted">{{ __('Total') }}</p>
                        <p class="text-xl font-bold text-smoke">{{ number_format($this->selectedOrder->total, 0) }}</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:button variant="ghost" wire:click="$toggle('showDetailModal')">{{ __('Close') }}</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>

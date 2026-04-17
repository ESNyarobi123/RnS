<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('All Orders') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Track and manage orders across all businesses') }}</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-4">
            <p class="text-sm text-smoke-muted">{{ __('Total') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->orderSummary['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-terra-200 bg-terra-50 p-4">
            <p class="text-sm font-medium text-terra-dark">{{ __('Pending') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->orderSummary['pending'] }}</p>
        </div>
        <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4">
            <p class="text-sm font-medium text-indigo-700">{{ __('In Progress') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->orderSummary['in_progress'] }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-medium text-emerald-700">{{ __('Completed') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->orderSummary['completed'] }}</p>
        </div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="text-sm font-medium text-red-700">{{ __('Cancelled') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->orderSummary['cancelled'] }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-medium text-emerald-700">{{ __('Total Value') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ number_format($this->orderSummary['total_value'], 0) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search order#, customer, business...')" />
        </div>
        <flux:select wire:model.live="status" class="w-40">
            <flux:select.option value="">{{ __('All Status') }}</flux:select.option>
            @foreach ($this->statusOptions as $opt)
                <flux:select.option :value="$opt['value']">{{ $opt['label'] }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="businessType" class="w-40">
            <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
            <flux:select.option value="restaurant">{{ __('Restaurant') }}</flux:select.option>
            <flux:select.option value="salon">{{ __('Salon') }}</flux:select.option>
        </flux:select>
        <flux:input wire:model.live="dateFrom" type="date" :label="__('From')" class="w-40" />
        <flux:input wire:model.live="dateTo" type="date" :label="__('To')" class="w-40" />
        @if ($search || $status || $dateFrom || $dateTo || $businessType)
            <flux:button variant="ghost" size="sm" wire:click="resetFilters" icon="x-mark">{{ __('Clear') }}</flux:button>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-ivory-dark/30 bg-ivory-light text-xs uppercase text-smoke-muted">
                    <tr>
                        <th class="px-6 py-3">{{ __('Order #') }}</th>
                        <th class="px-6 py-3">{{ __('Business') }}</th>
                        <th class="px-6 py-3">{{ __('Customer') }}</th>
                        <th class="px-6 py-3">{{ __('Worker') }}</th>
                        <th class="px-6 py-3 text-center">{{ __('Items') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Total') }}</th>
                        <th class="px-6 py-3">{{ __('Status') }}</th>
                        <th class="px-6 py-3">{{ __('Date') }}</th>
                        <th class="px-6 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ivory-dark/30">
                    @forelse ($this->orders as $order)
                        <tr class="hover:bg-ivory-light">
                            <td class="px-6 py-4">
                                <button wire:click="viewOrder({{ $order->id }})" class="font-mono text-sm font-semibold text-terra hover:text-terra-dark hover:underline">
                                    {{ $order->order_number }}
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text class="font-medium">{{ $order->business?->name }}</flux:text>
                                <flux:badge size="sm" :color="$order->business?->type === \App\Enums\BusinessType::Restaurant ? 'amber' : 'violet'">
                                    {{ $order->business?->type->label() }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text>{{ $order->customer_name ?? '-' }}</flux:text>
                                @if ($order->customer_phone)
                                    <flux:text size="sm" class="text-smoke-muted">{{ $order->customer_phone }}</flux:text>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($order->worker)
                                    <flux:text>{{ $order->worker->name }}</flux:text>
                                    <flux:text size="sm" class="font-mono text-smoke-muted">{{ $order->worker->global_number }}</flux:text>
                                @else
                                    <flux:text class="text-smoke-muted">-</flux:text>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">{{ $order->items->count() }}</td>
                            <td class="px-6 py-4 text-right font-semibold">{{ number_format($order->total, 0) }}</td>
                            <td class="px-6 py-4">
                                <flux:badge size="sm" :color="$order->status->color()">{{ $order->status->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text size="sm">{{ $order->created_at->format('M d, Y') }}</flux:text>
                                <flux:text size="sm" class="text-smoke-muted">{{ $order->created_at->format('H:i') }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item wire:click="viewOrder({{ $order->id }})" icon="eye">{{ __('View Details') }}</flux:menu.item>
                                        @if ($order->status !== \App\Enums\OrderStatus::Confirmed && $order->status !== \App\Enums\OrderStatus::Completed && $order->status !== \App\Enums\OrderStatus::Cancelled)
                                            <flux:menu.item wire:click="updateOrderStatus({{ $order->id }}, 'confirmed')" icon="check">{{ __('Confirm') }}</flux:menu.item>
                                        @endif
                                        @if ($order->status !== \App\Enums\OrderStatus::InProgress && $order->status !== \App\Enums\OrderStatus::Completed && $order->status !== \App\Enums\OrderStatus::Cancelled)
                                            <flux:menu.item wire:click="updateOrderStatus({{ $order->id }}, 'in_progress')" icon="arrow-path">{{ __('In Progress') }}</flux:menu.item>
                                        @endif
                                        @if ($order->status !== \App\Enums\OrderStatus::Completed && $order->status !== \App\Enums\OrderStatus::Cancelled)
                                            <flux:menu.item wire:click="updateOrderStatus({{ $order->id }}, 'completed')" icon="check-circle">{{ __('Complete') }}</flux:menu.item>
                                        @endif
                                        @if ($order->status !== \App\Enums\OrderStatus::Cancelled && $order->status !== \App\Enums\OrderStatus::Completed)
                                            <flux:menu.separator />
                                            <flux:menu.item wire:click="updateOrderStatus({{ $order->id }}, 'cancelled')" wire:confirm="{{ __('Are you sure you want to cancel this order?') }}" icon="x-circle" variant="danger">{{ __('Cancel') }}</flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <flux:icon.clipboard-document-list class="mx-auto size-10 text-smoke-muted/40" />
                                <p class="mt-2 text-sm text-smoke-muted">{{ __('No orders found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->orders->links() }}

    {{-- Order Detail Modal --}}
    <flux:modal wire:model="showDetailModal" class="max-w-2xl">
        @if ($this->selectedOrder)
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ $this->selectedOrder->order_number }}</flux:heading>
                    <flux:badge size="sm" :color="$this->selectedOrder->status->color()">{{ $this->selectedOrder->status->label() }}</flux:badge>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-ivory-light p-3">
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Business') }}</flux:text>
                        <flux:text class="font-medium">{{ $this->selectedOrder->business?->name }}</flux:text>
                    </div>
                    <div class="rounded-xl bg-ivory-light p-3">
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Worker') }}</flux:text>
                        <flux:text class="font-medium">{{ $this->selectedOrder->worker?->name ?? '-' }}</flux:text>
                        @if ($this->selectedOrder->worker)
                            <flux:text size="sm" class="font-mono text-smoke-muted">{{ $this->selectedOrder->worker->global_number }}</flux:text>
                        @endif
                    </div>
                    <div class="rounded-xl bg-ivory-light p-3">
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Customer') }}</flux:text>
                        <flux:text class="font-medium">{{ $this->selectedOrder->customer_name ?? '-' }}</flux:text>
                        @if ($this->selectedOrder->customer_phone)
                            <flux:text size="sm" class="text-smoke-muted">{{ $this->selectedOrder->customer_phone }}</flux:text>
                        @endif
                    </div>
                    <div class="rounded-xl bg-ivory-light p-3">
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Date') }}</flux:text>
                        <flux:text class="font-medium">{{ $this->selectedOrder->created_at->format('M d, Y H:i') }}</flux:text>
                    </div>
                </div>

                {{-- Order Items --}}
                <div>
                    <flux:heading size="sm" class="mb-2">{{ __('Items') }} ({{ $this->selectedOrder->items->count() }})</flux:heading>
                    <div class="overflow-hidden rounded-xl border border-ivory-dark/40">
                        <table class="w-full text-sm">
                            <thead class="bg-ivory-light text-xs text-smoke-muted">
                                <tr>
                                    <th class="px-4 py-2 text-left">{{ __('Product') }}</th>
                                    <th class="px-4 py-2 text-center">{{ __('Qty') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Price') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ivory-dark/30">
                                @foreach ($this->selectedOrder->items as $item)
                                    <tr>
                                        <td class="px-4 py-2">{{ $item->product?->name ?? __('Deleted Product') }}</td>
                                        <td class="px-4 py-2 text-center">{{ $item->quantity }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format($item->unit_price, 0) }}</td>
                                        <td class="px-4 py-2 text-right font-medium">{{ number_format($item->total_price, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-ivory-dark/40 bg-ivory-light">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-right font-medium">{{ __('Subtotal') }}</td>
                                    <td class="px-4 py-2 text-right font-medium">{{ number_format($this->selectedOrder->subtotal, 0) }}</td>
                                </tr>
                                @if ($this->selectedOrder->tax > 0)
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-right text-sm text-smoke-muted">{{ __('Tax') }}</td>
                                        <td class="px-4 py-2 text-right text-sm">{{ number_format($this->selectedOrder->tax, 0) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-right font-semibold">{{ __('Total') }}</td>
                                    <td class="px-4 py-2 text-right font-semibold">{{ number_format($this->selectedOrder->total, 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                @if ($this->selectedOrder->notes)
                    <div class="rounded-xl bg-ivory-light p-3">
                        <flux:text size="sm" class="text-smoke-muted">{{ __('Notes') }}</flux:text>
                        <flux:text>{{ $this->selectedOrder->notes }}</flux:text>
                    </div>
                @endif

                @if ($this->selectedOrder->payments->isNotEmpty())
                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Payments') }}</flux:heading>
                        @foreach ($this->selectedOrder->payments as $payment)
                            <div class="flex items-center justify-between rounded-xl bg-ivory-light px-3 py-2">
                                <div>
                                    <flux:badge size="sm" color="zinc">{{ $payment->method->label() }}</flux:badge>
                                    <flux:badge size="sm" :color="$payment->status->color()">{{ $payment->status->label() }}</flux:badge>
                                </div>
                                <flux:text class="font-semibold">{{ number_format($payment->amount, 0) }}</flux:text>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end">
                    <flux:button wire:click="$set('showDetailModal', false)">{{ __('Close') }}</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>

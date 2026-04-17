<div class="flex h-full w-full flex-1 flex-col gap-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-smoke">{{ __('My Orders') }}</h1>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('All orders assigned to you at :business.', ['business' => $this->business?->name ?? '—']) }}</p>
        </div>
    </div>

    @if (! $this->business)
        <div class="rounded-2xl border-2 border-dashed border-ivory-dark/50 bg-ivory-light p-10 text-center">
            <flux:icon.link-slash class="mx-auto size-10 text-smoke-muted" />
            <p class="mt-4 text-sm text-smoke-muted">{{ __('You need to be linked to a business to see orders.') }}</p>
        </div>
    @else
        {{-- Status Filter Tabs --}}
        <div class="flex flex-wrap items-center gap-2">
            @php
                $tabs = [
                    '' => ['label' => __('All'), 'count' => $this->statusCounts['all'] ?? 0, 'color' => 'bg-smoke'],
                    'pending' => ['label' => __('Pending'), 'count' => $this->statusCounts['pending'] ?? 0, 'color' => 'bg-amber-500'],
                    'in_progress' => ['label' => __('In Progress'), 'count' => $this->statusCounts['in_progress'] ?? 0, 'color' => 'bg-indigo-500'],
                    'completed' => ['label' => __('Completed'), 'count' => $this->statusCounts['completed'] ?? 0, 'color' => 'bg-emerald-500'],
                    'cancelled' => ['label' => __('Cancelled'), 'count' => $this->statusCounts['cancelled'] ?? 0, 'color' => 'bg-red-500'],
                ];
            @endphp
            @foreach ($tabs as $key => $tab)
                <button
                    wire:click="$set('status', '{{ $key }}')"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ $status === $key ? 'bg-smoke text-white' : 'bg-white text-smoke-muted hover:bg-ivory-light border border-ivory-dark/30' }}"
                >
                    <span class="size-2 rounded-full {{ $tab['color'] }}"></span>
                    {{ $tab['label'] }}
                    <span class="rounded-full {{ $status === $key ? 'bg-white/20' : 'bg-ivory-light' }} px-1.5 py-0.5 text-xs font-bold">{{ $tab['count'] }}</span>
                </button>
            @endforeach

            <div class="ml-auto flex-1 sm:max-w-xs">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search order or customer...') }}" icon="magnifying-glass" />
            </div>
        </div>

        {{-- Orders Table --}}
        <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
            @if ($this->orders->isEmpty())
                <div class="p-10 text-center">
                    <flux:icon.clipboard-document-list class="mx-auto size-10 text-smoke-muted/30" />
                    <p class="mt-3 text-sm text-smoke-muted">{{ __('No orders found.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-ivory-dark/30 bg-ivory-light text-xs uppercase text-smoke-muted">
                            <tr>
                                <th class="px-5 py-3">{{ __('Order') }}</th>
                                <th class="px-5 py-3">{{ __('Customer') }}</th>
                                <th class="px-5 py-3 text-center">{{ __('Items') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Total') }}</th>
                                <th class="px-5 py-3">{{ __('Status') }}</th>
                                <th class="px-5 py-3">{{ __('Payment') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ivory-dark/30">
                            @foreach ($this->orders as $order)
                                <tr class="hover:bg-ivory-light">
                                    <td class="px-5 py-3">
                                        <span class="font-mono text-sm font-semibold text-smoke">{{ $order->order_number }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-sm text-smoke">{{ $order->customer_name ?? '—' }}</span>
                                        @if ($order->customer_phone)
                                            <p class="text-xs text-smoke-muted">{{ $order->customer_phone }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex size-6 items-center justify-center rounded-full bg-ivory-light text-xs font-semibold text-smoke">{{ $order->items->count() }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="font-semibold text-smoke">{{ number_format($order->total, 0) }}</span>
                                        <span class="text-xs text-smoke-muted">TZS</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <flux:badge size="sm" :color="$order->status->color()">{{ $order->status->label() }}</flux:badge>
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($order->payments->isNotEmpty())
                                            @php $lastPayment = $order->payments->last(); @endphp
                                            <flux:badge size="sm" :color="$lastPayment->status->color()">{{ $lastPayment->status->label() }}</flux:badge>
                                        @else
                                            <span class="text-xs text-smoke-muted">{{ __('Unpaid') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="text-xs text-smoke-muted">{{ $order->created_at->format('M d, Y') }}</span>
                                        <p class="text-xs text-smoke-muted/60">{{ $order->created_at->format('H:i') }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($this->orders->hasPages())
                    <div class="border-t border-ivory-dark/30 px-5 py-3">
                        {{ $this->orders->links() }}
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>

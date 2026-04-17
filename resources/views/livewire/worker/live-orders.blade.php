<div class="flex h-full w-full flex-1 flex-col gap-4" wire:poll.10s="$refresh">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-smoke">{{ __('Live Orders') }}</h1>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('Your assigned orders in real-time') }}</p>
        </div>

        @if ($this->business)
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 rounded-xl bg-white px-3 py-2 ring-1 ring-ivory-dark/30">
                    <flux:icon.clipboard-document-list class="size-4 text-smoke-muted" />
                    <span class="text-sm font-semibold text-smoke">{{ $this->todayStats['total'] }}</span>
                    <span class="text-xs text-smoke-muted">{{ __('today') }}</span>
                </div>
                <div class="flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 ring-1 ring-emerald-200">
                    <flux:icon.check-circle class="size-4 text-emerald-600" />
                    <span class="text-sm font-semibold text-smoke">{{ $this->todayStats['completed'] }}</span>
                    <span class="text-xs text-emerald-600">{{ __('done') }}</span>
                </div>
                <div class="flex items-center gap-2 rounded-xl bg-terra-50 px-3 py-2 ring-1 ring-terra-200">
                    <flux:icon.banknotes class="size-4 text-terra" />
                    <span class="text-sm font-semibold text-smoke">{{ number_format($this->todayStats['revenue'], 0) }}</span>
                    <span class="text-xs text-terra">TZS</span>
                </div>
            </div>
        @endif
    </div>

    @if (! $this->business)
        <div class="rounded-2xl border-2 border-dashed border-ivory-dark/50 bg-ivory-light p-10 text-center">
            <flux:icon.link-slash class="mx-auto size-10 text-smoke-muted" />
            <h2 class="mt-4 text-lg font-bold text-smoke">{{ __('Not Linked to a Business') }}</h2>
            <p class="mt-2 text-sm text-smoke-muted">{{ __('You need to be linked to a business to see live orders.') }}</p>
        </div>
    @else
        {{-- Kanban Board --}}
        <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach (\App\Enums\OrderStatus::liveStatuses() as $status)
                @php
                    $orders = match ($status) {
                        \App\Enums\OrderStatus::Pending => $this->pendingOrders,
                        \App\Enums\OrderStatus::Preparing => $this->preparingOrders,
                        \App\Enums\OrderStatus::Served => $this->servedOrders,
                        \App\Enums\OrderStatus::Completed => $this->completedOrders,
                    };
                    $columnColors = match ($status) {
                        \App\Enums\OrderStatus::Pending => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'dot' => 'bg-amber-400', 'count' => 'bg-amber-100 text-amber-700'],
                        \App\Enums\OrderStatus::Preparing => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'dot' => 'bg-indigo-400', 'count' => 'bg-indigo-100 text-indigo-700'],
                        \App\Enums\OrderStatus::Served => ['bg' => 'bg-cyan-50', 'border' => 'border-cyan-200', 'dot' => 'bg-cyan-400', 'count' => 'bg-cyan-100 text-cyan-700'],
                        \App\Enums\OrderStatus::Completed => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-400', 'count' => 'bg-emerald-100 text-emerald-700'],
                    };
                @endphp
                <div class="flex flex-col rounded-2xl border {{ $columnColors['border'] }} {{ $columnColors['bg'] }}">
                    {{-- Column Header --}}
                    <div class="flex items-center justify-between border-b {{ $columnColors['border'] }} px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full {{ $columnColors['dot'] }}"></span>
                            <h3 class="text-sm font-semibold text-smoke">{{ $status->liveLabel($this->businessType) }}</h3>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $columnColors['count'] }}">{{ $orders->count() }}</span>
                    </div>

                    {{-- Order Cards --}}
                    <div class="flex-1 space-y-3 overflow-y-auto p-3" style="max-height: 65vh;">
                        @forelse ($orders as $order)
                            <button
                                wire:click="viewOrder({{ $order->id }})"
                                class="w-full rounded-xl border border-ivory-dark/30 bg-white p-3 text-left shadow-sm transition hover:shadow-md hover:ring-1 hover:ring-terra/30"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-xs font-bold text-terra">{{ $order->order_number }}</p>
                                        <p class="mt-0.5 text-sm font-semibold text-smoke">{{ $order->customer_name }}</p>
                                    </div>
                                    <p class="text-sm font-bold text-smoke">{{ number_format($order->total, 0) }}</p>
                                </div>

                                {{-- Items Summary --}}
                                <div class="mt-2 space-y-0.5">
                                    @foreach ($order->items->take(3) as $item)
                                        <p class="text-xs text-smoke-muted">{{ $item->quantity }}x {{ $item->product?->name ?? 'Deleted' }}</p>
                                    @endforeach
                                    @if ($order->items->count() > 3)
                                        <p class="text-xs text-smoke-muted">+{{ $order->items->count() - 3 }} {{ __('more') }}</p>
                                    @endif
                                </div>

                                {{-- Time --}}
                                <p class="mt-2 text-xs text-smoke-muted">{{ $order->created_at->diffForHumans() }}</p>
                            </button>
                        @empty
                            <div class="py-8 text-center">
                                <p class="text-xs text-smoke-muted">{{ __('No orders') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Order Detail Modal --}}
    <flux:modal wire:model="showOrderDetail" class="w-full max-w-md">
        @if ($this->selectedOrder)
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">{{ $this->selectedOrder->order_number }}</flux:heading>
                    <flux:text class="mt-1 text-smoke-muted">{{ $this->selectedOrder->customer_name }}</flux:text>
                </div>

                {{-- Status --}}
                <div class="flex items-center gap-2">
                    <flux:badge size="sm" :color="$this->selectedOrder->status->color()">{{ $this->selectedOrder->status->liveLabel($this->businessType) }}</flux:badge>
                    @if ($this->selectedOrder->customer_phone)
                        <span class="text-xs text-smoke-muted">{{ $this->selectedOrder->customer_phone }}</span>
                    @endif
                </div>

                {{-- Items --}}
                <div>
                    <h3 class="mb-2 text-sm font-semibold text-smoke">{{ __('Items') }}</h3>
                    <div class="divide-y divide-ivory-dark/30 rounded-xl border border-ivory-dark/30 bg-ivory-light">
                        @foreach ($this->selectedOrder->items as $item)
                            <div class="flex items-center justify-between px-3 py-2">
                                <div>
                                    <p class="text-sm font-medium text-smoke">{{ $item->product?->name ?? 'Deleted' }}</p>
                                    <p class="text-xs text-smoke-muted">{{ $item->quantity }} × {{ number_format($item->unit_price, 0) }}</p>
                                </div>
                                <p class="text-sm font-semibold text-smoke">{{ number_format($item->total_price, 0) }}</p>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between px-3 py-2.5">
                            <p class="text-sm font-bold text-smoke">{{ __('Total') }}</p>
                            <p class="text-lg font-bold text-terra">{{ number_format($this->selectedOrder->total, 0) }} TZS</p>
                        </div>
                    </div>
                </div>

                {{-- Payment Status --}}
                @if ($this->selectedOrder->payments->isNotEmpty())
                    <div>
                        <h3 class="mb-2 text-sm font-semibold text-smoke">{{ __('Payment') }}</h3>
                        @foreach ($this->selectedOrder->payments as $payment)
                            <div class="flex items-center justify-between rounded-xl bg-ivory-light px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm" :color="$payment->status->color()">{{ $payment->status->label() }}</flux:badge>
                                    <span class="text-xs text-smoke-muted">{{ $payment->method->label() }}</span>
                                </div>
                                <span class="text-sm font-semibold text-smoke">{{ number_format($payment->amount, 0) }} TZS</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Notes --}}
                @if ($this->selectedOrder->notes)
                    <div>
                        <h3 class="mb-1 text-sm font-semibold text-smoke">{{ __('Notes') }}</h3>
                        <p class="rounded-xl bg-ivory-light px-3 py-2 text-sm text-smoke-muted">{{ $this->selectedOrder->notes }}</p>
                    </div>
                @endif

                {{-- Time --}}
                <div class="flex items-center justify-between text-xs text-smoke-muted">
                    <span>{{ __('Created') }} {{ $this->selectedOrder->created_at->format('M d, H:i') }}</span>
                    @if ($this->selectedOrder->completed_at)
                        <span>{{ __('Completed') }} {{ $this->selectedOrder->completed_at->format('H:i') }}</span>
                    @endif
                </div>

                <div class="flex justify-end">
                    <flux:button variant="ghost" wire:click="$toggle('showOrderDetail')">{{ __('Close') }}</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>

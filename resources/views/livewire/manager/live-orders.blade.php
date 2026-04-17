<div class="flex h-full w-full flex-1 flex-col gap-4" wire:poll.10s="$refresh">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-smoke">{{ __('Live Orders') }}</h1>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('Track and manage orders in real-time') }}</p>
        </div>
        <flux:button wire:click="openCreateOrder" class="!bg-terra !text-white hover:!bg-terra-dark">
            <flux:icon.plus class="-ml-1 mr-1 size-4" />
            {{ __('New Order') }}
        </flux:button>
    </div>

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
                        <div class="rounded-xl border border-ivory-dark/30 bg-white p-3 shadow-sm transition hover:shadow-md">
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

                            {{-- Actions --}}
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @if ($status === \App\Enums\OrderStatus::Pending)
                                    <flux:button size="sm" wire:click="moveToStatus({{ $order->id }}, 'preparing')" class="!bg-indigo-600 !text-white hover:!bg-indigo-700">
                                        {{ $this->businessType === \App\Enums\BusinessType::Salon ? __('Start') : __('Prepare') }}
                                    </flux:button>
                                    <flux:button size="sm" variant="ghost" wire:click="cancelOrder({{ $order->id }})" wire:confirm="{{ __('Cancel this order?') }}" class="!text-red-500">
                                        {{ __('Cancel') }}
                                    </flux:button>
                                @elseif ($status === \App\Enums\OrderStatus::Preparing)
                                    <flux:button size="sm" wire:click="moveToStatus({{ $order->id }}, 'served')" class="!bg-cyan-600 !text-white hover:!bg-cyan-700">
                                        {{ $this->businessType === \App\Enums\BusinessType::Salon ? __('Ready') : __('Served') }}
                                    </flux:button>
                                @elseif ($status === \App\Enums\OrderStatus::Served)
                                    @php $isPaid = $order->payments->where('status', \App\Enums\PaymentStatus::Completed)->isNotEmpty(); @endphp
                                    @if ($isPaid)
                                        <flux:button size="sm" wire:click="moveToStatus({{ $order->id }}, 'completed')" class="!bg-emerald-600 !text-white hover:!bg-emerald-700">
                                            {{ __('Complete') }}
                                        </flux:button>
                                    @else
                                        <flux:button size="sm" wire:click="openPayment({{ $order->id }})" class="!bg-terra !text-white hover:!bg-terra-dark">
                                            <flux:icon.banknotes class="-ml-0.5 mr-1 size-3.5" />
                                            {{ __('Pay') }}
                                        </flux:button>
                                        <flux:button size="sm" variant="ghost" wire:click="markPaidManual({{ $order->id }})" wire:confirm="{{ __('Mark as paid (cash/manual)?') }}" class="!text-emerald-600">
                                            {{ __('Paid') }}
                                        </flux:button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <p class="text-xs text-smoke-muted">{{ __('No orders') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- Create Order Modal --}}
    <flux:modal wire:model="showCreateModal" class="w-full max-w-2xl">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('New Order') }}</flux:heading>
                <flux:text class="mt-1 text-smoke-muted">{{ __('Create a new order for your customer.') }}</flux:text>
            </div>

            {{-- Customer Info --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="customerName" label="{{ __('Customer Name') }}" placeholder="{{ __('e.g. John Doe') }}" />
                <flux:input wire:model="customerPhone" label="{{ __('Phone Number') }}" placeholder="{{ __('e.g. 255700000000') }}" description="{{ __('Required for mobile payment push') }}" />
            </div>

            {{-- Category Tabs + Items --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-smoke">{{ __('Select Items') }}</label>
                @if ($this->categories->isNotEmpty())
                    {{-- Category Tabs --}}
                    <div class="mb-3 flex flex-wrap gap-1.5">
                        @foreach ($this->categories as $cat)
                            <button
                                wire:click="$set('selectedCategoryId', {{ $cat->id }})"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $selectedCategoryId === $cat->id ? 'bg-terra text-white' : 'bg-ivory-light text-smoke-muted hover:bg-ivory-dark/30' }}"
                            >
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Products Grid --}}
                    @php $selectedCat = $this->categories->firstWhere('id', $selectedCategoryId) ?? $this->categories->first(); @endphp
                    @if ($selectedCat)
                        <div class="grid max-h-48 grid-cols-2 gap-2 overflow-y-auto sm:grid-cols-3">
                            @foreach ($selectedCat->products as $product)
                                <button
                                    wire:click="addToCart({{ $product->id }})"
                                    class="flex items-center gap-2 rounded-lg border border-ivory-dark/30 bg-ivory-light p-2 text-left transition hover:border-terra/50 hover:bg-terra-50"
                                >
                                    @if ($product->hasImage())
                                        <img src="{{ $product->imageUrl() }}" class="size-8 rounded object-cover" />
                                    @else
                                        <div class="flex size-8 items-center justify-center rounded bg-ivory-dark/20">
                                            <flux:icon.cube class="size-4 text-smoke-muted/40" />
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-medium text-smoke">{{ $product->name }}</p>
                                        <p class="text-xs font-semibold text-terra">{{ number_format($product->price, 0) }}</p>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <p class="text-sm text-smoke-muted">{{ __('No categories/items yet. Add them in the Products page first.') }}</p>
                @endif
            </div>

            {{-- Cart --}}
            @if (count($cartItems) > 0)
                <div>
                    <label class="mb-2 block text-sm font-medium text-smoke">{{ __('Order Items') }}</label>
                    <div class="divide-y divide-ivory-dark/30 rounded-xl border border-ivory-dark/30 bg-ivory-light">
                        @foreach ($cartItems as $index => $item)
                            <div class="flex items-center justify-between px-3 py-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-smoke">{{ $item['name'] }}</p>
                                    <p class="text-xs text-smoke-muted">{{ number_format($item['price'], 0) }} x {{ $item['quantity'] }} = <span class="font-semibold text-terra">{{ number_format($item['price'] * $item['quantity'], 0) }}</span></p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button wire:click="updateCartQty({{ $index }}, {{ $item['quantity'] - 1 }})" class="flex size-6 items-center justify-center rounded bg-ivory-dark/30 text-smoke hover:bg-ivory-dark/50">
                                        <flux:icon.minus class="size-3" />
                                    </button>
                                    <span class="w-6 text-center text-xs font-bold text-smoke">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateCartQty({{ $index }}, {{ $item['quantity'] + 1 }})" class="flex size-6 items-center justify-center rounded bg-ivory-dark/30 text-smoke hover:bg-ivory-dark/50">
                                        <flux:icon.plus class="size-3" />
                                    </button>
                                    <button wire:click="removeFromCart({{ $index }})" class="ml-1 flex size-6 items-center justify-center rounded text-red-400 hover:bg-red-50 hover:text-red-500">
                                        <flux:icon.x-mark class="size-3" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between px-3 py-2.5">
                            <p class="text-sm font-bold text-smoke">{{ __('Total') }}</p>
                            <p class="text-lg font-bold text-terra">{{ number_format($this->cartTotal, 0) }} TZS</p>
                        </div>
                    </div>
                </div>
            @endif

            <flux:textarea wire:model="orderNotes" label="{{ __('Notes') }}" placeholder="{{ __('Any special instructions...') }}" rows="2" />

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$toggle('showCreateModal')">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="createOrder" class="!bg-terra !text-white hover:!bg-terra-dark" :disabled="count($cartItems) === 0">
                    {{ __('Create Order') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Payment Modal --}}
    <flux:modal wire:model="showPaymentModal" class="w-full max-w-md">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Process Payment') }}</flux:heading>
                @if ($this->payingOrder)
                    <flux:text class="mt-1 text-smoke-muted">{{ $this->payingOrder->order_number }} · {{ $this->payingOrder->customer_name }}</flux:text>
                @endif
            </div>

            @if ($this->payingOrder)
                <div class="rounded-xl bg-ivory-light p-4 text-center">
                    <p class="text-xs text-smoke-muted">{{ __('Amount Due') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ number_format($this->payingOrder->total, 0) }} <span class="text-sm text-smoke-muted">TZS</span></p>
                </div>

                {{-- Payment Method --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-smoke">{{ __('Payment Method') }}</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            wire:click="$set('paymentMethod', 'cash')"
                            class="flex flex-col items-center gap-2 rounded-xl border-2 p-4 transition {{ $paymentMethod === 'cash' ? 'border-terra bg-terra-50' : 'border-ivory-dark/30 hover:border-ivory-dark/50' }}"
                        >
                            <flux:icon.banknotes class="size-6 {{ $paymentMethod === 'cash' ? 'text-terra' : 'text-smoke-muted' }}" />
                            <span class="text-sm font-medium {{ $paymentMethod === 'cash' ? 'text-terra' : 'text-smoke' }}">{{ __('Cash') }}</span>
                        </button>
                        <button
                            wire:click="$set('paymentMethod', 'mobile_money')"
                            class="flex flex-col items-center gap-2 rounded-xl border-2 p-4 transition {{ $paymentMethod === 'mobile_money' ? 'border-terra bg-terra-50' : 'border-ivory-dark/30 hover:border-ivory-dark/50' }} {{ ! $this->hasSelcom ? 'opacity-50' : '' }}"
                            {{ ! $this->hasSelcom ? 'disabled' : '' }}
                        >
                            <flux:icon.device-phone-mobile class="size-6 {{ $paymentMethod === 'mobile_money' ? 'text-terra' : 'text-smoke-muted' }}" />
                            <span class="text-sm font-medium {{ $paymentMethod === 'mobile_money' ? 'text-terra' : 'text-smoke' }}">{{ __('Mobile Money') }}</span>
                            @if (! $this->hasSelcom)
                                <span class="text-xs text-red-400">{{ __('Not configured') }}</span>
                            @endif
                        </button>
                    </div>
                </div>

                @if ($paymentMethod === 'mobile_money' && ! $this->payingOrder->customer_phone)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                        {{ __('Customer phone number is required for mobile payment. Edit the order to add it.') }}
                    </div>
                @endif

                @if ($paymentStatusMessage)
                    <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm text-indigo-700">
                        <div class="flex items-center gap-2">
                            @if ($pushingPayment)
                                <flux:icon.arrow-path class="size-4 animate-spin" />
                            @else
                                <flux:icon.information-circle class="size-4" />
                            @endif
                            {{ $paymentStatusMessage }}
                        </div>
                    </div>
                @endif

                <div class="flex flex-col gap-2">
                    @if ($paymentMethod === 'mobile_money' && $paymentStatusMessage && ! $pushingPayment)
                        <flux:button wire:click="checkPaymentStatus({{ $this->payingOrderId }})" class="w-full !bg-indigo-600 !text-white hover:!bg-indigo-700">
                            <flux:icon.arrow-path class="mr-1 size-4" />
                            {{ __('Check Payment Status') }}
                        </flux:button>
                    @endif

                    <div class="flex justify-end gap-3">
                        <flux:button variant="ghost" wire:click="$toggle('showPaymentModal')">{{ __('Cancel') }}</flux:button>
                        <flux:button wire:click="processPayment" class="!bg-terra !text-white hover:!bg-terra-dark" :disabled="$pushingPayment">
                            @if ($paymentMethod === 'cash')
                                {{ __('Record Cash Payment') }}
                            @else
                                {{ __('Send Payment Push') }}
                            @endif
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>
</div>

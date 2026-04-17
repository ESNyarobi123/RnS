<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-smoke">{{ __('Admin Dashboard') }}</h1>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('Complete platform overview and management') }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-lg bg-terra/10 px-3 py-1.5 text-sm font-semibold text-terra">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" />
            </svg>
            {{ __('Admin') }}
        </span>
    </div>

    {{-- Key Metrics Row --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-terra/10">
                    <flux:icon.building-storefront class="size-5 text-terra" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Businesses') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $this->platformStats['total_businesses'] }}</p>
                </div>
            </div>
            <div class="mt-3 flex gap-2">
                <flux:badge size="sm" color="amber">{{ $this->platformStats['restaurants'] }} {{ __('Restaurants') }}</flux:badge>
                <flux:badge size="sm" color="violet">{{ $this->platformStats['salons'] }} {{ __('Salons') }}</flux:badge>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-50">
                    <flux:icon.currency-dollar class="size-5 text-emerald-600" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Revenue (Month)') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ number_format($this->platformStats['revenue_this_month'], 0) }}</p>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-sm font-medium text-emerald-600">{{ __('Today') }}: {{ number_format($this->platformStats['revenue_today'], 0) }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-purple-50">
                    <flux:icon.clipboard-document-list class="size-5 text-purple-600" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Orders') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $this->platformStats['total_orders'] }}</p>
                </div>
            </div>
            <div class="mt-3 flex gap-2">
                <flux:badge size="sm" color="yellow">{{ $this->platformStats['pending_orders'] }} {{ __('Pending') }}</flux:badge>
                <flux:badge size="sm" color="green">{{ $this->platformStats['completed_orders'] }} {{ __('Done') }}</flux:badge>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-terra-50">
                    <flux:icon.users class="size-5 text-terra" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Users') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $this->platformStats['total_managers'] + $this->platformStats['total_workers'] }}</p>
                </div>
            </div>
            <div class="mt-3 flex gap-2">
                <flux:badge size="sm" color="blue">{{ $this->platformStats['total_managers'] }} {{ __('Managers') }}</flux:badge>
                <flux:badge size="sm" color="cyan">{{ $this->platformStats['total_workers'] }} {{ __('Workers') }}</flux:badge>
            </div>
        </div>
    </div>

    {{-- Secondary Metrics --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <a href="{{ route('admin.workers') }}" class="rounded-2xl border border-ivory-dark/40 bg-white p-4 transition hover:border-terra/30 hover:shadow-sm" wire:navigate>
            <p class="text-sm text-smoke-muted">{{ __('Active Links') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->platformStats['active_links'] }}</p>
        </a>
        <a href="{{ route('admin.orders') }}" class="rounded-2xl border border-ivory-dark/40 bg-white p-4 transition hover:border-terra/30 hover:shadow-sm" wire:navigate>
            <p class="text-sm text-smoke-muted">{{ __('Orders Today') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->platformStats['orders_today'] }}</p>
        </a>
        <a href="{{ route('admin.payments') }}?status=pending" class="rounded-2xl border border-terra-200 bg-terra-50 p-4 transition hover:border-terra/40 hover:shadow-sm" wire:navigate>
            <p class="text-sm font-medium text-terra-dark">{{ __('Pending Payments') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->platformStats['pending_payments'] }}</p>
        </a>
        <a href="{{ route('admin.payrolls') }}?status=pending" class="rounded-2xl border border-terra-200 bg-terra-50 p-4 transition hover:border-terra/40 hover:shadow-sm" wire:navigate>
            <p class="text-sm font-medium text-terra-dark">{{ __('Pending Payrolls') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->platformStats['pending_payrolls'] }}</p>
        </a>
        <a href="{{ route('admin.feedbacks') }}" class="rounded-2xl border border-ivory-dark/40 bg-white p-4 transition hover:border-terra/30 hover:shadow-sm" wire:navigate>
            <p class="text-sm text-smoke-muted">{{ __('Feedback') }}</p>
            <div class="mt-1 flex items-center gap-2">
                <p class="text-xl font-bold text-smoke">{{ $this->platformStats['total_feedback'] }}</p>
                @if ($this->platformStats['total_feedback'] > 0)
                    <span class="text-sm font-medium text-terra">★ {{ number_format($this->platformStats['avg_rating'], 1) }}</span>
                @endif
            </div>
        </a>
        @if ($this->platformStats['low_stock_items'] > 0 || $this->platformStats['out_of_stock_items'] > 0)
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                <p class="text-sm font-medium text-red-700">{{ __('Stock Alerts') }}</p>
                <div class="mt-1 flex items-center gap-2">
                    @if ($this->platformStats['out_of_stock_items'] > 0)
                        <flux:badge size="sm" color="red">{{ $this->platformStats['out_of_stock_items'] }} {{ __('Out') }}</flux:badge>
                    @endif
                    @if ($this->platformStats['low_stock_items'] > 0)
                        <flux:badge size="sm" color="yellow">{{ $this->platformStats['low_stock_items'] }} {{ __('Low') }}</flux:badge>
                    @endif
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm font-medium text-emerald-700">{{ __('Stock Status') }}</p>
                <p class="mt-1 text-xl font-bold text-emerald-600">{{ __('OK') }}</p>
            </div>
        @endif
    </div>

    {{-- Main Content Grid --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Top Businesses --}}
        <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
            <div class="flex items-center justify-between border-b border-ivory-dark/30 px-6 py-4">
                <h2 class="font-semibold text-smoke">{{ __('Top Businesses') }}</h2>
                <a href="{{ route('admin.businesses') }}" class="text-sm font-medium text-terra hover:text-terra-dark" wire:navigate>{{ __('View All') }}</a>
            </div>
            @if ($this->topBusinesses->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-sm text-smoke-muted">{{ __('No businesses yet.') }}</p>
                </div>
            @else
                <div class="divide-y divide-ivory-dark/30">
                    @foreach ($this->topBusinesses as $business)
                        <div class="flex items-center justify-between px-6 py-3">
                            <div class="flex items-center gap-3">
                                @if ($business->hasImage())
                                    <img src="{{ $business->imageUrl() }}" class="size-9 rounded-lg object-cover" />
                                @else
                                    <div class="flex size-9 items-center justify-center rounded-lg bg-ivory">
                                        <flux:icon.building-storefront class="size-4 text-smoke-muted" />
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-smoke">{{ $business->name }}</p>
                                    <div class="flex items-center gap-2">
                                        <flux:badge size="sm" :color="$business->type === \App\Enums\BusinessType::Restaurant ? 'amber' : 'violet'">
                                            {{ $business->type->label() }}
                                        </flux:badge>
                                        <span class="text-xs text-smoke-muted">{{ $business->active_worker_links_count }} {{ __('workers') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-smoke">{{ number_format($business->total_revenue ?? 0, 0) }}</p>
                                <span class="text-xs text-smoke-muted">{{ $business->orders_count }} {{ __('orders') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent Orders --}}
        <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
            <div class="flex items-center justify-between border-b border-ivory-dark/30 px-6 py-4">
                <h2 class="font-semibold text-smoke">{{ __('Recent Orders') }}</h2>
                <a href="{{ route('admin.orders') }}" class="text-sm font-medium text-terra hover:text-terra-dark" wire:navigate>{{ __('View All') }}</a>
            </div>
            @if ($this->recentOrders->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-sm text-smoke-muted">{{ __('No orders yet.') }}</p>
                </div>
            @else
                <div class="divide-y divide-ivory-dark/30">
                    @foreach ($this->recentOrders as $order)
                        <div class="flex items-center justify-between px-6 py-3">
                            <div>
                                <p class="font-mono text-sm font-medium text-smoke">{{ $order->order_number }}</p>
                                <span class="text-xs text-smoke-muted">{{ $order->business?->name }} &middot; {{ $order->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <p class="text-sm font-semibold text-smoke">{{ number_format($order->total, 0) }}</p>
                                <flux:badge size="sm" :color="$order->status->color()">{{ $order->status->label() }}</flux:badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent Payments --}}
        <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
            <div class="flex items-center justify-between border-b border-ivory-dark/30 px-6 py-4">
                <h2 class="font-semibold text-smoke">{{ __('Recent Payments') }}</h2>
                <a href="{{ route('admin.payments') }}" class="text-sm font-medium text-terra hover:text-terra-dark" wire:navigate>{{ __('View All') }}</a>
            </div>
            @if ($this->recentPayments->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-sm text-smoke-muted">{{ __('No payments yet.') }}</p>
                </div>
            @else
                <div class="divide-y divide-ivory-dark/30">
                    @foreach ($this->recentPayments as $payment)
                        <div class="flex items-center justify-between px-6 py-3">
                            <div>
                                <p class="text-sm font-medium text-smoke">{{ $payment->business?->name }}</p>
                                <span class="text-xs text-smoke-muted">{{ $payment->method->label() }} &middot; {{ $payment->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <p class="text-sm font-semibold text-smoke">{{ number_format($payment->amount, 0) }}</p>
                                <flux:badge size="sm" :color="$payment->status->color()">{{ $payment->status->label() }}</flux:badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent Workers --}}
        <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
            <div class="flex items-center justify-between border-b border-ivory-dark/30 px-6 py-4">
                <h2 class="font-semibold text-smoke">{{ __('Recent Workers') }}</h2>
                <a href="{{ route('admin.workers') }}" class="text-sm font-medium text-terra hover:text-terra-dark" wire:navigate>{{ __('View All') }}</a>
            </div>
            @if ($this->recentWorkers->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-sm text-smoke-muted">{{ __('No workers yet.') }}</p>
                </div>
            @else
                <div class="divide-y divide-ivory-dark/30">
                    @foreach ($this->recentWorkers as $worker)
                        <div class="flex items-center justify-between px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-9 items-center justify-center rounded-full bg-ivory text-sm font-bold text-smoke">
                                    {{ $worker->initials() }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-smoke">{{ $worker->name }}</p>
                                    <span class="font-mono text-xs text-smoke-muted">{{ $worker->global_number }}</span>
                                </div>
                            </div>
                            @php $activeLink = $worker->businessLinks->first(); @endphp
                            @if ($activeLink)
                                <flux:badge size="sm" :color="$activeLink->business?->type === \App\Enums\BusinessType::Restaurant ? 'amber' : 'violet'">
                                    {{ $activeLink->business?->name }}
                                </flux:badge>
                            @else
                                <flux:badge size="sm" color="gray">{{ __('Unlinked') }}</flux:badge>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div class="flex h-full w-full flex-1 flex-col gap-6">

    @if (! $this->hasBusiness)
        {{-- No Business State --}}
        <div class="rounded-2xl border-2 border-dashed border-ivory-dark/50 bg-ivory-light p-10 text-center sm:p-14">
            <div class="mx-auto flex size-20 items-center justify-center rounded-3xl bg-terra/10">
                <flux:icon.building-storefront class="size-10 text-terra" />
            </div>
            <h2 class="mt-6 text-xl font-bold text-smoke">{{ __('Create Your Business') }}</h2>
            <p class="mx-auto mt-3 max-w-sm text-sm text-smoke-muted">
                {{ __('Set up your restaurant or salon to start managing workers, products, orders, and more.') }}
            </p>
            <a href="{{ route('manager.business.create') }}" class="mt-8 inline-flex items-center gap-2 rounded-xl bg-terra px-8 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-terra-dark">
                {{ __('Get Started') }}
                <flux:icon.arrow-right class="size-4" />
            </a>

            <div class="mx-auto mt-12 grid max-w-lg gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-white p-4 ring-1 ring-ivory-dark/30">
                    <div class="mx-auto flex size-10 items-center justify-center rounded-xl bg-terra/10">
                        <flux:icon.users class="size-5 text-terra" />
                    </div>
                    <p class="mt-2 text-sm font-semibold text-smoke">{{ __('Manage Team') }}</p>
                    <p class="mt-1 text-xs text-smoke-muted">{{ __('Link workers via TIP numbers') }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 ring-1 ring-ivory-dark/30">
                    <div class="mx-auto flex size-10 items-center justify-center rounded-xl bg-emerald-50">
                        <flux:icon.clipboard-document-list class="size-5 text-emerald-600" />
                    </div>
                    <p class="mt-2 text-sm font-semibold text-smoke">{{ __('Track Orders') }}</p>
                    <p class="mt-1 text-xs text-smoke-muted">{{ __('Monitor sales in real-time') }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 ring-1 ring-ivory-dark/30">
                    <div class="mx-auto flex size-10 items-center justify-center rounded-xl bg-amber-50">
                        <flux:icon.currency-dollar class="size-5 text-amber-500" />
                    </div>
                    <p class="mt-2 text-sm font-semibold text-smoke">{{ __('Revenue') }}</p>
                    <p class="mt-1 text-xs text-smoke-muted">{{ __('Track earnings & payroll') }}</p>
                </div>
            </div>
        </div>
    @else
        {{-- Business Header Banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-smoke p-6 sm:p-8">
            <div class="absolute -right-10 -top-10 size-40 rounded-full bg-terra/10 blur-2xl"></div>
            <div class="absolute -bottom-8 -left-8 size-32 rounded-full bg-terra/5 blur-xl"></div>
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-5">
                @if ($this->business->hasImage())
                    <img src="{{ $this->business->imageUrl() }}" alt="{{ $this->business->name }}" class="size-18 rounded-2xl object-cover ring-2 ring-terra/30 sm:size-20" />
                @else
                    <div class="flex size-18 items-center justify-center rounded-2xl bg-terra/20 sm:size-20">
                        <flux:icon.building-storefront class="size-9 text-terra" />
                    </div>
                @endif
                <div class="flex-1">
                    <p class="text-sm text-ivory-dark/60">{{ __('Your Business') }}</p>
                    <h1 class="text-2xl font-bold text-ivory">{{ $this->business->name }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <flux:badge color="{{ $this->business->type->value === 'restaurant' ? 'amber' : 'violet' }}" size="sm">
                            {{ $this->business->type->label() }}
                        </flux:badge>
                        <flux:badge :color="$this->business->isActive() ? 'green' : 'red'" size="sm">
                            {{ $this->business->status->label() }}
                        </flux:badge>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2 text-right">
                    <div class="rounded-xl bg-ivory/10 px-4 py-2 text-center">
                        <p class="text-xs text-ivory-dark/50">{{ __('Total Revenue') }}</p>
                        <p class="text-xl font-bold text-ivory">{{ number_format($this->stats['total_revenue'], 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Today's Performance --}}
        <div>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-smoke-muted">{{ __("Today's Performance") }}</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-2xl border border-ivory-dark/40 bg-white p-4">
                    <div class="flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-smoke/5">
                            <flux:icon.clipboard-document-list class="size-4 text-smoke-muted" />
                        </div>
                        <p class="text-xs font-medium text-smoke-muted">{{ __("Today's Orders") }}</p>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-smoke">{{ $this->stats['orders_today'] }}</p>
                    <p class="text-xs text-smoke-muted">{{ $this->stats['orders_completed_today'] }} {{ __('completed') }}</p>
                </div>
                <div class="rounded-2xl border border-terra-200 bg-terra-50 p-4">
                    <div class="flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-terra/10">
                            <flux:icon.clock class="size-4 text-terra" />
                        </div>
                        <p class="text-xs font-medium text-terra-dark">{{ __('Pending') }}</p>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-smoke">{{ $this->stats['orders_pending'] }}</p>
                    <p class="text-xs text-terra-dark/70">{{ __('awaiting') }}</p>
                </div>
                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4">
                    <div class="flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-100">
                            <flux:icon.arrow-path class="size-4 text-indigo-600" />
                        </div>
                        <p class="text-xs font-medium text-indigo-700">{{ __('In Progress') }}</p>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-smoke">{{ $this->stats['orders_in_progress'] }}</p>
                    <p class="text-xs text-indigo-600/70">{{ __('active') }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100">
                            <flux:icon.currency-dollar class="size-4 text-emerald-600" />
                        </div>
                        <p class="text-xs font-medium text-emerald-700">{{ __('Revenue Today') }}</p>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($this->stats['revenue_today'], 0) }}</p>
                    <p class="text-xs text-emerald-600/70">{{ __('earned') }}</p>
                </div>
            </div>
        </div>

        {{-- Business Overview Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wider text-smoke-muted">{{ $this->businessType->workerTitlePlural() }}</p>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-terra/10">
                        <flux:icon.users class="size-4 text-terra" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-smoke">{{ $this->stats['workers'] }}</p>
                <p class="mt-1 text-xs text-smoke-muted">{{ __('active team members') }}</p>
            </div>
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wider text-smoke-muted">{{ $this->businessType->itemLabelPlural() }}</p>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-smoke/5">
                        <flux:icon.squares-2x2 class="size-4 text-smoke-muted" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-smoke">{{ $this->stats['products'] }}</p>
                <p class="mt-1 text-xs text-smoke-muted">{{ __('in :count categories', ['count' => $this->stats['categories']]) }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wider text-emerald-700">{{ __('Payroll Paid') }}</p>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100">
                        <flux:icon.banknotes class="size-4 text-emerald-600" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($this->stats['total_payroll_paid'], 0) }}</p>
                @if ($this->stats['pending_payroll'] > 0)
                    <p class="mt-1 text-xs font-medium text-terra">{{ number_format($this->stats['pending_payroll'], 0) }} {{ __('pending') }}</p>
                @else
                    <p class="mt-1 text-xs text-emerald-600/70">{{ __('all settled') }}</p>
                @endif
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wider text-amber-700">{{ __('Customer Rating') }}</p>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-amber-100">
                        <flux:icon.star class="size-4 text-amber-500" />
                    </div>
                </div>
                @if ($this->feedbackStats['total_reviews'] > 0)
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <p class="text-2xl font-bold text-smoke">{{ number_format($this->feedbackStats['avg_rating'], 1) }}</p>
                        <span class="text-lg text-amber-500">★</span>
                    </div>
                    <p class="mt-1 text-xs text-amber-700/70">{{ $this->feedbackStats['total_reviews'] }} {{ __('reviews') }}</p>
                @else
                    <p class="mt-2 text-2xl font-bold text-smoke">—</p>
                    <p class="mt-1 text-xs text-amber-700/70">{{ __('no reviews yet') }}</p>
                @endif
            </div>
        </div>

        {{-- Team + Recent Orders --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Team Section --}}
            <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
                <div class="flex items-center justify-between border-b border-ivory-dark/30 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <flux:icon.users class="size-4 text-terra" />
                        <h2 class="font-semibold text-smoke">{{ $this->businessType->workerTitlePlural() }}</h2>
                    </div>
                    <a href="{{ route('manager.workers.index') }}" class="rounded-lg bg-terra px-3 py-1.5 text-xs font-medium text-white transition hover:bg-terra-dark">
                        {{ __('Manage') }}
                    </a>
                </div>
                @if ($this->business->activeWorkerLinks->isEmpty())
                    <div class="p-8 text-center">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-smoke/5">
                            <flux:icon.user-plus class="size-6 text-smoke-muted" />
                        </div>
                        <p class="mt-3 text-sm text-smoke-muted">{{ __('No :workers linked yet.', ['workers' => strtolower($this->businessType->workerTitlePlural())]) }}</p>
                        <a href="{{ route('manager.workers.index') }}" class="mt-3 inline-flex text-sm font-medium text-terra hover:text-terra-dark">
                            {{ __('Link :title', ['title' => $this->businessType->workerTitle()]) }} →
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-ivory-dark/30">
                        @foreach ($this->business->activeWorkerLinks as $link)
                            <div class="flex items-center justify-between px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 items-center justify-center rounded-full bg-terra/10 text-sm font-bold text-terra">
                                        {{ $link->worker->initials() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-smoke">{{ $link->worker->name }}</p>
                                        <span class="font-mono text-xs text-smoke-muted">{{ $link->worker->global_number }}</span>
                                    </div>
                                </div>
                                <flux:badge size="sm" :color="$link->link_type->value === 'permanent' ? 'green' : 'amber'">
                                    {{ $link->link_type->label() }}
                                </flux:badge>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Products/Services Section --}}
            <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
                <div class="flex items-center justify-between border-b border-ivory-dark/30 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <flux:icon.squares-2x2 class="size-4 text-terra" />
                        <h2 class="font-semibold text-smoke">{{ $this->businessType->itemLabelPlural() }}</h2>
                    </div>
                    <a href="{{ route('manager.products.index') }}" class="rounded-lg bg-terra px-3 py-1.5 text-xs font-medium text-white transition hover:bg-terra-dark">
                        {{ __('Manage') }}
                    </a>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-6">
                        <div class="text-center">
                            <p class="text-3xl font-bold text-smoke">{{ $this->stats['products'] }}</p>
                            <p class="text-xs text-smoke-muted">{{ __('active') }} {{ strtolower($this->businessType->itemLabelPlural()) }}</p>
                        </div>
                        <div class="h-10 w-px bg-ivory-dark/30"></div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-smoke">{{ $this->stats['categories'] }}</p>
                            <p class="text-xs text-smoke-muted">{{ __('categories') }}</p>
                        </div>
                        <div class="h-10 w-px bg-ivory-dark/30"></div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-smoke">{{ $this->stats['total_orders'] }}</p>
                            <p class="text-xs text-smoke-muted">{{ __('total orders') }}</p>
                        </div>
                    </div>
                    <div class="mt-5 rounded-xl bg-ivory-light px-4 py-3">
                        <p class="text-sm text-smoke-muted">
                            {{ __(':completed of :total orders completed', ['completed' => $this->stats['total_completed'], 'total' => $this->stats['total_orders']]) }}
                            —
                            <span class="font-semibold text-smoke">{{ number_format($this->stats['total_revenue'], 0) }}</span> {{ __('total revenue') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders Table --}}
        @if ($this->recentOrders->isNotEmpty())
            <div>
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-smoke-muted">{{ __('Recent Orders') }}</h2>
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
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ivory-dark/30">
                                @foreach ($this->recentOrders as $order)
                                    <tr class="hover:bg-ivory-light">
                                        <td class="px-5 py-3">
                                            <span class="font-mono text-sm font-semibold text-smoke">{{ $order->order_number }}</span>
                                            @if ($order->customer_name)
                                                <p class="text-xs text-smoke-muted">{{ $order->customer_name }}</p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3">
                                            @if ($order->worker)
                                                <p class="text-sm text-smoke">{{ $order->worker->name }}</p>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

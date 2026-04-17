<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="relative overflow-hidden rounded-2xl bg-smoke p-6 sm:p-8">
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                @if ($this->worker->hasImage())
                    <img src="{{ $this->worker->imageUrl() }}" alt="{{ $this->worker->name }}" class="size-20 rounded-2xl object-cover ring-2 ring-terra/30" />
                @else
                    <div class="flex size-20 items-center justify-center rounded-2xl bg-terra/20 text-2xl font-bold text-terra">{{ $this->worker->initials() }}</div>
                @endif
                <div>
                    <p class="text-sm text-ivory-dark/60">{{ __('Welcome back,') }}</p>
                    <h1 class="text-2xl font-bold text-ivory">{{ $this->worker->name }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <flux:badge color="{{ $this->isLinked && $this->business->type->value === 'restaurant' ? 'amber' : ($this->isLinked ? 'violet' : 'blue') }}" size="sm">{{ $this->title }}</flux:badge>
                        <span class="rounded-lg bg-ivory/10 px-2.5 py-1 font-mono text-xs font-semibold text-ivory-dark/80">{{ $this->worker->global_number }}</span>
                    </div>
                </div>
            </div>

            @if ($this->isLinked)
                <div class="text-right">
                    <div class="flex items-center gap-2 rounded-xl bg-ivory/10 px-3 py-2">
                        <flux:icon.building-storefront class="size-4 text-terra" />
                        <span class="text-sm font-semibold text-ivory">{{ $this->business->name }}</span>
                    </div>
                    @if ($this->activeLink->qr_code)
                        <p class="mt-3 font-mono text-xs text-terra">{{ $this->activeLink->qr_code }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if ($this->isLinked)
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5"><p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Orders Today') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ $this->stats['orders_today'] }}</p></div>
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5"><p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Customers Served') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ $this->customersServed }}</p></div>
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5"><p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Tips Today') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($this->tipsSummary['today'], 0) }} TZS</p></div>
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5"><p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Pending Calls') }}</p><p class="mt-2 text-2xl font-bold text-amber-600">{{ $this->pendingCalls }}</p></div>
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5">
                <p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Rating') }}</p>
                @if ($this->feedbackStats['total_reviews'] > 0)
                    <p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($this->feedbackStats['avg_rating'], 1) }} ★</p>
                @else
                    <p class="mt-2 text-2xl font-bold text-smoke">—</p>
                @endif
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[0.9fr,1.1fr]">
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
                <h2 class="text-lg font-semibold text-smoke">{{ __('My QR & Code') }}</h2>
                <p class="mt-1 text-sm text-smoke-muted">{{ __('Customers can scan this QR or type the code to open your WhatsApp service menu directly.') }}</p>
                <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center">
                    @if ($this->activeLink->qr_image_path)
                        <img src="{{ asset('storage/'.$this->activeLink->qr_image_path) }}" class="h-40 w-40 rounded-2xl bg-white p-3 ring-1 ring-ivory-dark/30" />
                    @endif
                    <div>
                        <p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Worker Code') }}</p>
                        <p class="mt-2 font-mono text-lg font-semibold text-terra">{{ $this->activeLink->qr_code ?? __('Not generated yet') }}</p>
                        <p class="mt-2 text-xs text-smoke-muted">{{ __('Share this with customers after you are linked to the business.') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
                <h2 class="text-lg font-semibold text-smoke">{{ __('Recent Orders') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($this->recentOrders as $order)
                        <div class="rounded-2xl border border-ivory-dark/30 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-mono text-sm font-semibold text-smoke">{{ $order->order_number }}</p>
                                    <p class="mt-1 text-sm text-smoke-muted">{{ $order->customer_name ?? __('Walk-in customer') }}</p>
                                </div>
                                <div class="text-right">
                                    <flux:badge size="sm" :color="$order->status->color()">{{ $order->status->label() }}</flux:badge>
                                    <p class="mt-1 text-xs text-smoke-muted">{{ number_format($order->total, 0) }} TZS</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-ivory-dark/40 p-8 text-center text-sm text-smoke-muted">{{ __('No assigned orders yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <div class="rounded-2xl border-2 border-dashed border-ivory-dark/50 bg-ivory-light p-10 text-center sm:p-14">
            <div class="mx-auto flex size-20 items-center justify-center rounded-3xl bg-smoke/5">
                <flux:icon.link-slash class="size-10 text-smoke-muted" />
            </div>
            <h2 class="mt-6 text-xl font-bold text-smoke">{{ __('Not Linked to a Business') }}</h2>
            <p class="mx-auto mt-3 max-w-sm text-sm text-smoke-muted">{{ __('Share your TIP number with a manager first. Your QR, calls, tips, and customer history appear after the link is active.') }}</p>
            <div class="mx-auto mt-8 inline-flex items-center gap-3 rounded-2xl bg-white px-8 py-5 shadow-sm ring-1 ring-ivory-dark/40">
                <flux:icon.identification class="size-6 text-terra" />
                <span class="text-3xl font-bold tracking-widest text-smoke">{{ $this->worker->global_number }}</span>
            </div>
        </div>
    @endif
</div>

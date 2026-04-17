<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('My Tips') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('All tips sent to you from WhatsApp customers are listed here.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Total Tips') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($stats['total'], 0) }} TZS</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Tip Count') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ $stats['count'] }}</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Average Tip') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($stats['average'], 0) }} TZS</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('vs Previous') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($stats['percentage_change'], 1) }}%</p></div>
    </div>

    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <flux:select wire:model.live="period" class="w-full sm:w-40">
                <option value="today">{{ __('Today') }}</option>
                <option value="week">{{ __('This Week') }}</option>
                <option value="month">{{ __('This Month') }}</option>
                <option value="all">{{ __('All Time') }}</option>
            </flux:select>
            <flux:input wire:model.live="search" placeholder="{{ __('Search customer, phone, or source...') }}" class="w-full" />
        </div>
    </div>

    <div class="rounded-2xl border border-ivory-dark/40 bg-white">
        @if ($tips->count() > 0)
            <div class="divide-y divide-ivory-dark/30">
                @foreach ($tips as $tip)
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-smoke">{{ number_format($tip->amount, 0) }} TZS</p>
                                <p class="mt-1 text-sm text-smoke-muted">{{ $tip->customer_name ?? __('Anonymous customer') }}</p>
                                @if ($tip->customer_phone)
                                    <p class="text-xs text-smoke-muted">{{ $tip->customer_phone }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-wider text-smoke-muted">{{ $tip->source }}</p>
                                <p class="mt-1 text-xs text-smoke-muted">{{ $tip->created_at->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($tips instanceof \Illuminate\Contracts\Pagination\Paginator && $tips->hasPages())
                {{ $tips->links() }}
            @endif
        @else
            <div class="p-12 text-center">
                <flux:icon.currency-dollar class="mx-auto size-12 text-smoke-muted" />
                <h3 class="mt-4 text-lg font-semibold text-smoke">{{ __('No tips found') }}</h3>
                <p class="mt-2 text-sm text-smoke-muted">{{ __('Tips will appear here after customers reward your service from WhatsApp.') }}</p>
            </div>
        @endif
    </div>
</div>

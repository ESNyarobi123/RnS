<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('Tips Dashboard') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Track WhatsApp tips received by your team.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Total Tips') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($stats['total'], 0) }} TZS</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Tip Count') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ $stats['count'] }}</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Average Tip') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($stats['average'], 0) }} TZS</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('vs Previous') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($stats['percentage_change'], 1) }}%</p></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.2fr,0.8fr]">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:select wire:model.live="period" class="w-full sm:w-40">
                    <option value="today">{{ __('Today') }}</option>
                    <option value="week">{{ __('This Week') }}</option>
                    <option value="month">{{ __('This Month') }}</option>
                    <option value="all">{{ __('All Time') }}</option>
                </flux:select>
                <flux:select wire:model.live="workerFilter" class="w-full sm:w-52">
                    <option value="all">{{ __('All Workers') }}</option>
                    @foreach ($workers as $worker)
                        <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                    @endforeach
                </flux:select>
                <flux:input wire:model.live="search" placeholder="{{ __('Search customer, phone, or source...') }}" class="w-full" />
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($tips as $tip)
                    <div class="rounded-2xl border border-ivory-dark/30 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-smoke">{{ number_format($tip->amount, 0) }} TZS</p>
                                <p class="mt-1 text-sm text-smoke-muted">{{ $tip->worker?->name ?? __('Worker') }} · {{ $tip->customer_name ?? __('Anonymous customer') }}</p>
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
                @empty
                    <div class="rounded-2xl border border-dashed border-ivory-dark/40 p-8 text-center text-sm text-smoke-muted">
                        {{ __('No tips found for the current filter.') }}
                    </div>
                @endforelse
            </div>

            @if ($tips instanceof \Illuminate\Contracts\Pagination\Paginator && $tips->hasPages())
                <div class="mt-4">{{ $tips->links() }}</div>
            @endif
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <h2 class="text-lg font-semibold text-smoke">{{ __('Top Team Members') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($workerStats['top_workers'] as $index => $workerStat)
                    <div class="flex items-center gap-3 rounded-2xl bg-ivory-light p-3">
                        <div class="flex size-9 items-center justify-center rounded-full bg-terra/10 font-semibold text-terra">{{ $index + 1 }}</div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-smoke">{{ $workerStat->worker?->name ?? __('Unknown') }}</p>
                            <p class="text-xs text-smoke-muted">{{ $workerStat->tip_count }} {{ __('tips') }} · {{ number_format((float) $workerStat->total_amount, 0) }} TZS</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-smoke-muted">{{ __('No team tip data yet for this period.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

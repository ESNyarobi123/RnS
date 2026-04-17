<div class="flex h-full w-full flex-1 flex-col gap-6">

    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('My Reviews') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Customer feedback on your service at :business.', ['business' => $this->business?->name ?? '—']) }}</p>
    </div>

    @if (! $this->business)
        <div class="rounded-2xl border-2 border-dashed border-ivory-dark/50 bg-ivory-light p-10 text-center">
            <flux:icon.link-slash class="mx-auto size-10 text-smoke-muted" />
            <p class="mt-4 text-sm text-smoke-muted">{{ __('You need to be linked to a business to see reviews.') }}</p>
        </div>
    @else
        {{-- Rating Summary --}}
        <div class="grid gap-4 sm:grid-cols-3">
            {{-- Average --}}
            <div class="flex flex-col items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center">
                @if ($this->summary['total'] > 0)
                    <p class="text-5xl font-bold text-smoke">{{ $this->summary['avg'] }}</p>
                    <div class="mt-2 flex items-center gap-0.5">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= round($this->summary['avg']))
                                <span class="text-lg text-amber-500">★</span>
                            @else
                                <span class="text-lg text-amber-200">★</span>
                            @endif
                        @endfor
                    </div>
                    <p class="mt-2 text-sm text-amber-700/70">{{ $this->summary['total'] }} {{ __('reviews') }}</p>
                @else
                    <p class="text-5xl font-bold text-smoke-muted">—</p>
                    <p class="mt-2 text-sm text-amber-700/70">{{ __('No reviews yet') }}</p>
                @endif
            </div>

            {{-- Breakdown --}}
            <div class="col-span-2 rounded-2xl border border-ivory-dark/40 bg-white p-6">
                <h3 class="mb-4 text-sm font-semibold text-smoke">{{ __('Rating Breakdown') }}</h3>
                <div class="space-y-2.5">
                    @foreach ($this->summary['breakdown'] ?? [] as $star => $data)
                        <button
                            wire:click="$set('rating', '{{ $rating === (string) $star ? '' : $star }}')"
                            class="flex w-full items-center gap-3 rounded-lg px-2 py-1 transition hover:bg-ivory-light {{ $rating === (string) $star ? 'bg-amber-50' : '' }}"
                        >
                            <span class="w-6 text-right text-sm font-semibold text-smoke">{{ $star }}</span>
                            <span class="text-amber-500">★</span>
                            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-ivory-light">
                                <div class="h-full rounded-full bg-amber-400 transition-all" style="width: {{ $data['pct'] }}%"></div>
                            </div>
                            <span class="w-10 text-right text-xs font-medium text-smoke-muted">{{ $data['count'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Filter indicator --}}
        @if ($rating)
            <div class="flex items-center gap-2">
                <flux:badge color="amber" size="sm">
                    {{ __('Showing :star star reviews', ['star' => $rating]) }}
                </flux:badge>
                <button wire:click="$set('rating', '')" class="text-xs text-terra hover:text-terra-dark">{{ __('Clear filter') }}</button>
            </div>
        @endif

        {{-- Reviews List --}}
        <div class="space-y-3">
            @forelse ($this->reviews as $review)
                <div class="rounded-2xl border border-ivory-dark/40 bg-white p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-sm font-bold text-amber-600">
                                {{ substr($review->customer_name ?? '?', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-smoke">{{ $review->customer_name ?? __('Customer') }}</p>
                                @if ($review->order)
                                    <p class="font-mono text-xs text-smoke-muted">{{ $review->order->order_number }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $review->rating)
                                        <span class="text-amber-500">★</span>
                                    @else
                                        <span class="text-amber-200">★</span>
                                    @endif
                                @endfor
                            </div>
                            <p class="mt-0.5 text-xs text-smoke-muted">{{ $review->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @if ($review->comment)
                        <p class="mt-3 rounded-xl bg-ivory-light px-4 py-3 text-sm text-smoke">{{ $review->comment }}</p>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border-2 border-dashed border-ivory-dark/30 p-10 text-center">
                    <flux:icon.chat-bubble-bottom-center-text class="mx-auto size-10 text-smoke-muted/30" />
                    <p class="mt-3 text-sm text-smoke-muted">{{ __('No reviews yet. Keep up the great work!') }}</p>
                </div>
            @endforelse

            @if ($this->reviews->hasPages())
                <div class="pt-2">
                    {{ $this->reviews->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

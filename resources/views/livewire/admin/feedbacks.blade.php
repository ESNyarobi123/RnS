<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('Feedback & Reviews') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Customer feedback and ratings across all businesses') }}</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-4">
            <p class="text-sm text-smoke-muted">{{ __('Total') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->feedbackSummary['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-terra-200 bg-terra-50 p-4">
            <p class="text-sm font-medium text-terra-dark">{{ __('Avg Rating') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->feedbackSummary['total'] > 0 ? number_format($this->feedbackSummary['avg_rating'], 1) : '-' }}</p>
        </div>
        @foreach ([5 => 'green', 4 => 'blue', 3 => 'yellow', 2 => 'orange', 1 => 'red'] as $star => $color)
            <div class="rounded-2xl border border-{{ $color }}-200 bg-{{ $color }}-50 p-4">
                <p class="text-sm font-medium text-{{ $color }}-700">{{ $star }}★</p>
                <p class="mt-1 text-xl font-bold text-smoke">{{ $this->feedbackSummary[['', 'one', 'two', 'three', 'four', 'five'][$star] . '_star'] }}</p>
            </div>
        @endforeach
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-4">
            <p class="text-sm text-smoke-muted">{{ __('NPS') }}</p>
            @php
                $total = $this->feedbackSummary['total'];
                $promoters = $this->feedbackSummary['five_star'] + $this->feedbackSummary['four_star'];
                $detractors = $this->feedbackSummary['one_star'] + $this->feedbackSummary['two_star'];
                $nps = $total > 0 ? round((($promoters - $detractors) / $total) * 100) : 0;
            @endphp
            <p class="mt-1 text-xl font-bold {{ $nps >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $nps }}%</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search customer, comment, business...')" />
        </div>
        <flux:select wire:model.live="rating" class="w-36">
            <flux:select.option value="">{{ __('All Ratings') }}</flux:select.option>
            @for ($i = 5; $i >= 1; $i--)
                <flux:select.option :value="$i">{{ $i }} {{ __('Star') }}{{ $i > 1 ? 's' : '' }}</flux:select.option>
            @endfor
        </flux:select>
        <flux:select wire:model.live="businessType" class="w-40">
            <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
            <flux:select.option value="restaurant">{{ __('Restaurant') }}</flux:select.option>
            <flux:select.option value="salon">{{ __('Salon') }}</flux:select.option>
        </flux:select>
        @if ($search || $rating || $businessType)
            <flux:button variant="ghost" size="sm" wire:click="resetFilters" icon="x-mark">{{ __('Clear') }}</flux:button>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-ivory-dark/30 bg-ivory-light text-xs uppercase text-smoke-muted">
                    <tr>
                        <th class="px-6 py-3">{{ __('Customer') }}</th>
                        <th class="px-6 py-3">{{ __('Business') }}</th>
                        <th class="px-6 py-3">{{ __('Worker') }}</th>
                        <th class="px-6 py-3 text-center">{{ __('Rating') }}</th>
                        <th class="px-6 py-3">{{ __('Comment') }}</th>
                        <th class="px-6 py-3">{{ __('Date') }}</th>
                        <th class="px-6 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ivory-dark/30">
                    @forelse ($this->feedbacks as $feedback)
                        <tr class="hover:bg-ivory-light">
                            <td class="px-6 py-4">
                                <flux:text class="font-medium">{{ $feedback->customer_name }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text>{{ $feedback->business?->name }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                @if ($feedback->worker)
                                    <flux:text>{{ $feedback->worker->name }}</flux:text>
                                    <flux:text size="sm" class="font-mono text-smoke-muted">{{ $feedback->worker->global_number }}</flux:text>
                                @else
                                    <flux:text class="text-smoke-muted">-</flux:text>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $ratingColor = match ($feedback->rating) {
                                        5 => 'green', 4 => 'blue', 3 => 'yellow', 2 => 'orange', 1 => 'red', default => 'zinc',
                                    };
                                @endphp
                                <flux:badge size="sm" :color="$ratingColor">{{ $feedback->rating }}★</flux:badge>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text size="sm" class="max-w-[250px] truncate">{{ $feedback->comment ?? '-' }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text size="sm">{{ $feedback->created_at->format('M d, Y') }}</flux:text>
                                <flux:text size="sm" class="text-smoke-muted">{{ $feedback->created_at->diffForHumans() }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:button variant="ghost" size="sm" wire:click="deleteFeedback({{ $feedback->id }})" wire:confirm="{{ __('Delete this feedback?') }}" icon="trash" class="text-red-500 hover:text-red-700" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <flux:icon.chat-bubble-bottom-center-text class="mx-auto size-10 text-smoke-muted/40" />
                                <p class="mt-2 text-sm text-smoke-muted">{{ __('No feedback found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->feedbacks->links() }}
</div>

<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('Feedback Management') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Monitor customer feedback and service ratings') }}</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-terra/10">
                    <flux:icon.chat-bubble-bottom-center-text class="size-5 text-terra" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Total Feedback') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100">
                    <flux:icon.star class="size-5 text-emerald-600" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Average Rating') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $stats['average_rating'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full {{ $stats['nps_score'] >= 0 ? 'bg-emerald-100' : 'bg-red-100' }}">
                    <flux:icon.presentation-chart-line class="size-5 {{ $stats['nps_score'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('NPS Score') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $stats['nps_score'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-blue-100">
                    <flux:icon.users class="size-5 text-blue-600" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Promoters') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $stats['promoters'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Rating Breakdown --}}
    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <h3 class="mb-4 text-lg font-semibold text-smoke">{{ __('Rating Breakdown') }}</h3>
        <div class="grid grid-cols-5 gap-4">
            @for ($i = 5; $i >= 1; $i--)
                <div class="text-center">
                    <div class="flex justify-center gap-1 mb-2">
                        @for ($j = 1; $j <= 5; $j++)
                            <flux:icon.star 
                                class="size-5 {{ $j <= $i ? 'text-yellow-400' : 'text-gray-300' }}" 
                                {{ $j <= $i ? 'solid' : 'outline' }} 
                            />
                        @endfor
                    </div>
                    <p class="text-2xl font-bold text-smoke">{{ $stats['rating_breakdown'][$i] }}</p>
                    <p class="text-sm text-smoke-muted">{{ $i }} {{ __('Stars') }}</p>
                </div>
            @endfor
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:select
                    wire:model.live="dateRange"
                    class="w-full sm:w-40"
                >
                    <option value="7">{{ __('Last 7 days') }}</option>
                    <option value="30">{{ __('Last 30 days') }}</option>
                    <option value="90">{{ __('Last 90 days') }}</option>
                    <option value="all">{{ __('All time') }}</option>
                </flux:select>
                
                <flux:select
                    wire:model.live="rating"
                    placeholder="{{ __('All Ratings') }}"
                    class="w-full sm:w-40"
                >
                    <option value="all">{{ __('All Ratings') }}</option>
                    <option value="5">{{ __('5 Stars') }}</option>
                    <option value="4">{{ __('4 Stars') }}</option>
                    <option value="3">{{ __('3 Stars') }}</option>
                    <option value="2">{{ __('2 Stars') }}</option>
                    <option value="1">{{ __('1 Star') }}</option>
                </flux:select>

                <flux:select
                    wire:model.live="workerFilter"
                    placeholder="{{ __('All Workers') }}"
                    class="w-full sm:w-48"
                >
                    <option value="all">{{ __('All Workers') }}</option>
                    @foreach ($workers as $worker)
                        <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                    @endforeach
                </flux:select>
                
                <flux:input
                    wire:model.live="search"
                    placeholder="{{ __('Search feedback...') }}"
                    class="w-full sm:w-64"
                />
            </div>
        </div>
    </div>

    {{-- Feedback List --}}
    <div class="rounded-2xl border border-ivory-dark/40 bg-white">
        @if ($feedbacks->count() > 0)
            <div class="divide-y divide-ivory-dark/30">
                @foreach ($feedbacks as $feedback)
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4 flex-1">
                                <div class="flex size-12 items-center justify-center rounded-full bg-terra/10">
                                    <flux:icon.star class="size-6 text-terra" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="flex gap-1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <flux:icon.star 
                                                    class="size-4 {{ $i <= $feedback->rating ? 'text-yellow-400' : 'text-gray-300' }}" 
                                                    {{ $i <= $feedback->rating ? 'solid' : 'outline' }} 
                                                />
                                            @endfor
                                        </div>
                                        <span class="text-sm font-semibold text-smoke">{{ $feedback->rating }}/5</span>
                                    </div>
                                    
                                    <div class="space-y-1">
                                        @if ($feedback->customer_name)
                                            <p class="text-sm text-smoke-muted">{{ __('From') }}: {{ $feedback->customer_name }}</p>
                                        @endif
                                        
                                        @if ($feedback->worker)
                                            <p class="text-sm text-smoke-muted">{{ __('Worker') }}: {{ $feedback->worker->name }}</p>
                                        @endif
                                        @if ($feedback->order)
                                            <p class="text-xs text-smoke-muted">{{ $feedback->order->order_number }}</p>
                                        @endif
                                        
                                        @if ($feedback->comment)
                                            <p class="text-sm text-smoke">{{ $feedback->comment }}</p>
                                        @endif
                                    </div>
                                    
                                    <p class="text-xs text-smoke-muted mt-2">
                                        {{ $feedback->created_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-2">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm">
                                        <flux:icon.ellipsis-vertical class="size-4" />
                                    </flux:button>

                                    <flux:menu>
                                        <flux:menu.item 
                                            wire:click="delete({{ $feedback->id }})" 
                                            icon="trash"
                                            variant="danger"
                                            wire:confirm="{{ __('Delete this feedback? This action cannot be undone.') }}"
                                        >
                                            {{ __('Delete') }}
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $feedbacks->links() }}
        @else
            <div class="p-12 text-center">
                <flux:icon.chat-bubble-bottom-center-text class="mx-auto size-12 text-smoke-muted" />
                <h3 class="mt-4 text-lg font-semibold text-smoke">{{ __('No feedback found') }}</h3>
                <p class="mt-2 text-sm text-smoke-muted">
                    {{ __('Customer feedback will appear here once customers start rating your services.') }}
                </p>
            </div>
        @endif
    </div>
</div>

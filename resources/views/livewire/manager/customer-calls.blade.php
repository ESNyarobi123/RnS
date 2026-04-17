<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('Customer Calls') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Watch all WhatsApp service calls coming from tables, seats, and customer support flows.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Total Calls') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ $stats['total_calls'] }}</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Pending') }}</p><p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['pending_calls'] }}</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Responded') }}</p><p class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['completed_calls'] }}</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('This Week') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ $stats['calls_this_week'] }}</p></div>
    </div>

    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <flux:select wire:model.live="dateRange" class="w-full sm:w-40">
                <option value="today">{{ __('Today') }}</option>
                <option value="week">{{ __('This Week') }}</option>
                <option value="month">{{ __('This Month') }}</option>
                <option value="all">{{ __('All Time') }}</option>
            </flux:select>
            <flux:select wire:model.live="status" class="w-full sm:w-40">
                <option value="all">{{ __('All Status') }}</option>
                <option value="pending">{{ __('Pending') }}</option>
                <option value="responded">{{ __('Responded') }}</option>
            </flux:select>
            <flux:input wire:model.live="search" placeholder="{{ __('Search customer, phone, table, or message...') }}" class="w-full" />
        </div>
    </div>

    <div class="rounded-2xl border border-ivory-dark/40 bg-white">
        @if ($customerCalls->count() > 0)
            <div class="divide-y divide-ivory-dark/30">
                @foreach ($customerCalls as $call)
                    <div class="p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-smoke">{{ $call->customer_name ?? __('Anonymous Customer') }}</p>
                                    <flux:badge size="sm" :color="$call->status === 'responded' ? 'green' : 'amber'">{{ ucfirst($call->status) }}</flux:badge>
                                </div>
                                <p class="mt-1 text-sm text-smoke-muted">{{ $call->table?->display_name ?? $business->tableLabel() }}</p>
                                @if ($call->customer_phone)
                                    <p class="text-xs text-smoke-muted">{{ $call->customer_phone }}</p>
                                @endif
                                @if ($call->notes)
                                    <p class="mt-2 text-sm text-smoke">{{ $call->notes }}</p>
                                @endif
                                <p class="mt-2 text-xs text-smoke-muted">{{ $call->created_at->format('M j, Y g:i A') }}</p>
                            </div>

                            @if ($call->status === 'pending')
                                <flux:button wire:click="markAsCompleted({{ $call->id }})" class="!bg-terra !text-white hover:!bg-terra-dark">
                                    {{ __('Mark Responded') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $customerCalls->links() }}
        @else
            <div class="p-12 text-center">
                <flux:icon.phone class="mx-auto size-12 text-smoke-muted" />
                <h3 class="mt-4 text-lg font-semibold text-smoke">{{ __('No customer calls found') }}</h3>
                <p class="mt-2 text-sm text-smoke-muted">{{ __('New WhatsApp calls will appear here when customers ask for help or call the team.') }}</p>
            </div>
        @endif
    </div>
</div>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('Customer Calls') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('See the latest WhatsApp customer calls happening in your linked business.') }}</p>
    </div>

    @if (! $business)
        <div class="rounded-2xl border-2 border-dashed border-ivory-dark/40 p-10 text-center text-sm text-smoke-muted">
            {{ __('You need an active business link before customer calls can appear here.') }}
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Total Calls') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ $stats['total'] }}</p></div>
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Pending') }}</p><p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p></div>
            <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Responded') }}</p><p class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['responded'] }}</p></div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:select wire:model.live="status" class="w-full sm:w-40">
                    <option value="all">{{ __('All Status') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="responded">{{ __('Responded') }}</option>
                </flux:select>
                <flux:input wire:model.live="search" placeholder="{{ __('Search customer, phone, or table...') }}" class="w-full" />
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white">
            @if ($calls->count() > 0)
                <div class="divide-y divide-ivory-dark/30">
                    @foreach ($calls as $call)
                        <div class="p-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-smoke">{{ $call->customer_name ?? __('Anonymous Customer') }}</p>
                                        <flux:badge size="sm" :color="$call->status === 'responded' ? 'green' : 'amber'">{{ ucfirst($call->status) }}</flux:badge>
                                    </div>
                                    <p class="mt-1 text-sm text-smoke-muted">{{ $call->table?->display_name ?? __('No table / seat') }}</p>
                                    @if ($call->customer_phone)
                                        <p class="text-xs text-smoke-muted">{{ $call->customer_phone }}</p>
                                    @endif
                                    @if ($call->notes)
                                        <p class="mt-2 text-sm text-smoke">{{ $call->notes }}</p>
                                    @endif
                                    <p class="mt-2 text-xs text-smoke-muted">{{ $call->created_at->format('M j, Y g:i A') }}</p>
                                </div>

                                @if ($call->status === 'pending')
                                    <flux:button wire:click="markAsResponded({{ $call->id }})" class="!bg-terra !text-white hover:!bg-terra-dark">
                                        {{ __('Mark Responded') }}
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($calls instanceof \Illuminate\Contracts\Pagination\Paginator && $calls->hasPages())
                    {{ $calls->links() }}
                @endif
            @else
                <div class="p-12 text-center">
                    <flux:icon.phone class="mx-auto size-12 text-smoke-muted" />
                    <h3 class="mt-4 text-lg font-semibold text-smoke">{{ __('No customer calls yet') }}</h3>
                    <p class="mt-2 text-sm text-smoke-muted">{{ __('WhatsApp customer service requests will appear here.') }}</p>
                </div>
            @endif
        </div>
    @endif
</div>

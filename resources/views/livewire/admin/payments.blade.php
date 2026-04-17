<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('All Payments') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Track all payment transactions across the platform') }}</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-medium text-emerald-700">{{ __('Total Revenue') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ number_format($this->paymentSummary['total_amount'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-4">
            <p class="text-sm text-smoke-muted">{{ __('Today') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ number_format($this->paymentSummary['today_amount'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-terra-200 bg-terra-50 p-4">
            <p class="text-sm font-medium text-terra-dark">{{ __('Pending') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ number_format($this->paymentSummary['pending_amount'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-medium text-emerald-700">{{ __('Completed') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->paymentSummary['completed_count'] }}</p>
        </div>
        <div class="rounded-2xl border border-terra-200 bg-terra-50 p-4">
            <p class="text-sm font-medium text-terra-dark">{{ __('Pending #') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->paymentSummary['pending_count'] }}</p>
        </div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="text-sm font-medium text-red-700">{{ __('Failed') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->paymentSummary['failed_count'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search reference or business...')" />
        </div>
        <flux:select wire:model.live="status" class="w-40">
            <flux:select.option value="">{{ __('All Status') }}</flux:select.option>
            @foreach ($this->statusOptions as $opt)
                <flux:select.option :value="$opt['value']">{{ $opt['label'] }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="method" class="w-44">
            <flux:select.option value="">{{ __('All Methods') }}</flux:select.option>
            @foreach ($this->methodOptions as $opt)
                <flux:select.option :value="$opt['value']">{{ $opt['label'] }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live="dateFrom" type="date" :label="__('From')" class="w-40" />
        <flux:input wire:model.live="dateTo" type="date" :label="__('To')" class="w-40" />
        @if ($search || $status || $method || $dateFrom || $dateTo)
            <flux:button variant="ghost" size="sm" wire:click="resetFilters" icon="x-mark">{{ __('Clear') }}</flux:button>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-ivory-dark/30 bg-ivory-light text-xs uppercase text-smoke-muted">
                    <tr>
                        <th class="px-6 py-3">{{ __('Reference') }}</th>
                        <th class="px-6 py-3">{{ __('Business') }}</th>
                        <th class="px-6 py-3">{{ __('Order') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Amount') }}</th>
                        <th class="px-6 py-3">{{ __('Method') }}</th>
                        <th class="px-6 py-3">{{ __('Status') }}</th>
                        <th class="px-6 py-3">{{ __('Paid At') }}</th>
                        <th class="px-6 py-3">{{ __('Created') }}</th>
                        <th class="px-6 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ivory-dark/30">
                    @forelse ($this->payments as $payment)
                        <tr class="hover:bg-ivory-light">
                            <td class="px-6 py-4">
                                <flux:text class="font-mono text-sm">{{ Str::limit($payment->reference, 16) }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text class="font-medium">{{ $payment->business?->name }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                @if ($payment->order)
                                    <flux:text class="font-mono text-sm">{{ $payment->order->order_number }}</flux:text>
                                @else
                                    <flux:text class="text-smoke-muted">-</flux:text>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-semibold">{{ number_format($payment->amount, 0) }}</td>
                            <td class="px-6 py-4">
                                <flux:badge size="sm" color="zinc">{{ $payment->method->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-4">
                                <flux:badge size="sm" :color="$payment->status->color()">{{ $payment->status->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-4">
                                @if ($payment->paid_at)
                                    <flux:text size="sm">{{ $payment->paid_at->format('M d, Y H:i') }}</flux:text>
                                @else
                                    <flux:text class="text-smoke-muted">-</flux:text>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <flux:text size="sm">{{ $payment->created_at->format('M d, Y') }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                @if ($payment->status !== \App\Enums\PaymentStatus::Completed)
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            @if ($payment->status !== \App\Enums\PaymentStatus::Completed)
                                                <flux:menu.item wire:click="markPaymentCompleted({{ $payment->id }})" icon="check-circle">{{ __('Mark Completed') }}</flux:menu.item>
                                            @endif
                                            @if ($payment->status !== \App\Enums\PaymentStatus::Failed)
                                                <flux:menu.item wire:click="updatePaymentStatus({{ $payment->id }}, 'failed')" icon="x-circle" variant="danger">{{ __('Mark Failed') }}</flux:menu.item>
                                            @endif
                                            @if ($payment->status !== \App\Enums\PaymentStatus::Refunded)
                                                <flux:menu.item wire:click="updatePaymentStatus({{ $payment->id }}, 'refunded')" icon="arrow-uturn-left">{{ __('Refund') }}</flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <flux:icon.currency-dollar class="mx-auto size-10 text-smoke-muted/40" />
                                <p class="mt-2 text-sm text-smoke-muted">{{ __('No payments found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->payments->links() }}
</div>

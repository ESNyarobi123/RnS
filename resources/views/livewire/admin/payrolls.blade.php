<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('All Payrolls') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('View salary payments to workers across all businesses') }}</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-medium text-emerald-700">{{ __('Total Paid') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ number_format($this->payrollSummary['total_paid'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-terra-200 bg-terra-50 p-4">
            <p class="text-sm font-medium text-terra-dark">{{ __('Pending Amount') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ number_format($this->payrollSummary['total_pending'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-medium text-emerald-700">{{ __('Paid') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->payrollSummary['paid_count'] }}</p>
        </div>
        <div class="rounded-2xl border border-terra-200 bg-terra-50 p-4">
            <p class="text-sm font-medium text-terra-dark">{{ __('Pending') }}</p>
            <p class="mt-1 text-xl font-bold text-smoke">{{ $this->payrollSummary['pending_count'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search worker, TIP number, or business...')" />
        </div>
        <flux:select wire:model.live="status" class="w-40">
            <flux:select.option value="">{{ __('All Status') }}</flux:select.option>
            @foreach ($this->statusOptions as $opt)
                <flux:select.option :value="$opt['value']">{{ $opt['label'] }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live="dateFrom" type="date" :label="__('Period From')" class="w-40" />
        <flux:input wire:model.live="dateTo" type="date" :label="__('Period To')" class="w-40" />
        @if ($search || $status || $dateFrom || $dateTo)
            <flux:button variant="ghost" size="sm" wire:click="resetFilters" icon="x-mark">{{ __('Clear') }}</flux:button>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-ivory-dark/30 bg-ivory-light text-xs uppercase text-smoke-muted">
                    <tr>
                        <th class="px-6 py-3">{{ __('Worker') }}</th>
                        <th class="px-6 py-3">{{ __('TIP Number') }}</th>
                        <th class="px-6 py-3">{{ __('Business') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Amount') }}</th>
                        <th class="px-6 py-3">{{ __('Period') }}</th>
                        <th class="px-6 py-3">{{ __('Status') }}</th>
                        <th class="px-6 py-3">{{ __('Paid At') }}</th>
                        <th class="px-6 py-3">{{ __('Notes') }}</th>
                        <th class="px-6 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ivory-dark/30">
                    @forelse ($this->payrolls as $payroll)
                        <tr class="hover:bg-ivory-light">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 items-center justify-center rounded-full bg-terra/10 text-sm font-bold text-terra">
                                        {{ $payroll->worker?->initials() }}
                                    </div>
                                    <flux:text class="font-medium">{{ $payroll->worker?->name }}</flux:text>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text class="font-mono text-sm">{{ $payroll->worker?->global_number }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text>{{ $payroll->business?->name }}</flux:text>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold">{{ number_format($payroll->amount, 0) }}</td>
                            <td class="px-6 py-4">
                                <flux:text size="sm">{{ $payroll->period_start->format('M d') }} - {{ $payroll->period_end->format('M d, Y') }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:badge size="sm" :color="$payroll->status->color()">{{ $payroll->status->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-4">
                                @if ($payroll->paid_at)
                                    <flux:text size="sm">{{ $payroll->paid_at->format('M d, Y') }}</flux:text>
                                @else
                                    <flux:text class="text-smoke-muted">-</flux:text>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <flux:text size="sm" class="max-w-[120px] truncate text-smoke-muted">{{ $payroll->notes ?? '-' }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                @if ($payroll->status === \App\Enums\PayrollStatus::Pending)
                                    <flux:button variant="primary" size="sm" wire:click="markPayrollPaid({{ $payroll->id }})" wire:confirm="{{ __('Mark this payroll as paid?') }}" icon="check-circle">
                                        {{ __('Pay') }}
                                    </flux:button>
                                @else
                                    <flux:badge size="sm" color="green">{{ __('Paid') }}</flux:badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <flux:icon.banknotes class="mx-auto size-10 text-smoke-muted/40" />
                                <p class="mt-2 text-sm text-smoke-muted">{{ __('No payrolls found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->payrolls->links() }}
</div>

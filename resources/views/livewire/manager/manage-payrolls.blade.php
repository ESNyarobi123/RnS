<div class="flex h-full w-full flex-1 flex-col gap-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-smoke">{{ __('Payrolls') }}</h1>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('Manage worker payments and payroll records') }}</p>
        </div>
        <flux:button size="sm" wire:click="openCreatePayroll" class="!bg-terra !text-white hover:!bg-terra-dark">
            {{ __('+ Payroll') }}
        </flux:button>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100">
                    <flux:icon.check-circle class="size-4 text-emerald-600" />
                </div>
                <p class="text-xs font-medium text-emerald-700">{{ __('Total Paid') }}</p>
            </div>
            <p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($this->payrollSummary['total_paid'], 0) }}</p>
            <p class="text-xs text-emerald-600/70">{{ $this->payrollSummary['paid_count'] }} {{ __('records') }}</p>
        </div>
        <div class="rounded-2xl border border-terra-200 bg-terra-50 p-4">
            <div class="flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-terra/10">
                    <flux:icon.clock class="size-4 text-terra" />
                </div>
                <p class="text-xs font-medium text-terra-dark">{{ __('Pending') }}</p>
            </div>
            <p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($this->payrollSummary['total_pending'], 0) }}</p>
            <p class="text-xs text-terra-dark/70">{{ $this->payrollSummary['pending_count'] }} {{ __('records') }}</p>
        </div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-4">
            <div class="flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-smoke/5">
                    <flux:icon.banknotes class="size-4 text-smoke-muted" />
                </div>
                <p class="text-xs font-medium text-smoke-muted">{{ __('Total Spent') }}</p>
            </div>
            <p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($this->payrollSummary['total_paid'] + $this->payrollSummary['total_pending'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-4">
            <div class="flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-smoke/5">
                    <flux:icon.users class="size-4 text-smoke-muted" />
                </div>
                <p class="text-xs font-medium text-smoke-muted">{{ __('Active Workers') }}</p>
            </div>
            <p class="mt-2 text-2xl font-bold text-smoke">{{ $this->activeWorkers->count() }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search workers...') }}" icon="magnifying-glass" size="sm" />
        </div>
        <flux:select wire:model.live="status" size="sm" class="w-40">
            <option value="">{{ __('All Statuses') }}</option>
            @foreach ($this->statusOptions as $opt)
                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
            @endforeach
        </flux:select>
        @if ($search || $status)
            <flux:button size="sm" variant="ghost" wire:click="resetFilters">{{ __('Clear') }}</flux:button>
        @endif
    </div>

    {{-- Payrolls Table --}}
    <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-ivory-dark/30 bg-ivory-light text-xs uppercase text-smoke-muted">
                    <tr>
                        <th class="px-5 py-3">{{ __('Worker') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Amount') }}</th>
                        <th class="px-5 py-3">{{ __('Period') }}</th>
                        <th class="px-5 py-3">{{ __('Status') }}</th>
                        <th class="px-5 py-3">{{ __('Notes') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ivory-dark/30">
                    @forelse ($this->payrolls as $payroll)
                        <tr class="hover:bg-ivory-light">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex size-8 items-center justify-center rounded-full bg-terra/10 text-xs font-bold text-terra">
                                        {{ $payroll->worker->initials() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-smoke">{{ $payroll->worker->name }}</p>
                                        <span class="font-mono text-xs text-smoke-muted">{{ $payroll->worker->global_number }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-smoke">{{ number_format($payroll->amount, 0) }}</td>
                            <td class="px-5 py-3 text-xs text-smoke-muted">
                                {{ $payroll->period_start->format('M d') }} — {{ $payroll->period_end->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3">
                                <flux:badge size="sm" :color="$payroll->status->color()">{{ $payroll->status->label() }}</flux:badge>
                            </td>
                            <td class="max-w-[150px] truncate px-5 py-3 text-xs text-smoke-muted">
                                {{ $payroll->notes ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if ($payroll->status === \App\Enums\PayrollStatus::Pending)
                                    <flux:button size="sm" wire:click="markPaid({{ $payroll->id }})" wire:confirm="{{ __('Mark this payroll as paid?') }}" class="!bg-emerald-600 !text-white hover:!bg-emerald-700">
                                        {{ __('Pay') }}
                                    </flux:button>
                                @else
                                    <span class="text-xs text-emerald-600">{{ __('Paid') }} {{ $payroll->paid_at?->format('M d') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <flux:icon.banknotes class="mx-auto size-8 text-smoke-muted/40" />
                                <p class="mt-3 text-sm text-smoke-muted">{{ __('No payroll records found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($this->payrolls->hasPages())
            <div class="border-t border-ivory-dark/30 px-5 py-3">
                {{ $this->payrolls->links() }}
            </div>
        @endif
    </div>

    {{-- Create Payroll Modal --}}
    <flux:modal wire:model="showCreateModal" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create Payroll') }}</flux:heading>
                <flux:text class="mt-1 text-smoke-muted">{{ __('Record a new payroll entry for a worker.') }}</flux:text>
            </div>

            <div class="space-y-4">
                <flux:select wire:model="payrollWorkerId" label="{{ __('Worker') }}">
                    <option value="">{{ __('Select a worker...') }}</option>
                    @foreach ($this->activeWorkers as $worker)
                        <option value="{{ $worker->id }}">{{ $worker->name }} ({{ $worker->global_number }})</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="payrollAmount" type="number" min="1" label="{{ __('Amount') }}" placeholder="0" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="payrollPeriodStart" type="date" label="{{ __('Period Start') }}" />
                    <flux:input wire:model="payrollPeriodEnd" type="date" label="{{ __('Period End') }}" />
                </div>

                <flux:textarea wire:model="payrollNotes" label="{{ __('Notes') }}" placeholder="{{ __('Optional notes...') }}" rows="2" />
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$toggle('showCreateModal')">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="savePayroll" class="!bg-terra !text-white hover:!bg-terra-dark">
                    {{ __('Create') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

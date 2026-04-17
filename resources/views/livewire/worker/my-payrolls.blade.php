<div class="flex h-full w-full flex-1 flex-col gap-6">

    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('My Payrolls') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Your earnings and payment history at :business.', ['business' => $this->business?->name ?? '—']) }}</p>
    </div>

    @if (! $this->business)
        <div class="rounded-2xl border-2 border-dashed border-ivory-dark/50 bg-ivory-light p-10 text-center">
            <flux:icon.link-slash class="mx-auto size-10 text-smoke-muted" />
            <p class="mt-4 text-sm text-smoke-muted">{{ __('You need to be linked to a business to see payrolls.') }}</p>
        </div>
    @else
        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-emerald-700">{{ __('Total Earned') }}</p>
                <p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($this->summary['total_earned'], 0) }}</p>
                <p class="mt-1 text-xs text-emerald-600/70">{{ $this->summary['paid_count'] }} {{ __('payments') }}</p>
            </div>
            <div class="rounded-2xl border border-terra-200 bg-terra-50 p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-terra-dark">{{ __('Pending') }}</p>
                <p class="mt-2 text-2xl font-bold text-smoke">{{ number_format($this->summary['pending'], 0) }}</p>
                <p class="mt-1 text-xs text-terra-dark/70">{{ $this->summary['pending_count'] }} {{ __('awaiting') }}</p>
            </div>
            <div class="col-span-2 rounded-2xl border border-ivory-dark/40 bg-white p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-smoke-muted">{{ __('Lifetime Summary') }}</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-smoke">{{ number_format($this->summary['total_earned'] + $this->summary['pending'], 0) }}</p>
                    <span class="text-xs text-smoke-muted">TZS {{ __('total') }}</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-ivory-light">
                    @php $total = $this->summary['total_earned'] + $this->summary['pending']; @endphp
                    @if ($total > 0)
                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ ($this->summary['total_earned'] / $total) * 100 }}%"></div>
                    @endif
                </div>
                <div class="mt-1.5 flex justify-between text-xs text-smoke-muted">
                    <span class="flex items-center gap-1"><span class="size-2 rounded-full bg-emerald-500"></span> {{ __('Paid') }}</span>
                    <span class="flex items-center gap-1"><span class="size-2 rounded-full bg-ivory-dark/30"></span> {{ __('Pending') }}</span>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="flex items-center gap-2">
            @foreach (['' => __('All'), 'pending' => __('Pending'), 'paid' => __('Paid')] as $key => $label)
                <button
                    wire:click="$set('status', '{{ $key }}')"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $status === $key ? 'bg-smoke text-white' : 'bg-white text-smoke-muted hover:bg-ivory-light border border-ivory-dark/30' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Payrolls Table --}}
        <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
            @if ($this->payrolls->isEmpty())
                <div class="p-10 text-center">
                    <flux:icon.banknotes class="mx-auto size-10 text-smoke-muted/30" />
                    <p class="mt-3 text-sm text-smoke-muted">{{ __('No payroll records yet.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-ivory-dark/30 bg-ivory-light text-xs uppercase text-smoke-muted">
                            <tr>
                                <th class="px-5 py-3">{{ __('Period') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Amount') }}</th>
                                <th class="px-5 py-3">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Paid Date') }}</th>
                                <th class="px-5 py-3">{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ivory-dark/30">
                            @foreach ($this->payrolls as $payroll)
                                <tr class="hover:bg-ivory-light">
                                    <td class="px-5 py-3">
                                        <span class="text-sm font-medium text-smoke">{{ $payroll->period_start->format('M d') }} — {{ $payroll->period_end->format('M d, Y') }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="font-semibold text-smoke">{{ number_format($payroll->amount, 0) }}</span>
                                        <span class="text-xs text-smoke-muted">TZS</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <flux:badge size="sm" :color="$payroll->status->color()">{{ $payroll->status->label() }}</flux:badge>
                                    </td>
                                    <td class="px-5 py-3 text-right text-xs text-smoke-muted">
                                        {{ $payroll->paid_at ? $payroll->paid_at->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="max-w-[200px] truncate px-5 py-3 text-xs text-smoke-muted">
                                        {{ $payroll->notes ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($this->payrolls->hasPages())
                    <div class="border-t border-ivory-dark/30 px-5 py-3">
                        {{ $this->payrolls->links() }}
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>

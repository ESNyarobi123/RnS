<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('All Workers') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('View all workers, their TIP numbers, and business links') }}</p>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search by name, email, or TIP number...')" />
        </div>
        <flux:select wire:model.live="linkStatus" class="w-44">
            <flux:select.option value="">{{ __('All Workers') }}</flux:select.option>
            <flux:select.option value="linked">{{ __('Linked') }}</flux:select.option>
            <flux:select.option value="unlinked">{{ __('Unlinked') }}</flux:select.option>
        </flux:select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-ivory-dark/30 bg-ivory-light text-xs uppercase text-smoke-muted">
                    <tr>
                        <th class="px-6 py-3">{{ __('Worker') }}</th>
                        <th class="px-6 py-3">{{ __('TIP Number') }}</th>
                        <th class="px-6 py-3">{{ __('Contact') }}</th>
                        <th class="px-6 py-3">{{ __('Current Business') }}</th>
                        <th class="px-6 py-3">{{ __('Role') }}</th>
                        <th class="px-6 py-3 text-center">{{ __('Orders') }}</th>
                        <th class="px-6 py-3">{{ __('Link History') }}</th>
                        <th class="px-6 py-3">{{ __('Joined') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ivory-dark/30">
                    @forelse ($this->workers as $worker)
                        @php
                            $activeLink = $worker->businessLinks->where('is_active', true)->first();
                            $pastLinks = $worker->businessLinks->where('is_active', false);
                        @endphp
                        <tr class="hover:bg-ivory-light">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 items-center justify-center rounded-full bg-terra/10 text-sm font-bold text-terra">
                                        {{ $worker->initials() }}
                                    </div>
                                    <flux:text class="font-medium">{{ $worker->name }}</flux:text>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text class="font-mono text-sm font-semibold">{{ $worker->global_number }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text size="sm">{{ $worker->email }}</flux:text>
                                @if ($worker->phone)
                                    <flux:text size="sm" class="text-smoke-muted">{{ $worker->phone }}</flux:text>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($activeLink)
                                    <div>
                                        <flux:text class="font-medium">{{ $activeLink->business?->name }}</flux:text>
                                        <div class="mt-1 flex gap-1">
                                            <flux:badge size="sm" :color="$activeLink->business?->type === \App\Enums\BusinessType::Restaurant ? 'amber' : 'violet'">
                                                {{ $activeLink->business?->type->label() }}
                                            </flux:badge>
                                            <flux:badge size="sm" :color="$activeLink->link_type->value === 'permanent' ? 'green' : 'yellow'">
                                                {{ $activeLink->link_type->label() }}
                                            </flux:badge>
                                        </div>
                                    </div>
                                @else
                                    <flux:badge size="sm" color="gray">{{ __('Unlinked') }}</flux:badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($activeLink)
                                    <flux:text class="font-medium">{{ $activeLink->business?->type->workerTitle() }}</flux:text>
                                @else
                                    <flux:text class="text-smoke-muted">{{ __('Worker') }}</flux:text>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <flux:text class="font-semibold">{{ $worker->assigned_orders_count }}</flux:text>
                            </td>
                            <td class="px-6 py-4">
                                <flux:text size="sm">{{ $worker->businessLinks->count() }} {{ __('total') }}</flux:text>
                                @if ($pastLinks->isNotEmpty())
                                    <flux:text size="sm" class="text-smoke-muted">{{ $pastLinks->count() }} {{ __('past') }}</flux:text>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <flux:text size="sm">{{ $worker->created_at->format('M d, Y') }}</flux:text>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <flux:icon.users class="mx-auto size-10 text-smoke-muted/40" />
                                <p class="mt-2 text-sm text-smoke-muted">{{ __('No workers found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->workers->links() }}
</div>

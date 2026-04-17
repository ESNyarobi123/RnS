<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('Shift Management') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Manage {{ $workerTitle }} schedules and shifts') }}</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-terra/10">
                    <flux:icon.calendar-days class="size-5 text-terra" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Total Shifts') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $stats['total_shifts'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100">
                    <flux:icon.clock class="size-5 text-emerald-600" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Scheduled') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $stats['scheduled_shifts'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-blue-100">
                    <flux:icon.play class="size-5 text-blue-600" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('In Progress') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $stats['in_progress_shifts'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-yellow-100">
                    <flux:icon.check-circle class="size-5 text-yellow-600" />
                </div>
                <div>
                    <p class="text-sm text-smoke-muted">{{ __('Completed') }}</p>
                    <p class="text-2xl font-bold text-smoke">{{ $stats['completed_shifts'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:input
                    wire:model.live="search"
                    placeholder="{{ __('Search {{ $workerTitle }}...') }}"
                    class="w-full sm:w-64"
                />
                
                <flux:select
                    wire:model.live="status"
                    placeholder="{{ __('All Status') }}"
                    class="w-full sm:w-40"
                >
                    <option value="all">{{ __('All Status') }}</option>
                    <option value="scheduled">{{ __('Scheduled') }}</option>
                    <option value="in_progress">{{ __('In Progress') }}</option>
                    <option value="completed">{{ __('Completed') }}</option>
                </flux:select>

                <flux:select
                    wire:model.live="dateFilter"
                    placeholder="{{ __('All Dates') }}"
                    class="w-full sm:w-40"
                >
                    <option value="today">{{ __('Today') }}</option>
                    <option value="week">{{ __('This Week') }}</option>
                    <option value="month">{{ __('This Month') }}</option>
                </flux:select>
            </div>

            <flux:button wire:click="create" class="!bg-terra !text-white hover:!bg-terra-dark">
                <flux:icon.plus class="mr-1 size-4" />
                {{ __('Schedule Shift') }}
            </flux:button>
        </div>
    </div>

    {{-- Shifts Table --}}
    <div class="rounded-2xl border border-ivory-dark/40 bg-white">
        @if ($shifts->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-ivory-light">
                        <tr class="text-left text-sm text-smoke-muted">
                            <th class="px-6 py-3 font-medium">{{ __('{{ $workerTitle }}') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Shift Type') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Start Time') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('End Time') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Date') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                            <th class="px-6 py-3 font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ivory-dark/30">
                        @foreach ($shifts as $shift)
                            <tr class="hover:bg-ivory-light transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-10 items-center justify-center rounded-full bg-terra/10">
                                            <flux:icon.user class="size-5 text-terra" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-smoke">{{ $shift['worker_name'] }}</p>
                                            <p class="text-sm text-smoke-muted">{{ $shift['worker_email'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium
                                        {{ $shift['shift_type'] === 'regular' ? 'bg-emerald-100 text-emerald-700' : 
                                          ($shift['shift_type'] === 'overtime' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ucfirst($shift['shift_type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-smoke font-medium">{{ $shift['start_time'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-smoke font-medium">{{ $shift['end_time'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-smoke font-medium">{{ $shift['date'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium
                                        {{ $shift['status'] === 'scheduled' ? 'bg-gray-100 text-gray-700' : 
                                          ($shift['status'] === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        {{ ucfirst($shift['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <flux:button
                                            wire:click="edit({{ $shift['id'] }})"
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <flux:icon.pencil class="size-4" />
                                        </flux:button>
                                        
                                        <flux:dropdown position="bottom" align="end">
                                            <flux:button variant="ghost" size="sm">
                                                <flux:icon.ellipsis-vertical class="size-4" />
                                            </flux:button>

                                            <flux:menu>
                                                <flux:menu.item icon="play" wire:click="$dispatch('start-shift', { shiftId: {{ $shift['id'] }} })">
                                                    {{ __('Start Shift') }}
                                                </flux:menu.item>
                                                
                                                <flux:menu.item icon="stop" wire:click="$dispatch('end-shift', { shiftId: {{ $shift['id'] }} })">
                                                    {{ __('End Shift') }}
                                                </flux:menu.item>
                                                
                                                <flux:menu.separator />
                                                
                                                <flux:menu.item 
                                                    wire:click="$dispatch('delete-shift', { shiftId: {{ $shift['id'] }} })" 
                                                    icon="trash"
                                                    variant="danger"
                                                    wire:confirm="{{ __('Delete this shift? This action cannot be undone.') }}"
                                                >
                                                    {{ __('Delete') }}
                                                </flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $shifts->links() }}
        @else
            <div class="p-12 text-center">
                <flux:icon.calendar-days class="mx-auto size-12 text-smoke-muted" />
                <h3 class="mt-4 text-lg font-semibold text-smoke">{{ __('No shifts scheduled') }}</h3>
                <p class="mt-2 text-sm text-smoke-muted">
                    {{ __('Get started by scheduling your first {{ $workerTitle }} shift.') }}
                </p>
                <flux:button wire:click="create" class="mt-4 !bg-terra !text-white hover:!bg-terra-dark">
                    <flux:icon.plus class="mr-1 size-4" />
                    {{ __('Schedule Shift') }}
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Shift Modal --}}
    <flux:modal name="shift-modal" class="max-w-2xl">
        <flux:heading size="lg">
            {{ $editingWorker ? __('Edit Shift') : __('Schedule New Shift') }}
        </flux:heading>

        <form wire:submit="saveShift" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:select
                        wire:model="editingWorker"
                        label="{{ __('{{ $workerTitle }}') }}"
                        placeholder="{{ __('Select {{ $workerTitle }}...') }}"
                        required
                    >
                        <option value="">{{ __('Select {{ $workerTitle }}...') }}</option>
                        @foreach ($workers as $worker)
                            <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select
                        wire:model="shiftType"
                        label="{{ __('Shift Type') }}"
                        required
                    >
                        <option value="regular">{{ __('Regular') }}</option>
                        <option value="overtime">{{ __('Overtime') }}</option>
                        <option value="holiday">{{ __('Holiday') }}</option>
                    </flux:select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="shiftStart"
                        label="{{ __('Start Time') }}"
                        type="time"
                        required
                    />

                    <flux:input
                        wire:model="shiftEnd"
                        label="{{ __('End Time') }}"
                        type="time"
                        required
                    />
                </div>

                <flux:checkbox
                    wire:model="isRecurring"
                    label="{{ __('Recurring Shift') }}"
                    hint="{{ __('This shift repeats on selected days') }}"
                />

                @if ($isRecurring)
                    <flux:checkbox-group
                        label="{{ __('Select Days') }}"
                        wire:model="selectedDays"
                        :options="[
                            ['monday' => __('Monday')],
                            ['tuesday' => __('Tuesday')],
                            ['wednesday' => __('Wednesday')],
                            ['thursday' => __('Thursday')],
                            ['friday' => __('Friday')],
                            ['saturday' => __('Saturday')],
                            ['sunday' => __('Sunday')],
                        ]"
                    />
                @endif

                <div class="flex justify-end gap-2 pt-4">
                    <flux:button
                        type="button"
                        wire:click="$dispatch('close-modal', 'shift-modal')"
                        variant="ghost"
                    >
                        {{ __('Cancel') }}
                    </flux:button>
                    
                    <flux:button type="submit" class="!bg-terra !text-white hover:!bg-terra-dark">
                        {{ $editingWorker ? __('Update') : __('Create') }}
                    </flux:button>
                </div>
        </form>
    </flux:modal>

    @script
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('start-shift', (data) => {
                // Handle shift start
                Flux?.toast?.({ variant: 'success', text: '{{ __('Shift started successfully.') }}' });
            });

            Livewire.on('end-shift', (data) => {
                // Handle shift end
                Flux?.toast?.({ variant: 'success', text: '{{ __('Shift ended successfully.') }}' });
            });

            Livewire.on('delete-shift', (data) => {
                // Handle shift deletion
                Flux?.toast?.({ variant: 'success', text: '{{ __('Shift deleted successfully.') }}' });
            });
        });
    </script>
    @endscript
</div>

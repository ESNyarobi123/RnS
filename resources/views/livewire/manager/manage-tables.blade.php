<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ $title }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Create :label, rename them, and generate QR codes for WhatsApp entry points.', ['label' => strtolower($title)]) }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Total') }}</p><p class="mt-2 text-2xl font-bold text-smoke">{{ $stats['total'] }}</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Available') }}</p><p class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['available'] }}</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Occupied') }}</p><p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['occupied'] }}</p></div>
        <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6"><p class="text-sm text-smoke-muted">{{ __('Reserved') }}</p><p class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['reserved'] }}</p></div>
    </div>

    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:input wire:model.live="search" placeholder="{{ __('Search...') }}" class="w-full sm:w-64" />
                <flux:select wire:model.live="status" class="w-full sm:w-40">
                    <option value="all">{{ __('All Status') }}</option>
                    <option value="available">{{ __('Available') }}</option>
                    <option value="occupied">{{ __('Occupied') }}</option>
                    <option value="reserved">{{ __('Reserved') }}</option>
                </flux:select>
            </div>

            <flux:button wire:click="create" class="!bg-terra !text-white hover:!bg-terra-dark">
                <flux:icon.plus class="mr-1 size-4" />
                {{ __('Add :label', ['label' => $singleLabel]) }}
            </flux:button>
        </div>
    </div>

    <div class="rounded-2xl border border-ivory-dark/40 bg-white">
        @if ($tables->count() > 0)
            <div class="divide-y divide-ivory-dark/30">
                @foreach ($tables as $table)
                    <div class="p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex size-12 items-center justify-center rounded-full bg-terra/10">
                                    <flux:icon.table-cells class="size-5 text-terra" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-smoke">{{ $table->display_name }}</h3>
                                    <p class="mt-1 text-sm text-smoke-muted">
                                        {{ __('Capacity') }}: {{ $table->capacity }} · {{ ucfirst($table->status) }}
                                    </p>
                                    @if ($table->qr_code)
                                        <p class="mt-1 font-mono text-xs text-terra">{{ $table->qr_code }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                @if ($table->qr_image_path)
                                    <img src="{{ asset('storage/'.$table->qr_image_path) }}" class="h-20 w-20 rounded-2xl bg-white p-2 ring-1 ring-ivory-dark/30" />
                                @endif

                                <div class="flex items-center gap-2">
                                    <flux:button wire:click="regenerateQrCode({{ $table->id }})" variant="ghost" size="sm">
                                        <flux:icon.qr-code class="size-4 text-terra" />
                                    </flux:button>
                                    <flux:button wire:click="edit({{ $table->id }})" variant="ghost" size="sm">
                                        <flux:icon.pencil class="size-4 text-smoke-muted" />
                                    </flux:button>
                                    <flux:button wire:click="toggleStatus({{ $table->id }})" variant="ghost" size="sm">
                                        <flux:icon.arrow-path class="size-4 text-smoke-muted" />
                                    </flux:button>
                                    <flux:button wire:click="delete({{ $table->id }})" variant="ghost" size="sm" wire:confirm="{{ __('Delete this :label?', ['label' => strtolower($singleLabel)]) }}">
                                        <flux:icon.trash class="size-4 text-red-500" />
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $tables->links() }}
        @else
            <div class="p-12 text-center">
                <flux:icon.table-cells class="mx-auto size-12 text-smoke-muted" />
                <h3 class="mt-4 text-lg font-semibold text-smoke">{{ __('No :label yet', ['label' => strtolower($title)]) }}</h3>
                <p class="mt-2 text-sm text-smoke-muted">{{ __('Create the first one so the system can generate a WhatsApp QR for customers.') }}</p>
            </div>
        @endif
    </div>

    <flux:modal wire:model="showTableModal" class="max-w-md">
        <flux:heading size="lg">{{ $editingTable ? __('Edit :label', ['label' => $singleLabel]) : __('Add :label', ['label' => $singleLabel]) }}</flux:heading>
        <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="tableName" :label="__('Name')" required />
                <flux:input wire:model="capacity" :label="__('Capacity')" type="number" min="1" max="20" required />
                <flux:select wire:model="statusFilter" :label="__('Status')" required>
                    <option value="available">{{ __('Available') }}</option>
                    <option value="occupied">{{ __('Occupied') }}</option>
                    <option value="reserved">{{ __('Reserved') }}</option>
                </flux:select>
                <div class="flex justify-end gap-2 pt-4">
                    <flux:button type="button" wire:click="$set('showTableModal', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" class="!bg-terra !text-white hover:!bg-terra-dark">{{ $editingTable ? __('Update') : __('Create') }}</flux:button>
                </div>
        </form>
    </flux:modal>
</div>

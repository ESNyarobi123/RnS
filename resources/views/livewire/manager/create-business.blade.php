<div class="mx-auto w-full max-w-lg">
        <h1 class="text-2xl font-bold text-smoke">{{ __('Create Your Business') }}</h1>
        <p class="mt-2 text-sm text-smoke-muted">{{ __('Set up your business to start managing your team and services.') }}</p>

        <form wire:submit="save" class="mt-8 space-y-6">
            <flux:input
                wire:model="name"
                :label="__('Business Name')"
                :placeholder="__('e.g. Mama Lishe Restaurant')"
                required
            />

            <flux:select wire:model="type" :label="__('Business Type')" required>
                <flux:select.option value="">{{ __('Select type') }}</flux:select.option>
                <flux:select.option value="restaurant">{{ __('Restaurant') }}</flux:select.option>
                <flux:select.option value="salon">{{ __('Salon') }}</flux:select.option>
            </flux:select>

            <flux:textarea
                wire:model="description"
                :label="__('Description')"
                :placeholder="__('Briefly describe your business')"
                rows="3"
            />

            <flux:input
                wire:model="address"
                :label="__('Address')"
                :placeholder="__('Business location')"
            />

            <flux:input
                wire:model="phone"
                :label="__('Phone')"
                type="tel"
                :placeholder="__('e.g. +255 700 000 000')"
            />

            <div>
                <flux:label>{{ __('Logo') }}</flux:label>
                <flux:input wire:model="logo" type="file" accept="image/*" class="mt-1" />
                @if ($logo)
                    <img src="{{ $logo->temporaryUrl() }}" alt="Preview" class="mt-2 size-20 rounded-lg object-cover" />
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <flux:button :href="route('manager.dashboard')">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit">{{ __('Create Business') }}</flux:button>
            </div>
        </form>
</div>

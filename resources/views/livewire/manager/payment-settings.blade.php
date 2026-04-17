<div class="flex h-full w-full flex-1 flex-col gap-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('Payment Settings') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Configure your Selcom payment gateway credentials for receiving payments.') }}</p>
    </div>

    {{-- Info Banner --}}
    <div class="rounded-2xl border border-terra/30 bg-terra-50 p-4">
        <div class="flex gap-3">
            <flux:icon.shield-check class="mt-0.5 size-5 shrink-0 text-terra" />
            <div class="text-sm text-smoke">
                <p class="font-semibold">{{ __('Secure & Isolated') }}</p>
                <p class="mt-1 text-smoke-muted">{{ __('Your API credentials are encrypted and used only for your business payments. Each business has its own separate payment configuration.') }}</p>
            </div>
        </div>
    </div>

    {{-- Selcom Credentials Card --}}
    <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="border-b border-ivory-dark/30 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <flux:icon.credit-card class="size-4 text-terra" />
                    <h2 class="font-semibold text-smoke">{{ __('Selcom API Credentials') }}</h2>
                </div>
                @if ($this->setting?->is_active)
                    <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm">{{ __('Inactive') }}</flux:badge>
                @endif
            </div>
        </div>
        <div class="p-6">
            <div class="space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="vendor" label="{{ __('Vendor / Merchant ID') }}" placeholder="{{ __('e.g. VENDOR001') }}" description="{{ __('Your Selcom vendor/till ID.') }}" />
                    </div>

                    <flux:input wire:model="apiKey" type="password" label="{{ __('API Key') }}" placeholder="{{ __('Your Selcom API key') }}" />

                    <flux:input wire:model="apiSecret" type="password" label="{{ __('API Secret') }}" placeholder="{{ __('Your Selcom API secret') }}" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <flux:select wire:model="environment" label="{{ __('Environment') }}">
                            <option value="sandbox">{{ __('Sandbox (Testing)') }}</option>
                            <option value="production">{{ __('Production (Live)') }}</option>
                        </flux:select>
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" wire:model="isActive" class="size-4 rounded border-ivory-dark/40 text-terra focus:ring-terra" />
                            <span class="text-sm font-medium text-smoke">{{ __('Enable Selcom payments') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-ivory-dark/30 pt-6">
                <flux:button wire:click="testConnection" variant="ghost" class="text-sm !text-terra hover:!text-terra-dark">
                    <flux:icon.signal class="mr-1 size-4" />
                    {{ __('Test Connection') }}
                </flux:button>
                <flux:button wire:click="save" class="!bg-terra !text-white hover:!bg-terra-dark">
                    {{ __('Save Credentials') }}
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Help Section --}}
    <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
        <div class="border-b border-ivory-dark/30 px-6 py-4">
            <div class="flex items-center gap-2">
                <flux:icon.question-mark-circle class="size-4 text-terra" />
                <h2 class="font-semibold text-smoke">{{ __('How to get credentials') }}</h2>
            </div>
        </div>
        <div class="p-6">
            <ol class="space-y-3 text-sm text-smoke-muted">
                <li class="flex gap-3">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-terra/10 text-xs font-bold text-terra">1</span>
                    <span>{{ __('Visit Selcom Developers portal at developers.selcommobile.com') }}</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-terra/10 text-xs font-bold text-terra">2</span>
                    <span>{{ __('Register your business and create an API application') }}</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-terra/10 text-xs font-bold text-terra">3</span>
                    <span>{{ __('Copy your API Key, API Secret, and Vendor ID') }}</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-terra/10 text-xs font-bold text-terra">4</span>
                    <span>{{ __('Paste them above, start with Sandbox for testing, then switch to Production when ready') }}</span>
                </li>
            </ol>
        </div>
    </div>
</div>

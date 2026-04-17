<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('Bot Control') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Configure WhatsApp bot settings for all businesses') }}</p>
    </div>

    {{-- Bot Status Card --}}
    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full {{ $isActive ? 'bg-emerald-100' : 'bg-gray-100' }}">
                    <flux:icon.power class="size-5 {{ $isActive ? 'text-emerald-600' : 'text-gray-400' }}" />
                </div>
                <div>
                    <h3 class="font-semibold text-smoke">{{ __('Bot Status') }}</h3>
                    <p class="text-sm text-smoke-muted">{{ $isActive ? __('Active') : __('Inactive') }}</p>
                </div>
            </div>
            <flux:button 
                wire:click="toggleBot" 
                :variant="$isActive ? 'danger' : 'primary'"
                wire:confirm="{{ $isActive ? __('Deactivate the bot? All WhatsApp services will stop working.') : __('Activate the bot?') }}"
            >
                {{ $isActive ? __('Deactivate') : __('Activate') }}
            </flux:button>
        </div>
    </div>

    {{-- Settings Form --}}
    <div class="rounded-2xl border border-ivory-dark/40 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-smoke">{{ __('Bot Settings') }}</h2>
        
        <form wire:submit="save" class="space-y-4">
            {{-- WhatsApp Number --}}
            <flux:input 
                wire:model="phoneNumber" 
                label="{{ __('WhatsApp Number') }}" 
                placeholder="255700000000"
                description="{{ __('The WhatsApp number that will be used for the bot (include country code)') }}"
                required
            />

            {{-- Bot Secret Key --}}
            <div>
                <flux:input 
                    wire:model="secretKey" 
                    label="{{ __('Bot Secret Key') }}" 
                    placeholder="{{ __('Secret key for API authentication') }}"
                    type="password"
                    required
                />
                <div class="mt-2 flex items-center gap-2">
                    <flux:button 
                        wire:click="generateSecretKey" 
                        variant="ghost" 
                        size="sm"
                    >
                        <flux:icon.key class="mr-1 size-3.5" />
                        {{ __('Generate New Key') }}
                    </flux:button>
                    @if ($secretKey)
                        <span class="text-xs text-emerald-600">{{ __('Key generated') }}</span>
                    @endif
                </div>
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <flux:button type="submit" class="!bg-terra !text-white hover:!bg-terra-dark">
                    <flux:icon.device-phone-mobile class="mr-1 size-4" />
                    {{ __('Save Bot Settings') }}
                </flux:button>
            </div>
        </form>
    </div>

    {{-- Info Card --}}
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
        <div class="flex gap-3">
            <flux:icon.information-circle class="size-5 text-amber-600 flex-shrink-0" />
            <div class="text-sm text-amber-800">
                <h4 class="font-semibold">{{ __('Important Notes') }}</h4>
                <ul class="mt-2 space-y-1 text-xs">
                    <li>• {{ __('The secret key must be kept secure and shared only with the WhatsApp bot application') }}</li>
                    <li>• {{ __('WhatsApp number should include country code (e.g., 255700000000)') }}</li>
                    <li>• {{ __('Bot must be activated before WhatsApp services will work') }}</li>
                    <li>• {{ __('Each business will generate its own QR code after bot is configured') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

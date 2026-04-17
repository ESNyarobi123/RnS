<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-smoke">{{ __('Business Settings') }}</h1>
        <p class="mt-1 text-sm text-smoke-muted">{{ __('Update branding, WhatsApp bot assets, and the customer-facing QR flow.') }}</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.4fr,0.9fr]">
        <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
            <div class="border-b border-ivory-dark/30 px-6 py-4">
                <div class="flex items-center gap-2">
                    <flux:icon.building-storefront class="size-4 text-terra" />
                    <h2 class="font-semibold text-smoke">{{ __('Business Information') }}</h2>
                </div>
            </div>
            <div class="space-y-5 p-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-smoke">{{ __('Logo') }}</label>
                    <div class="flex items-center gap-4">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" class="size-16 rounded-2xl object-cover ring-2 ring-terra/30" />
                        @elseif ($this->business->hasImage())
                            <img src="{{ $this->business->imageUrl() }}" class="size-16 rounded-2xl object-cover ring-2 ring-ivory-dark/30" />
                        @else
                            <div class="flex size-16 items-center justify-center rounded-2xl bg-ivory-light">
                                <flux:icon.building-storefront class="size-8 text-smoke-muted/40" />
                            </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer rounded-lg bg-ivory-light px-4 py-2 text-sm font-medium text-smoke transition hover:bg-ivory-dark/30">
                                {{ __('Upload') }}
                                <input type="file" wire:model="logo" accept="image/*" class="hidden" />
                            </label>
                            @if ($this->business->hasImage())
                                <flux:button size="sm" variant="ghost" wire:click="removeLogo" wire:confirm="{{ __('Remove the logo?') }}">
                                    <flux:icon.trash class="size-4 text-red-500" />
                                </flux:button>
                            @endif
                        </div>
                    </div>
                    @error('logo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="name" label="{{ __('Business Name') }}" />
                    </div>
                    <div class="sm:col-span-2">
                        <flux:textarea wire:model="description" label="{{ __('Description') }}" rows="3" />
                    </div>
                    <flux:input wire:model="address" label="{{ __('Address') }}" />
                    <flux:input wire:model="phone" label="{{ __('Phone') }}" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-smoke">{{ __('Menu Image for WhatsApp') }}</label>
                    <div class="flex items-center gap-4">
                        @if ($menuImage)
                            <img src="{{ $menuImage->temporaryUrl() }}" class="h-20 w-28 rounded-2xl object-cover ring-2 ring-terra/20" />
                        @elseif ($this->business->hasMenuImage())
                            <img src="{{ $this->business->menuImageUrl() }}" class="h-20 w-28 rounded-2xl object-cover ring-2 ring-ivory-dark/20" />
                        @else
                            <div class="flex h-20 w-28 items-center justify-center rounded-2xl bg-ivory-light text-xs text-smoke-muted">
                                {{ __('No menu image') }}
                            </div>
                        @endif

                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer rounded-lg bg-ivory-light px-4 py-2 text-sm font-medium text-smoke transition hover:bg-ivory-dark/30">
                                {{ __('Upload') }}
                                <input type="file" wire:model="menuImage" accept="image/*" class="hidden" />
                            </label>
                            @if ($this->business->hasMenuImage())
                                <flux:button size="sm" variant="ghost" wire:click="removeMenuImage" wire:confirm="{{ __('Remove the menu image?') }}">
                                    <flux:icon.trash class="size-4 text-red-500" />
                                </flux:button>
                            @endif
                        </div>
                    </div>
                    @error('menuImage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between border-t border-ivory-dark/30 pt-6">
                    <div class="text-sm text-smoke-muted">
                        {{ __('Business type: :type', ['type' => $this->business->type->label()]) }}
                    </div>
                    <flux:button wire:click="save" class="!bg-terra !text-white hover:!bg-terra-dark">
                        {{ __('Save Changes') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
                <div class="border-b border-ivory-dark/30 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <flux:icon.qr-code class="size-4 text-terra" />
                        <h2 class="font-semibold text-smoke">{{ __('WhatsApp QR & Code') }}</h2>
                    </div>
                </div>
                <div class="space-y-4 p-6">
                    @if ($this->botSetting)
                        <div class="rounded-2xl bg-ivory-light p-4">
                            <p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Bot Number') }}</p>
                            <p class="mt-1 font-semibold text-smoke">{{ $this->botSetting->phone_number }}</p>
                        </div>
                    @else
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            {{ __('Admin must activate Bot Control before QR links can send customers to WhatsApp.') }}
                        </div>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-ivory-dark/30 p-4">
                            <p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Business Code') }}</p>
                            <p class="mt-2 break-all font-mono text-lg font-semibold text-terra">{{ $this->business->bot_code ?? __('Not generated yet') }}</p>
                            <p class="mt-2 text-xs text-smoke-muted">{{ __('This is the same code customers can type manually in WhatsApp.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-ivory-dark/30 p-4 text-center">
                            @if ($this->business->qr_image_path)
                                <img src="{{ asset('storage/'.$this->business->qr_image_path) }}" class="mx-auto h-40 w-40 rounded-2xl bg-white p-3 ring-1 ring-ivory-dark/30" />
                            @else
                                <div class="mx-auto flex h-40 w-40 items-center justify-center rounded-2xl bg-ivory-light text-sm text-smoke-muted">
                                    {{ __('QR not generated') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <flux:button wire:click="generateBotAssets" class="w-full !bg-smoke !text-ivory hover:!bg-smoke-light">
                        <flux:icon.qr-code class="mr-2 size-4" />
                        {{ __('Generate / Refresh Business QR') }}
                    </flux:button>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-ivory-dark/40 bg-white">
                <div class="border-b border-ivory-dark/30 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <flux:icon.chart-bar class="size-4 text-terra" />
                        <h2 class="font-semibold text-smoke">{{ __('Business Overview') }}</h2>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-px bg-ivory-dark/30 sm:grid-cols-4">
                    <div class="bg-white p-5 text-center">
                        <p class="text-2xl font-bold text-smoke">{{ $this->business->activeWorkerLinks()->count() }}</p>
                        <p class="mt-1 text-xs text-smoke-muted">{{ $this->business->type->workerTitlePlural() }}</p>
                    </div>
                    <div class="bg-white p-5 text-center">
                        <p class="text-2xl font-bold text-smoke">{{ $this->business->activeProducts()->count() }}</p>
                        <p class="mt-1 text-xs text-smoke-muted">{{ $this->business->type->itemLabelPlural() }}</p>
                    </div>
                    <div class="bg-white p-5 text-center">
                        <p class="text-2xl font-bold text-smoke">{{ $this->business->orders()->count() }}</p>
                        <p class="mt-1 text-xs text-smoke-muted">{{ __('Total Orders') }}</p>
                    </div>
                    <div class="bg-white p-5 text-center">
                        <p class="text-2xl font-bold text-smoke">{{ $this->business->status->label() }}</p>
                        <p class="mt-1 text-xs text-smoke-muted">{{ __('Status') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

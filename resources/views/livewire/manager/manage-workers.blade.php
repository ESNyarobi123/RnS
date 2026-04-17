<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-smoke">{{ $this->workerTitlePlural }}</h1>
                <p class="mt-1 text-sm text-smoke-muted">{{ __('Manage your team and find workers by their TIP number.') }}</p>
            </div>
            <flux:button wire:click="openLinkModal" class="!bg-terra !text-white hover:!bg-terra-dark">
                <flux:icon.plus class="-ml-1 mr-1 size-4" />
                {{ __('Link :title', ['title' => $this->workerTitle]) }}
            </flux:button>
        </div>

        {{-- TIP Search --}}
        <div class="rounded-2xl border border-ivory-dark/40 bg-white">
            <div class="border-b border-ivory-dark/30 px-6 py-4">
                <div class="flex items-center gap-2">
                    <flux:icon.magnifying-glass class="size-4 text-terra" />
                    <h2 class="font-semibold text-smoke">{{ __('Find :title by TIP Number', ['title' => $this->workerTitle]) }}</h2>
                </div>
            </div>
            <div class="p-6">
                <div class="flex gap-3">
                    <div class="flex-1">
                        <flux:input
                            wire:model="searchTip"
                            wire:keydown.enter="searchWorker"
                            placeholder="{{ __('Paste TIP number (e.g. TIP-123456)') }}"
                            class="font-mono"
                        />
                    </div>
                    <flux:button wire:click="searchWorker" class="!bg-terra !text-white hover:!bg-terra-dark">
                        <flux:icon.magnifying-glass class="size-4" />
                        {{ __('Search') }}
                    </flux:button>
                    @if ($showSearchResult)
                        <flux:button wire:click="clearSearch" variant="ghost" class="!text-smoke-muted">
                            <flux:icon.x-mark class="size-4" />
                        </flux:button>
                    @endif
                </div>

                {{-- Search Result --}}
                @if ($showSearchResult && $searchResultData)
                    <div class="mt-5 overflow-hidden rounded-2xl border border-terra/20 bg-gradient-to-br from-terra-50/50 to-ivory-light">
                        <div class="p-6">
                            <div class="flex flex-col items-center gap-5 sm:flex-row sm:items-start">
                                {{-- Avatar --}}
                                @if ($searchResultData['avatar'])
                                    <img src="{{ $searchResultData['avatar'] }}" class="size-20 rounded-2xl border-2 border-terra/20 object-cover shadow-sm" />
                                @else
                                    <div class="flex size-20 items-center justify-center rounded-2xl border-2 border-terra/20 bg-terra/10 shadow-sm">
                                        <span class="text-2xl font-bold text-terra">{{ $searchResultData['initials'] }}</span>
                                    </div>
                                @endif

                                {{-- Info --}}
                                <div class="flex-1 text-center sm:text-left">
                                    <h3 class="text-xl font-bold text-smoke">{{ $searchResultData['name'] }}</h3>
                                    <p class="mt-0.5 font-mono text-sm font-semibold text-terra">{{ $searchResultData['global_number'] }}</p>

                                    <div class="mt-3 flex flex-wrap justify-center gap-x-4 gap-y-1 text-sm text-smoke-muted sm:justify-start">
                                        @if ($searchResultData['email'])
                                            <span class="flex items-center gap-1">
                                                <flux:icon.envelope class="size-3.5" />
                                                {{ $searchResultData['email'] }}
                                            </span>
                                        @endif
                                        @if ($searchResultData['phone'])
                                            <span class="flex items-center gap-1">
                                                <flux:icon.phone class="size-3.5" />
                                                {{ $searchResultData['phone'] }}
                                            </span>
                                        @endif
                                        <span class="flex items-center gap-1">
                                            <flux:icon.calendar class="size-3.5" />
                                            {{ __('Joined :date', ['date' => $searchResultData['joined']]) }}
                                        </span>
                                    </div>

                                    {{-- Stats Row --}}
                                    <div class="mt-4 flex flex-wrap justify-center gap-3 sm:justify-start">
                                        <div class="rounded-lg bg-white/80 px-3 py-1.5 text-center shadow-sm">
                                            <p class="text-lg font-bold text-smoke">{{ $searchResultData['total_orders'] }}</p>
                                            <p class="text-xs text-smoke-muted">{{ __('Orders') }}</p>
                                        </div>
                                        <div class="rounded-lg bg-white/80 px-3 py-1.5 text-center shadow-sm">
                                            <p class="text-lg font-bold text-smoke">{{ $searchResultData['total_feedbacks'] }}</p>
                                            <p class="text-xs text-smoke-muted">{{ __('Reviews') }}</p>
                                        </div>
                                        @if ($searchResultData['avg_rating'])
                                            <div class="rounded-lg bg-white/80 px-3 py-1.5 text-center shadow-sm">
                                                <p class="text-lg font-bold text-amber-500">★ {{ $searchResultData['avg_rating'] }}</p>
                                                <p class="text-xs text-smoke-muted">{{ __('Rating') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Action --}}
                            <div class="mt-5 flex flex-wrap items-center justify-center gap-3 border-t border-terra/10 pt-4 sm:justify-end">
                                @if ($searchResultData['is_linked_here'])
                                    <flux:badge color="green" size="sm">
                                        <flux:icon.check-circle class="mr-1 size-3.5" />
                                        {{ __('Already linked to your business') }}
                                    </flux:badge>
                                @elseif ($searchResultData['is_linked_elsewhere'])
                                    <flux:badge color="amber" size="sm">
                                        <flux:icon.exclamation-triangle class="mr-1 size-3.5" />
                                        {{ __('Currently linked to another business') }}
                                    </flux:badge>
                                @else
                                    <flux:button wire:click="linkFromSearch" class="!bg-terra !text-white hover:!bg-terra-dark">
                                        <flux:icon.link class="-ml-1 mr-1 size-4" />
                                        {{ __('Link :title', ['title' => $this->workerTitle]) }}
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                @elseif ($showSearchResult && !$searchResultData)
                    <div class="mt-5 rounded-xl border border-red-100 bg-red-50 p-6 text-center">
                        <flux:icon.x-circle class="mx-auto size-10 text-red-300" />
                        <p class="mt-2 text-sm font-medium text-red-600">{{ __('No worker found') }}</p>
                        <p class="mt-1 text-xs text-red-400">{{ __('Double-check the TIP number and try again.') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Active Workers --}}
        <div class="rounded-2xl border border-ivory-dark/40 bg-white">
            <div class="border-b border-ivory-dark/30 px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="flex size-5 items-center justify-center rounded-full bg-emerald-100 text-xs">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                    </span>
                    <h2 class="font-semibold text-smoke">{{ __('Active :workers', ['workers' => $this->workerTitlePlural]) }}</h2>
                    <span class="rounded-full bg-ivory-light px-2 py-0.5 text-xs font-bold text-smoke-muted">{{ $this->activeLinks->count() }}</span>
                </div>
            </div>

            @if ($this->activeLinks->isEmpty())
                <div class="p-10 text-center">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-ivory-light">
                        <flux:icon.users class="size-6 text-smoke-muted/40" />
                    </div>
                    <p class="mt-3 text-sm font-medium text-smoke-muted">{{ __('No :workers linked yet', ['workers' => strtolower($this->workerTitlePlural)]) }}</p>
                    <p class="mt-1 text-xs text-smoke-muted/70">{{ __('Use the search above or click "Link :title" to add one.', ['title' => $this->workerTitle]) }}</p>
                </div>
            @else
                <div class="divide-y divide-ivory-dark/20">
                    @foreach ($this->activeLinks as $link)
                        <div class="group flex items-center justify-between px-6 py-4 transition hover:bg-ivory-light/50">
                            <button wire:click="viewProfile({{ $link->worker_id }})" class="flex flex-1 items-center gap-4 text-left">
                                <div class="relative">
                                    @if ($link->worker->hasImage())
                                        <img src="{{ $link->worker->imageUrl() }}" class="size-12 rounded-xl border border-ivory-dark/20 object-cover" />
                                    @else
                                        <div class="flex size-12 items-center justify-center rounded-xl bg-terra/10 text-sm font-bold text-terra">
                                            {{ $link->worker->initials() }}
                                        </div>
                                    @endif
                                    <span class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-white bg-emerald-500"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-smoke group-hover:text-terra">{{ $link->worker->name }}</p>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs text-terra/70">{{ $link->worker->global_number }}</span>
                                        @if ($link->worker->phone)
                                            <span class="text-xs text-smoke-muted">· {{ $link->worker->phone }}</span>
                                        @endif
                                    </div>
                                </div>
                            </button>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <flux:badge size="sm" :color="$link->link_type->value === 'permanent' ? 'green' : 'amber'">
                                        {{ $link->link_type->label() }}
                                    </flux:badge>
                                    @if ($link->qr_code)
                                        <p class="mt-1 font-mono text-xs text-terra">{{ $link->qr_code }}</p>
                                    @endif
                                    @if ($link->expires_at)
                                        <p class="mt-1 text-xs text-smoke-muted">{{ __('Exp') }}: {{ $link->expires_at->format('M d, Y') }}</p>
                                    @endif
                                </div>
                                @if ($link->qr_image_path)
                                    <img src="{{ asset('storage/'.$link->qr_image_path) }}" class="hidden h-14 w-14 rounded-xl bg-white p-1 ring-1 ring-ivory-dark/30 sm:block" />
                                @endif
                                <flux:button size="sm" variant="ghost" wire:click="regenerateQrCode({{ $link->id }})" title="{{ __('Refresh QR') }}">
                                    <flux:icon.qr-code class="size-4 text-terra" />
                                </flux:button>
                                <flux:button variant="danger" size="sm" wire:click="unlinkWorker({{ $link->worker_id }})" wire:confirm="{{ __('Are you sure you want to unlink this :title?', ['title' => strtolower($this->workerTitle)]) }}">
                                    {{ __('Unlink') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Past Links --}}
        @if ($this->pastLinks->isNotEmpty())
            <div class="rounded-2xl border border-ivory-dark/40 bg-white">
                <div class="border-b border-ivory-dark/30 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <flux:icon.clock class="size-4 text-smoke-muted" />
                        <h2 class="font-semibold text-smoke">{{ __('Past :workers', ['workers' => $this->workerTitlePlural]) }}</h2>
                    </div>
                </div>
                <div class="divide-y divide-ivory-dark/20">
                    @foreach ($this->pastLinks as $link)
                        <div class="flex items-center justify-between px-6 py-4 opacity-60">
                            <button wire:click="viewProfile({{ $link->worker_id }})" class="flex items-center gap-3 text-left hover:opacity-80">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-smoke/5 text-sm font-bold text-smoke-muted">
                                    {{ $link->worker->initials() }}
                                </div>
                                <div>
                                    <p class="font-medium text-smoke">{{ $link->worker->name }}</p>
                                    <p class="font-mono text-xs text-smoke-muted">{{ $link->worker->global_number }}</p>
                                </div>
                            </button>
                            <p class="text-xs text-smoke-muted">{{ __('Unlinked') }}: {{ $link->unlinked_at?->format('M d, Y') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Link Worker Modal --}}
    <flux:modal wire:model="showLinkModal" class="max-w-md">
        <form wire:submit="linkWorker" class="space-y-6">
            <flux:heading size="lg">{{ __('Link :title', ['title' => $this->workerTitle]) }}</flux:heading>
            <flux:text>{{ __('Enter the worker\'s unique TIP number to link them to your business.') }}</flux:text>

            <flux:input
                wire:model="global_number"
                :label="__('Worker TIP Number')"
                placeholder="TIP-000000"
                required
            />

            <flux:select wire:model.live="link_type" :label="__('Link Type')">
                <flux:select.option value="permanent">{{ __('Permanent') }}</flux:select.option>
                <flux:select.option value="temporary">{{ __('Temporary') }}</flux:select.option>
            </flux:select>

            @if ($link_type === 'temporary')
                <flux:input
                    wire:model="expires_at"
                    :label="__('Expires On')"
                    type="date"
                    :min="now()->addDay()->format('Y-m-d')"
                />
            @endif

            <div class="flex justify-end gap-3">
                <flux:button wire:click="$set('showLinkModal', false)">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" class="!bg-terra !text-white hover:!bg-terra-dark">{{ __('Link :title', ['title' => $this->workerTitle]) }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Worker Profile Modal --}}
    <flux:modal wire:model="showProfileModal" class="w-full max-w-lg">
        @if ($this->profileWorker)
            <div class="space-y-6">
                {{-- Profile Header --}}
                <div class="flex flex-col items-center text-center">
                    @if ($this->profileWorker->hasImage())
                        <img src="{{ $this->profileWorker->imageUrl() }}" class="size-24 rounded-2xl border-2 border-terra/20 object-cover shadow-lg" />
                    @else
                        <div class="flex size-24 items-center justify-center rounded-2xl bg-terra/10 shadow-lg">
                            <span class="text-3xl font-bold text-terra">{{ $this->profileWorker->initials() }}</span>
                        </div>
                    @endif
                    <h2 class="mt-4 text-xl font-bold text-smoke">{{ $this->profileWorker->name }}</h2>
                    <p class="mt-0.5 font-mono text-sm font-semibold text-terra">{{ $this->profileWorker->global_number }}</p>

                    @if ($this->profileStats['link_type'] ?? null)
                        <div class="mt-2">
                            <flux:badge size="sm" :color="$this->profileStats['link_type']->value === 'permanent' ? 'green' : 'amber'">
                                {{ $this->profileStats['link_type']->label() }}
                                @if ($this->profileStats['linked_at'])
                                    · {{ __('since :date', ['date' => $this->profileStats['linked_at']->format('M d, Y')]) }}
                                @endif
                            </flux:badge>
                        </div>
                    @endif
                </div>

                {{-- Contact Info --}}
                <div class="rounded-xl bg-ivory-light p-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="flex items-center gap-2">
                            <flux:icon.envelope class="size-4 text-terra" />
                            <div>
                                <p class="text-xs text-smoke-muted">{{ __('Email') }}</p>
                                <p class="text-sm font-medium text-smoke">{{ $this->profileWorker->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:icon.phone class="size-4 text-terra" />
                            <div>
                                <p class="text-xs text-smoke-muted">{{ __('Phone') }}</p>
                                <p class="text-sm font-medium text-smoke">{{ $this->profileWorker->phone ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl border border-ivory-dark/30 bg-white p-3 text-center">
                        <p class="text-2xl font-bold text-smoke">{{ $this->profileStats['orders_count'] ?? 0 }}</p>
                        <p class="text-xs text-smoke-muted">{{ __('Orders') }}</p>
                    </div>
                    <div class="rounded-xl border border-ivory-dark/30 bg-white p-3 text-center">
                        <p class="text-2xl font-bold text-emerald-600">{{ number_format($this->profileStats['total_earnings'] ?? 0, 0) }}</p>
                        <p class="text-xs text-smoke-muted">{{ __('Earnings (TZS)') }}</p>
                    </div>
                    <div class="rounded-xl border border-ivory-dark/30 bg-white p-3 text-center">
                        <p class="text-2xl font-bold text-smoke">{{ $this->profileStats['feedback_count'] ?? 0 }}</p>
                        <p class="text-xs text-smoke-muted">{{ __('Reviews') }}</p>
                    </div>
                    <div class="rounded-xl border border-ivory-dark/30 bg-white p-3 text-center">
                        @if ($this->profileStats['avg_rating'] ?? null)
                            <p class="text-2xl font-bold text-amber-500">★ {{ $this->profileStats['avg_rating'] }}</p>
                        @else
                            <p class="text-2xl font-bold text-smoke-muted">—</p>
                        @endif
                        <p class="text-xs text-smoke-muted">{{ __('Avg Rating') }}</p>
                    </div>
                </div>

                @if ($this->profileStats['expires_at'] ?? null)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-700">
                        <flux:icon.clock class="mr-1 inline size-4" />
                        {{ __('Link expires on :date', ['date' => $this->profileStats['expires_at']->format('M d, Y')]) }}
                    </div>
                @endif

                @php
                    $activeLink = $this->business?->workerLinks()->where('worker_id', $this->profileWorker->id)->where('is_active', true)->first();
                @endphp
                @if ($activeLink && $activeLink->qr_code)
                    <div class="rounded-2xl border border-ivory-dark/30 bg-ivory-light p-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-smoke-muted">{{ __('Worker WhatsApp Code') }}</p>
                                <p class="mt-2 font-mono text-lg font-semibold text-terra">{{ $activeLink->qr_code }}</p>
                                <p class="mt-1 text-xs text-smoke-muted">{{ __('Scan or share this code so customers can start a worker-specific WhatsApp conversation.') }}</p>
                            </div>
                            @if ($activeLink->qr_image_path)
                                <img src="{{ asset('storage/'.$activeLink->qr_image_path) }}" class="h-28 w-28 rounded-2xl bg-white p-2 ring-1 ring-ivory-dark/30" />
                            @endif
                        </div>
                    </div>
                @endif

                <div class="flex justify-end">
                    <flux:button wire:click="$set('showProfileModal', false)" variant="ghost">{{ __('Close') }}</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>

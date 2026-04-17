<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-ivory text-smoke antialiased">
        <flux:sidebar sticky collapsible class="border-e border-ivory-dark/40 bg-smoke">
            <flux:sidebar.header class="!border-b-ivory-dark/10">
                <flux:sidebar.brand :href="route('dashboard')" logo="/img/logo.svg" :name="config('app.name', 'Tipta')" class="!text-ivory" wire:navigate />
                <flux:sidebar.collapse class="!text-ivory-dark/60 hover:!text-ivory" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard') || request()->routeIs('*.dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                @auth
                    @if (auth()->user()->isAdmin())
                        <flux:separator class="!border-ivory-dark/10" />

                        <flux:sidebar.item icon="building-storefront" :href="route('admin.businesses')" :current="request()->routeIs('admin.businesses')" wire:navigate>
                            {{ __('Businesses') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="user-group" :href="route('admin.managers')" :current="request()->routeIs('admin.managers')" wire:navigate>
                            {{ __('Managers') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="identification" :href="route('admin.workers')" :current="request()->routeIs('admin.workers')" wire:navigate>
                            {{ __('Workers') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.orders')" :current="request()->routeIs('admin.orders')" wire:navigate>
                            {{ __('Orders') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="currency-dollar" :href="route('admin.payments')" :current="request()->routeIs('admin.payments')" wire:navigate>
                            {{ __('Payments') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="banknotes" :href="route('admin.payrolls')" :current="request()->routeIs('admin.payrolls')" wire:navigate>
                            {{ __('Payrolls') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chat-bubble-bottom-center-text" :href="route('admin.feedbacks')" :current="request()->routeIs('admin.feedbacks')" wire:navigate>
                            {{ __('Feedback') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="device-phone-mobile" :href="route('admin.bot-control')" :current="request()->routeIs('admin.bot-control')" wire:navigate>
                            {{ __('Bot Control') }}
                        </flux:sidebar.item>
                    @endif

                    @if (auth()->user()->isManager())
                        @php
                            $managerBusiness = auth()->user()->businesses()->first();
                            $bizType = $managerBusiness?->type;
                        @endphp

                        <flux:separator class="!border-ivory-dark/10" />

                        <flux:sidebar.item icon="users" :href="route('manager.workers.index')" :current="request()->routeIs('manager.workers.*')" wire:navigate>
                            {{ $bizType ? $bizType->workerTitlePlural() : __('Workers') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="table-cells" :href="route('manager.tables.index')" :current="request()->routeIs('manager.tables.*')" wire:navigate>
                            {{ $bizType ? ($bizType->value === 'salon' ? __('Seats') : __('Tables')) : __('Tables') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('manager.menu.index')" :current="request()->routeIs('manager.menu.*')" wire:navigate>
                            {{ $bizType ? ($bizType->value === 'salon' ? __('Service Menu') : __('Menu')) : __('Menu') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="squares-2x2" :href="route('manager.products.index')" :current="request()->routeIs('manager.products.*')" wire:navigate>
                            {{ $bizType ? $bizType->itemLabelPlural() : __('Products') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="currency-dollar" :href="route('manager.tips.index')" :current="request()->routeIs('manager.tips.*')" wire:navigate>
                            {{ __('Tips') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chat-bubble-bottom-center-text" :href="route('manager.feedbacks.index')" :current="request()->routeIs('manager.feedbacks.*')" wire:navigate>
                            {{ __('Feedback') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="phone" :href="route('manager.customer-calls.index')" :current="request()->routeIs('manager.customer-calls.*')" wire:navigate>
                            {{ __('Customer Calls') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="bolt" :href="route('manager.live-orders')" :current="request()->routeIs('manager.live-orders')" wire:navigate>
                            {{ __('Live Orders') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('manager.orders.index')" :current="request()->routeIs('manager.orders.*')" wire:navigate>
                            {{ __('Orders') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="banknotes" :href="route('manager.payrolls.index')" :current="request()->routeIs('manager.payrolls.*')" wire:navigate>
                            {{ __('Payrolls') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="credit-card" :href="route('manager.payment-settings')" :current="request()->routeIs('manager.payment-settings')" wire:navigate>
                            {{ __('Payment Settings') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cog-6-tooth" :href="route('manager.settings')" :current="request()->routeIs('manager.settings')" wire:navigate>
                            {{ __('Settings') }}
                        </flux:sidebar.item>
                    @endif

                    @if (auth()->user()->isWorker())
                        <flux:separator class="!border-ivory-dark/10" />

                        <flux:sidebar.item icon="bolt" :href="route('worker.live-orders')" :current="request()->routeIs('worker.live-orders')" wire:navigate>
                            {{ __('Live Orders') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('worker.orders')" :current="request()->routeIs('worker.orders')" wire:navigate>
                            {{ __('My Orders') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="banknotes" :href="route('worker.payrolls')" :current="request()->routeIs('worker.payrolls')" wire:navigate>
                            {{ __('My Payrolls') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="currency-dollar" :href="route('worker.tips')" :current="request()->routeIs('worker.tips')" wire:navigate>
                            {{ __('My Tips') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="star" :href="route('worker.reviews')" :current="request()->routeIs('worker.reviews')" wire:navigate>
                            {{ __('My Reviews') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="phone" :href="route('worker.customer-calls')" :current="request()->routeIs('worker.customer-calls')" wire:navigate>
                            {{ __('Customer Calls') }}
                        </flux:sidebar.item>
                    @endif
                @endauth
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="max-lg:hidden" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="lg:hidden !bg-smoke !border-b-ivory-dark/10">
            <flux:sidebar.toggle class="lg:hidden !text-ivory" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

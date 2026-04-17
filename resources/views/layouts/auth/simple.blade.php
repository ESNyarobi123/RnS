<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-ivory antialiased">
        <div class="flex min-h-svh">
            {{-- Left: Branding Panel --}}
            <div class="hidden w-1/2 flex-col justify-between bg-smoke p-12 lg:flex">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5" wire:navigate>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-terra text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                            <path d="M11.584 2.376a.75.75 0 0 1 .832 0l9 6a.75.75 0 1 1-.832 1.248L12 3.901 3.416 9.624a.75.75 0 0 1-.832-1.248l9-6Z" />
                            <path fill-rule="evenodd" d="M20.25 10.332v9.918H21a.75.75 0 0 1 0 1.5H3a.75.75 0 0 1 0-1.5h.75v-9.918a.75.75 0 0 1 .634-.74A49.109 49.109 0 0 1 12 9c2.59 0 5.134.202 7.616.592a.75.75 0 0 1 .634.74Zm-7.5 2.418a.75.75 0 0 0-1.5 0v6.75a.75.75 0 0 0 1.5 0v-6.75Zm3-.75a.75.75 0 0 1 .75.75v6.75a.75.75 0 0 1-1.5 0v-6.75a.75.75 0 0 1 .75-.75ZM9 12.75a.75.75 0 0 0-1.5 0v6.75a.75.75 0 0 0 1.5 0v-6.75Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-ivory">{{ config('app.name', 'Tipta') }}</span>
                </a>

                <div class="max-w-md">
                    <h2 class="text-3xl font-bold leading-tight text-ivory">{{ __('Smart business management starts here.') }}</h2>
                    <p class="mt-4 text-ivory-dark/80">{{ __('Manage your restaurant or salon with ease. Track orders, workers, payments, and more — all in one platform.') }}</p>
                </div>

                <p class="text-sm text-ivory-dark/50">&copy; {{ date('Y') }} {{ config('app.name', 'Tipta') }}</p>
            </div>

            {{-- Right: Auth Form --}}
            <div class="flex w-full flex-col items-center justify-center px-6 py-12 lg:w-1/2">
                {{-- Mobile logo --}}
                <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2.5 lg:hidden" wire:navigate>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-terra text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                            <path d="M11.584 2.376a.75.75 0 0 1 .832 0l9 6a.75.75 0 1 1-.832 1.248L12 3.901 3.416 9.624a.75.75 0 0 1-.832-1.248l9-6Z" />
                            <path fill-rule="evenodd" d="M20.25 10.332v9.918H21a.75.75 0 0 1 0 1.5H3a.75.75 0 0 1 0-1.5h.75v-9.918a.75.75 0 0 1 .634-.74A49.109 49.109 0 0 1 12 9c2.59 0 5.134.202 7.616.592a.75.75 0 0 1 .634.74Zm-7.5 2.418a.75.75 0 0 0-1.5 0v6.75a.75.75 0 0 0 1.5 0v-6.75Zm3-.75a.75.75 0 0 1 .75.75v6.75a.75.75 0 0 1-1.5 0v-6.75a.75.75 0 0 1 .75-.75ZM9 12.75a.75.75 0 0 0-1.5 0v6.75a.75.75 0 0 0 1.5 0v-6.75Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-smoke">{{ config('app.name', 'Tipta') }}</span>
                </a>

                <div class="w-full max-w-sm">
                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

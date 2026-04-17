<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Tipta') }} — {{ __('Smart Business Management') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-ivory text-smoke antialiased">
        {{-- Navigation --}}
        <nav class="fixed top-0 z-50 w-full border-b border-ivory-dark/50 bg-ivory/80 backdrop-blur-lg">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-terra text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                            <path d="M11.584 2.376a.75.75 0 0 1 .832 0l9 6a.75.75 0 1 1-.832 1.248L12 3.901 3.416 9.624a.75.75 0 0 1-.832-1.248l9-6Z" />
                            <path fill-rule="evenodd" d="M20.25 10.332v9.918H21a.75.75 0 0 1 0 1.5H3a.75.75 0 0 1 0-1.5h.75v-9.918a.75.75 0 0 1 .634-.74A49.109 49.109 0 0 1 12 9c2.59 0 5.134.202 7.616.592a.75.75 0 0 1 .634.74Zm-7.5 2.418a.75.75 0 0 0-1.5 0v6.75a.75.75 0 0 0 1.5 0v-6.75Zm3-.75a.75.75 0 0 1 .75.75v6.75a.75.75 0 0 1-1.5 0v-6.75a.75.75 0 0 1 .75-.75ZM9 12.75a.75.75 0 0 0-1.5 0v6.75a.75.75 0 0 0 1.5 0v-6.75Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-smoke">{{ config('app.name', 'Tipta') }}</span>
                </a>

                @if (Route::has('login'))
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-terra px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-terra-dark">
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-smoke transition hover:bg-smoke/5">
                                {{ __('Log in') }}
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="rounded-lg bg-terra px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-terra-dark">
                                    {{ __('Get Started') }}
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </nav>

        {{-- Hero Section --}}
        <section class="relative overflow-hidden pt-28 pb-20 lg:pt-40 lg:pb-32">
            <div class="absolute inset-0 -z-10">
                <div class="absolute right-0 top-20 size-96 rounded-full bg-terra/5 blur-3xl"></div>
                <div class="absolute -left-20 bottom-0 size-80 rounded-full bg-terra/8 blur-3xl"></div>
            </div>

            <div class="mx-auto max-w-6xl px-6 text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-terra/20 bg-terra-50 px-4 py-1.5 text-sm font-medium text-terra-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Smart Business Management Platform') }}
                </div>

                <h1 class="mx-auto mt-8 max-w-4xl text-5xl font-bold leading-tight tracking-tight text-smoke lg:text-7xl">
                    {{ __('Manage your') }}
                    <span class="text-terra">{{ __('business') }}</span>
                    {{ __('with ease') }}
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-smoke-muted">
                    {{ __('From restaurants to salons — manage workers, track orders, process payments, and grow your business. All in one beautiful platform.') }}
                </p>

                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    @guest
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl bg-terra px-8 py-4 text-base font-semibold text-white shadow-lg shadow-terra/25 transition hover:bg-terra-dark hover:shadow-xl hover:shadow-terra/30">
                                {{ __('Start Free Today') }}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638l-3.96-4.158a.75.75 0 1 1 1.08-1.04l5.25 5.5a.75.75 0 0 1 0 1.04l-5.25 5.5a.75.75 0 1 1-1.08-1.04l3.96-4.158H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl border-2 border-smoke/15 px-8 py-4 text-base font-semibold text-smoke transition hover:border-smoke/30 hover:bg-smoke/5">
                            {{ __('Sign in') }}
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-terra px-8 py-4 text-base font-semibold text-white shadow-lg shadow-terra/25 transition hover:bg-terra-dark">
                            {{ __('Go to Dashboard') }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638l-3.96-4.158a.75.75 0 1 1 1.08-1.04l5.25 5.5a.75.75 0 0 1 0 1.04l-5.25 5.5a.75.75 0 1 1-1.08-1.04l3.96-4.158H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endguest
                </div>
            </div>
        </section>

        {{-- Features Section --}}
        <section class="border-t border-ivory-dark/50 bg-white py-20 lg:py-28">
            <div class="mx-auto max-w-6xl px-6">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-smoke lg:text-4xl">{{ __('Everything you need to run your business') }}</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-smoke-muted">{{ __('Powerful tools for restaurants and salons, designed to make your life easier.') }}</p>
                </div>

                <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    {{-- Feature 1 --}}
                    <div class="group rounded-2xl border border-ivory-dark/60 bg-ivory-light p-8 transition hover:border-terra/30 hover:shadow-lg hover:shadow-terra/5">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-terra/10 text-terra transition group-hover:bg-terra group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path d="M5.223 2.25c-.497 0-.974.198-1.325.55l-1.3 1.298A3.75 3.75 0 0 0 7.5 9.75c.627.47 1.406.75 2.25.75.844 0 1.624-.28 2.25-.75.626.47 1.406.75 2.25.75.844 0 1.623-.28 2.25-.75a3.75 3.75 0 0 0 4.902-5.652l-1.3-1.299a1.875 1.875 0 0 0-1.325-.549H5.223Z" />
                                <path fill-rule="evenodd" d="M3 20.25v-8.755c1.42.674 3.08.673 4.5 0A5.234 5.234 0 0 0 9.75 12c.804 0 1.568-.182 2.25-.506a5.234 5.234 0 0 0 2.25.506c.804 0 1.567-.182 2.25-.506 1.42.674 3.08.675 4.5.001v8.755h.75a.75.75 0 0 1 0 1.5H2.25a.75.75 0 0 1 0-1.5H3Zm3 0h4.5v-3.375c0-.621-.504-1.125-1.125-1.125h-2.25c-.621 0-1.125.504-1.125 1.125V20.25Zm7.5-6a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-.75.75h-3a.75.75 0 0 1-.75-.75v-3Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-smoke">{{ __('Multi-Business Support') }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-smoke-muted">{{ __('Manage restaurants and salons with role-specific labels — waiters, stylists, menu items, services, and more.') }}</p>
                    </div>

                    {{-- Feature 2 --}}
                    <div class="group rounded-2xl border border-ivory-dark/60 bg-ivory-light p-8 transition hover:border-terra/30 hover:shadow-lg hover:shadow-terra/5">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-terra/10 text-terra transition group-hover:bg-terra group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
                                <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-smoke">{{ __('Worker Management') }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-smoke-muted">{{ __('Link workers via unique TIP numbers, manage schedules, track performance, and handle payroll seamlessly.') }}</p>
                    </div>

                    {{-- Feature 3 --}}
                    <div class="group rounded-2xl border border-ivory-dark/60 bg-ivory-light p-8 transition hover:border-terra/30 hover:shadow-lg hover:shadow-terra/5">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-terra/10 text-terra transition group-hover:bg-terra group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                                <path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75ZM8.25 9.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM18.75 9a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V9.75a.75.75 0 0 0-.75-.75h-.008ZM4.5 9.75A.75.75 0 0 1 5.25 9h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75V9.75Z" clip-rule="evenodd" />
                                <path d="M2.25 18a.75.75 0 0 0 0 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 0 0-.75-.75H2.25Z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-smoke">{{ __('Payments & Payroll') }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-smoke-muted">{{ __('Track payments across multiple methods, manage payroll cycles, and get real-time revenue analytics.') }}</p>
                    </div>

                    {{-- Feature 4 --}}
                    <div class="group rounded-2xl border border-ivory-dark/60 bg-ivory-light p-8 transition hover:border-terra/30 hover:shadow-lg hover:shadow-terra/5">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-terra/10 text-terra transition group-hover:bg-terra group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd" />
                                <path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375ZM6 12a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V12Zm2.25 0a.75.75 0 0 1 .75-.75h3.38a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 15a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V15Zm2.25 0a.75.75 0 0 1 .75-.75h3.38a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 18a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V18Zm2.25 0a.75.75 0 0 1 .75-.75h3.38a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-smoke">{{ __('Order Tracking') }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-smoke-muted">{{ __('Full order lifecycle from pending to completed. Track items, assign workers, and monitor every step.') }}</p>
                    </div>

                    {{-- Feature 5 --}}
                    <div class="group rounded-2xl border border-ivory-dark/60 bg-ivory-light p-8 transition hover:border-terra/30 hover:shadow-lg hover:shadow-terra/5">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-terra/10 text-terra transition group-hover:bg-terra group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-smoke">{{ __('Customer Feedback') }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-smoke-muted">{{ __('Collect ratings and reviews from customers. Monitor satisfaction and improve your service quality.') }}</p>
                    </div>

                    {{-- Feature 6 --}}
                    <div class="group rounded-2xl border border-ivory-dark/60 bg-ivory-light p-8 transition hover:border-terra/30 hover:shadow-lg hover:shadow-terra/5">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-terra/10 text-terra transition group-hover:bg-terra group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z" clip-rule="evenodd" />
                                <path fill-rule="evenodd" d="M12.75 3a.75.75 0 0 1 .75-.75 8.25 8.25 0 0 1 8.25 8.25.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V3Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-smoke">{{ __('Admin Dashboard') }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-smoke-muted">{{ __('Full platform overview with real-time stats, stock alerts, revenue tracking, and quick action links.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Roles Section --}}
        <section class="bg-ivory py-20 lg:py-28">
            <div class="mx-auto max-w-6xl px-6">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-smoke lg:text-4xl">{{ __('Built for every role') }}</h2>
                    <p class="mx-auto mt-4 max-w-xl text-smoke-muted">{{ __('Whether you manage the business, work the floor, or oversee the platform.') }}</p>
                </div>

                <div class="mt-16 grid gap-6 lg:grid-cols-3">
                    <div class="relative overflow-hidden rounded-2xl border border-ivory-dark bg-white p-8">
                        <div class="absolute right-0 top-0 size-32 rounded-bl-full bg-terra/5"></div>
                        <div class="relative">
                            <div class="flex size-14 items-center justify-center rounded-2xl bg-terra text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-7">
                                    <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="mt-6 text-xl font-bold text-smoke">{{ __('Manager') }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-smoke-muted">
                                {{ __('Create your business, add products and categories, link workers, and track orders and revenue from your dashboard.') }}
                            </p>
                            <ul class="mt-6 space-y-2.5 text-sm text-smoke-muted">
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-terra">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Create & manage business profile') }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-terra">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Link workers by TIP number') }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-terra">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Manage products & categories') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="relative overflow-hidden rounded-2xl border border-ivory-dark bg-white p-8">
                        <div class="absolute right-0 top-0 size-32 rounded-bl-full bg-terra/5"></div>
                        <div class="relative">
                            <div class="flex size-14 items-center justify-center rounded-2xl bg-smoke text-ivory">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-7">
                                    <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z" />
                                </svg>
                            </div>
                            <h3 class="mt-6 text-xl font-bold text-smoke">{{ __('Worker') }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-smoke-muted">
                                {{ __('Get your unique TIP number, link to a business, view your daily assignments, and track your orders.') }}
                            </p>
                            <ul class="mt-6 space-y-2.5 text-sm text-smoke-muted">
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-terra">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Unique TIP-XXXXXX number') }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-terra">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Daily order tracking') }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-terra">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Business link management') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="relative overflow-hidden rounded-2xl border border-terra/20 bg-terra-50 p-8">
                        <div class="absolute right-0 top-0 size-32 rounded-bl-full bg-terra/10"></div>
                        <div class="relative">
                            <div class="flex size-14 items-center justify-center rounded-2xl bg-terra text-white shadow-lg shadow-terra/25">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-7">
                                    <path fill-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 0 0-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 0 0-2.282.819l-.922 1.597a1.875 1.875 0 0 0 .432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 0 0 0 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 0 0-.432 2.385l.922 1.597a1.875 1.875 0 0 0 2.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 0 0 2.28-.819l.923-1.597a1.875 1.875 0 0 0-.432-2.385l-.84-.692c-.095-.078-.17-.229-.154-.43a7.614 7.614 0 0 0 0-1.139c-.016-.2.059-.352.153-.43l.84-.692c.708-.582.891-1.59.433-2.385l-.922-1.597a1.875 1.875 0 0 0-2.282-.818l-1.02.382c-.114.043-.282.031-.449-.083a7.49 7.49 0 0 0-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 0 0-1.85-1.567h-1.843ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="mt-6 text-xl font-bold text-smoke">{{ __('Admin') }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-smoke-muted">
                                {{ __('Full platform control — manage all businesses, users, orders, payments, payroll, feedback, and stock alerts.') }}
                            </p>
                            <ul class="mt-6 space-y-2.5 text-sm text-smoke-muted">
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-terra">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Platform-wide analytics') }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-terra">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Manage all businesses & users') }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-terra">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Revenue & stock monitoring') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA Section --}}
        <section class="border-t border-ivory-dark/50 bg-smoke py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-6 text-center">
                <h2 class="text-3xl font-bold tracking-tight text-ivory lg:text-4xl">{{ __('Ready to transform your business?') }}</h2>
                <p class="mx-auto mt-4 max-w-xl text-ivory-dark">{{ __('Join Tipta today and experience smarter business management.') }}</p>
                @guest
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="mt-8 inline-flex items-center gap-2 rounded-xl bg-terra px-8 py-4 text-base font-semibold text-white shadow-lg shadow-terra/30 transition hover:bg-terra-light">
                            {{ __('Create Your Account') }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638l-3.96-4.158a.75.75 0 1 1 1.08-1.04l5.25 5.5a.75.75 0 0 1 0 1.04l-5.25 5.5a.75.75 0 1 1-1.08-1.04l3.96-4.158H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif
                @endguest
            </div>
        </section>

        {{-- Footer --}}
        <footer class="border-t border-ivory-dark/50 bg-ivory py-8">
            <div class="mx-auto max-w-6xl px-6 text-center">
                <p class="text-sm text-smoke-muted">&copy; {{ date('Y') }} {{ config('app.name', 'Tipta') }}. {{ __('All rights reserved.') }}</p>
            </div>
        </footer>
    </body>
</html>

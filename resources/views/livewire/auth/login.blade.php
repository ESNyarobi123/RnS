<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <div class="flex w-full flex-col text-center">
            <h1 class="text-2xl font-bold text-smoke">{{ __('Welcome back') }}</h1>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('Enter your credentials to access your account') }}</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0 !text-terra hover:!text-terra-dark" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                {{ __('Log in') }}
            </flux:button>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-smoke-muted">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link :href="route('register')" class="!text-terra font-medium hover:!text-terra-dark" wire:navigate>{{ __('Sign up') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>

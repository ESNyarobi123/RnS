<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <div class="flex w-full flex-col text-center">
            <h1 class="text-2xl font-bold text-smoke">{{ __('Create your account') }}</h1>
            <p class="mt-1 text-sm text-smoke-muted">{{ __('Get started with Tipta in seconds') }}</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Phone -->
            <flux:input
                name="phone"
                :label="__('Phone number')"
                :value="old('phone')"
                type="tel"
                autocomplete="tel"
                :placeholder="__('e.g. +255 700 000 000')"
            />

            <!-- Role -->
            <flux:select name="role" :label="__('I am a')" required>
                <flux:select.option value="">{{ __('Select your role') }}</flux:select.option>
                <flux:select.option value="manager" :selected="old('role') === 'manager'">{{ __('Business Manager') }}</flux:select.option>
                <flux:select.option value="worker" :selected="old('role') === 'worker'">{{ __('Worker') }}</flux:select.option>
            </flux:select>

            <div class="grid grid-cols-2 gap-4">
                <!-- Password -->
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Password')"
                    viewable
                />

                <!-- Confirm Password -->
                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Confirm')"
                    viewable
                />
            </div>

            <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                {{ __('Create account') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-smoke-muted">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" class="!text-terra font-medium hover:!text-terra-dark" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>

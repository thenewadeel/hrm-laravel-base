<x-guest-layout
    title="Log in"
    description="Log in to your HRM Enterprise ERP account."
>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-heading
            align="center"
            title="{{ __('Welcome back') }}"
            description="{{ __('Log in to continue to the HRM Enterprise ERP suite.') }}"
        />

        <x-validation-errors class="mt-6" />

        @session('status')
            <x-alert type="success" class="mt-6">
                {{ $value }}
            </x-alert>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <x-form.label for="email" value="{{ __('Email') }}" />
                <x-form.input id="email" class="mt-1.5 w-full" type="email" name="email" :value="old('email')"
                    required autofocus autocomplete="username" placeholder="you@company.com" />
                <x-form.input-error for="email" class="mt-1.5" />
            </div>

            <div>
                <x-form.label for="password" value="{{ __('Password') }}" />
                <x-form.input id="password" class="mt-1.5 w-full" type="password" name="password" required
                    autocomplete="current-password" placeholder="{{ __('Enter your password') }}" />
                <x-form.input-error for="password" class="mt-1.5" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="flex items-center">
                    <x-form.checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-secondary">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-secondary underline decoration-secondary underline-offset-2 transition-colors hover:text-primary"
                        href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <div>
                <x-button.primary type="submit" class="w-full">
                    {{ __('Log in') }}
                </x-button.primary>
            </div>
        </form>

        <div class="relative mt-6 flex items-center gap-3">
            <span class="h-px flex-1 bg-border-secondary"></span>
            <span class="text-xs font-medium uppercase tracking-wider text-muted">{{ __('new here') }}</span>
            <span class="h-px flex-1 bg-border-secondary"></span>
        </div>

        <p class="mt-6 text-center text-sm text-secondary">
            {{ __('Don\'t have an account?') }}
            <a href="{{ route('register') }}"
                class="font-semibold text-primary underline decoration-secondary underline-offset-2 transition-colors hover:text-accent">
                {{ __('Create one') }}
            </a>
        </p>
    </x-authentication-card>
</x-guest-layout>
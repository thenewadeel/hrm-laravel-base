<x-guest-layout
    title="Reset your password"
    description="Request a password reset link for the HRM Enterprise ERP suite."
>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-heading
            align="center"
            title="{{ __('Reset your password') }}"
            description="{{ __('Enter the email associated with your account and we\'ll send you a password reset link.') }}"
        />

        @session('status')
            <x-alert type="success" class="mt-6">
                {{ $value }}
            </x-alert>
        @endsession

        <x-validation-errors class="mt-6" />

        <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <x-form.label for="email" value="{{ __('Email') }}" />
                <x-form.input id="email" class="mt-1.5 w-full" type="email" name="email" :value="old('email')"
                    required autofocus autocomplete="username" placeholder="you@company.com" />
                <x-form.input-error for="email" class="mt-1.5" />
            </div>

            <div>
                <x-button.primary type="submit" class="w-full">
                    {{ __('Email Password Reset Link') }}
                </x-button.primary>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-secondary">
            {{ __('Remembered your password?') }}
            <a href="{{ route('login') }}"
                class="font-semibold text-primary underline decoration-secondary underline-offset-2 transition-colors hover:text-accent">
                {{ __('Back to login') }}
            </a>
        </p>
    </x-authentication-card>
</x-guest-layout>
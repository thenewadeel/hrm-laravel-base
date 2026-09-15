<x-guest-layout
    title="Reset your password"
    description="Choose a new password for your HRM Enterprise ERP account."
>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-heading
            align="center"
            title="{{ __('Set a new password') }}"
            description="{{ __('Choose a strong password you haven\'t used before.') }}"
        />

        <x-validation-errors class="mt-6" />

        <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <x-form.label for="email" value="{{ __('Email') }}" />
                <x-form.input id="email" class="mt-1.5 w-full" type="email" name="email"
                    :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-form.input-error for="email" class="mt-1.5" />
            </div>

            <div>
                <x-form.label for="password" value="{{ __('Password') }}" />
                <x-form.input id="password" class="mt-1.5 w-full" type="password" name="password" required
                    autocomplete="new-password" placeholder="{{ __('At least 8 characters') }}" />
                <x-form.input-error for="password" class="mt-1.5" />
            </div>

            <div>
                <x-form.label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-form.input id="password_confirmation" class="mt-1.5 w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password"
                    placeholder="{{ __('Re-enter your password') }}" />
                <x-form.input-error for="password_confirmation" class="mt-1.5" />
            </div>

            <div>
                <x-button.primary type="submit" class="w-full">
                    {{ __('Reset Password') }}
                </x-button.primary>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
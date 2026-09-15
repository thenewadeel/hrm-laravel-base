<x-guest-layout
    title="Confirm your password"
    description="This is a secure area of the HRM Enterprise ERP suite."
>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-heading
            align="center"
            title="{{ __('Confirm your password') }}"
            description="{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}"
        />

        <x-validation-errors class="mt-6" />

        <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <x-form.label for="password" value="{{ __('Password') }}" />
                <x-form.input id="password" class="mt-1.5 w-full" type="password" name="password" required
                    autocomplete="current-password" autofocus placeholder="{{ __('Enter your password') }}" />
                <x-form.input-error for="password" class="mt-1.5" />
            </div>

            <div>
                <x-button.primary type="submit" class="w-full">
                    {{ __('Confirm') }}
                </x-button.primary>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
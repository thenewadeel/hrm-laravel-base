<x-guest-layout
    title="Two-factor authentication"
    description="Verify your identity using a one-time code."
>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-heading
            align="center"
            title="{{ __('Two-factor authentication') }}"
            description="{{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}"
        />

        <div x-data="{ recovery: false }" class="mt-8">
            <div x-show="! recovery" x-cloak>
                <x-alert type="info">
                    {{ __('Please enter the authentication code provided by your authenticator application.') }}
                </x-alert>
            </div>

            <div x-show="recovery" x-cloak>
                <x-alert type="info">
                    {{ __('Please enter one of your emergency recovery codes.') }}
                </x-alert>
            </div>

            <x-validation-errors class="mt-6" />

            <form method="POST" action="{{ route('two-factor.login') }}" class="mt-6 space-y-5">
                @csrf

                <div x-show="! recovery" x-cloak>
                    <x-form.label for="code" value="{{ __('Code') }}" />
                    <x-form.input id="code" class="mt-1.5 w-full" type="text" inputmode="numeric" name="code"
                        autofocus x-ref="code" autocomplete="one-time-code"
                        placeholder="{{ __('6-digit code') }}" />
                    <x-form.input-error for="code" class="mt-1.5" />
                </div>

                <div x-show="recovery" x-cloak>
                    <x-form.label for="recovery_code" value="{{ __('Recovery Code') }}" />
                    <x-form.input id="recovery_code" class="mt-1.5 w-full" type="text" name="recovery_code"
                        x-ref="recovery_code" autocomplete="one-time-code"
                        placeholder="{{ __('Emergency recovery code') }}" />
                    <x-form.input-error for="recovery_code" class="mt-1.5" />
                </div>

                <div class="flex items-center justify-between">
                    <button type="button"
                        class="text-sm font-medium text-secondary underline decoration-secondary underline-offset-2 transition-colors hover:text-primary"
                        x-show="! recovery" x-cloak
                        x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                        {{ __('Use a recovery code') }}
                    </button>

                    <button type="button"
                        class="text-sm font-medium text-secondary underline decoration-secondary underline-offset-2 transition-colors hover:text-primary"
                        x-show="recovery" x-cloak
                        x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                        {{ __('Use an authentication code') }}
                    </button>
                </div>

                <div>
                    <x-button.primary type="submit" class="w-full">
                        {{ __('Log in') }}
                    </x-button.primary>
                </div>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
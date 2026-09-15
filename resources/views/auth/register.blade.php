<x-guest-layout
    title="Create your account"
    description="Start your free trial of the HRM Enterprise ERP suite. Sign up in under a minute."
>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="text-center">
            <h1 class="text-2xl font-bold tracking-tight text-primary">Create your account</h1>
            <p class="mt-2 text-sm text-secondary">Start your free trial of the HRM Enterprise ERP suite.</p>
        </div>

        <x-validation-errors class="mt-6" />

        <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5"
            x-data="{ submitting: false, showPassword: false, showConfirmation: false, passwordMet: false }"
            x-on:submit="submitting = true">
            @csrf

            <div>
                <x-form.label for="name" value="{{ __('Full name') }}" />
                <x-form.input id="name" class="mt-1.5 w-full" type="text" name="name" :value="old('name')"
                    required autofocus autocomplete="name" placeholder="{{ __('Jane Doe') }}" />
                <x-form.input-error for="name" class="mt-1.5" />
            </div>

            <div>
                <x-form.label for="email" value="{{ __('Work email') }}" />
                <x-form.input id="email" class="mt-1.5 w-full" type="email" name="email" :value="old('email')"
                    required autocomplete="username" placeholder="you@company.com" />
                <x-form.input-error for="email" class="mt-1.5" />
            </div>

            <div>
                <x-form.label for="password" value="{{ __('Password') }}" />
                <div class="relative mt-1.5">
                    <x-form.input id="password" class="w-full pr-11" type="password" name="password"
                        x-ref="password" x-bind:type="showPassword ? 'text' : 'password'"
                        x-on:input="passwordMet = $event.target.value.length >= 8" required
                        autocomplete="new-password" placeholder="{{ __('At least 8 characters') }}" />

                    <button type="button"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-muted transition-colors hover:text-primary focus:outline-none"
                        x-on:click="showPassword = !showPassword"
                        x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'">
                        <svg class="size-4" x-show="!showPassword" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg class="size-4" x-show="showPassword" x-cloak viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c6.5 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                            <path d="M6.61 6.61A13.5 13.5 0 0 0 2 12s3.5 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                            <line x1="2" y1="2" x2="22" y2="22" />
                        </svg>
                    </button>
                </div>

                <p class="mt-1.5 text-xs font-medium text-muted" x-bind:class="passwordMet ? 'text-accent' : ''">
                    <svg x-show="passwordMet" x-cloak class="mr-1 inline size-3.5 text-accent" viewBox="0 0 20 20"
                        fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ __('Use at least 8 characters') }}
                </p>

                <x-form.input-error for="password" class="mt-1.5" />
            </div>

            <div>
                <x-form.label for="password_confirmation" value="{{ __('Confirm password') }}" />
                <div class="relative mt-1.5">
                    <x-form.input id="password_confirmation" class="w-full pr-11" type="password"
                        name="password_confirmation" x-ref="password_confirmation"
                        x-bind:type="showConfirmation ? 'text' : 'password'" required
                        autocomplete="new-password" placeholder="{{ __('Re-enter your password') }}" />

                    <button type="button"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-muted transition-colors hover:text-primary focus:outline-none"
                        x-on:click="showConfirmation = !showConfirmation"
                        x-bind:aria-label="showConfirmation ? 'Hide password' : 'Show password'">
                        <svg class="size-4" x-show="!showConfirmation" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg class="size-4" x-show="showConfirmation" x-cloak viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c6.5 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                            <path d="M6.61 6.61A13.5 13.5 0 0 0 2 12s3.5 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                            <line x1="2" y1="2" x2="22" y2="22" />
                        </svg>
                    </button>
                </div>

                <x-form.input-error for="password_confirmation" class="mt-1.5" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div>
                    <label for="terms" class="flex items-start gap-2.5">
                        <x-form.checkbox name="terms" id="terms" required class="mt-0.5" />

                        <span class="text-sm text-secondary">
                            {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' =>
                                    '<a target="_blank" href="' .
                                    route('terms.show') .
                                    '" class="font-medium text-primary underline decoration-secondary underline-offset-2 hover:text-accent">' .
                                    __('Terms of Service') .
                                    '</a>',
                                'privacy_policy' =>
                                    '<a target="_blank" href="' .
                                    route('policy.show') .
                                    '" class="font-medium text-primary underline decoration-secondary underline-offset-2 hover:text-accent">' .
                                    __('Privacy Policy') .
                                    '</a>',
                            ]) !!}
                        </span>
                    </label>
                </div>
            @endif

            <div>
                <x-button.primary type="submit" class="w-full" x-bind:disabled="submitting">
                    <span x-show="!submitting">{{ __('Create account') }}</span>
                    <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                        <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ __('Creating your account…') }}
                    </span>
                </x-button.primary>
            </div>

            <div class="relative flex items-center gap-3">
                <span class="h-px flex-1 bg-border-secondary"></span>
                <span class="text-xs font-medium uppercase tracking-wider text-muted">{{ __('or') }}</span>
                <span class="h-px flex-1 bg-border-secondary"></span>
            </div>

            <p class="text-center text-sm text-secondary">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}"
                    class="font-semibold text-primary underline decoration-secondary underline-offset-2 transition-colors hover:text-accent">
                    {{ __('Log in') }}
                </a>
            </p>
        </form>
    </x-authentication-card>
</x-guest-layout>
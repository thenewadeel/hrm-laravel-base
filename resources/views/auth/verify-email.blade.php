<x-guest-layout
    title="Verify your email"
    description="Verify your email address to continue using the HRM Enterprise ERP suite."
>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-heading
            align="center"
            title="{{ __('Verify your email') }}"
            description="{{ __('Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}"
        />

        @if (session('status') == 'verification-link-sent')
            <x-alert type="success" class="mt-6">
                {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mt-8">
            @csrf

            <div>
                <x-button.primary type="submit" class="w-full">
                    {{ __('Resend Verification Email') }}
                </x-button.primary>
            </div>
        </form>

        <div class="mt-6 flex items-center justify-center gap-6 text-sm">
            <a href="{{ route('profile.show') }}"
                class="font-medium text-primary underline decoration-secondary underline-offset-2 transition-colors hover:text-accent">
                {{ __('Edit Profile') }}
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="font-medium text-secondary underline decoration-secondary underline-offset-2 transition-colors hover:text-primary">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
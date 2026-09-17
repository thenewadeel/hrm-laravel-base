<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            📱 {{ __('Mobile Inventory') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-sm mx-auto px-4">
            <div class="space-y-6">
                <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-primary">{{ __('Mobile Dashboard') }}</h3>
                        <p class="text-sm text-muted mt-2">{{ __('Large touch-friendly controls and simplified data for on-the-go inventory management.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
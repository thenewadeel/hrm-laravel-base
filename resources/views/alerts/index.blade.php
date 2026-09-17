<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            🔔 {{ __('Alerts') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-primary">{{ __('Low Stock Alerts') }}</h3>
                    <p class="text-sm text-muted">{{ __('Inventory items at or below their reorder level.') }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-primary">{{ __('Expiry Alerts') }}</h3>
                    <p class="text-sm text-muted">{{ __('Items approaching their expiry dates.') }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-primary">{{ __('System Notifications') }}</h3>
                    <p class="text-sm text-muted">{{ __('Important system and workflow notifications.') }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-primary">{{ __('Alert Preferences') }}</h3>
                    <p class="text-sm text-muted">{{ __('Configure which alerts you receive and how.') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
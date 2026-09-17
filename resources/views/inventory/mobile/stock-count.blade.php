<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            🔢 {{ __('Mobile Stock Count') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-sm mx-auto px-4">
            <div class="space-y-6">
                <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-primary">{{ __('Barcode-First Counting') }}</h3>
                        <p class="text-sm text-muted mt-2">{{ __('Scan items with a barcode reader and record quantities with large, touch-friendly inputs.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
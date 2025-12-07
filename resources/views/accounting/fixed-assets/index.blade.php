<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    🏗️ Fixed Assets
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Manage your organization's fixed assets and depreciation
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:accounting.fixed-asset-index />
        </div>
    </div>

</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    📅 Create Financial Year
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Set up a new financial year for your organization
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @livewire('accounting.financial-years.financial-year-form')
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-primary leading-tight">
                💰 Fees
            </h2>
            <p class="text-sm text-secondary mt-1">
                Manage member fees and payments
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:membership.fee-manager />
        </div>
    </div>
</x-app-layout>
{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="'Command Center - '.($organization?->name ?? '')"
            description="{{ __('Eagle eye view across finance, people, inventory, and operations.') }}"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-[1400px] space-y-6 px-4 sm:px-6 lg:px-8">
            <livewire:dashboard.executive-dashboard :organization="$organization" />
        </div>
    </div>
</x-app-layout>
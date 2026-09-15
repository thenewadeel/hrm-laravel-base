{{-- resources/views/organizations/index.blade.php --}}

<x-layout>
    {{-- Header Section --}}
    <div class="bg-gradient-to-r from-tertiary to-secondary py-8 shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-primary">
                🏭 {{ __('Organization Management') }}
            </h1>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="bg-bg-primary min-h-screen py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Organization List Component --}}
            <div class="surface mb-8 overflow-hidden rounded-xl p-4 shadow-lg">
                @livewire('organization.organization-list')
            </div>
            <div class="container mx-auto p-4">
                @livewire('organization-tree')
            </div>
            {{-- The Modal Component (no wrapper div needed) --}}
            @livewire('organization.organization-form')
        </div>
    </div>
</x-layout>
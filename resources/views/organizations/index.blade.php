{{-- resources/views/organizations/index.blade.php --}}

<x-layout>
    {{-- Header Section --}}
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 py-8 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">
                🏭 Organization Management
            </h1>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="bg-gray-50 min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Organization List Component --}}
            <div class="p-4 bg-white shadow-lg rounded-xl overflow-hidden mb-8">
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

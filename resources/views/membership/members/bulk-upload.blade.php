<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    📤 Bulk Member Upload
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Upload multiple members at once using CSV files
                </p>
            </div>
            <a href="{{ route('members.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500/50">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                Back to Members
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:membership.bulk-member-upload />
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    ➕ Create Member
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Add a new member to your organization
                </p>
            </div>
            <a href="{{ route('members.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-secondary rounded-md shadow-sm text-sm font-medium text-primary bg-surface hover:bg-tertiary focus:outline-none focus:ring-2 focus:ring-primary/50">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                Back to Members
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:membership.member-form />
        </div>
    </div>
</x-app-layout>
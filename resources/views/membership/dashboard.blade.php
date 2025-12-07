<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    👥 Membership Dashboard
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Overview of membership management and activities
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:membership.membership-dashboard />
        </div>
    </div>
</x-app-layout>
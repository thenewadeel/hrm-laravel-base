<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Badge System Test
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">

                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold mb-4">Basic Badges</h2>
                        <x-new-badge>Default Badge</x-new-badge>
                        <x-new-badge color="green">Green Badge</x-new-badge>
                        <x-new-badge color="red">Red Badge</x-new-badge>
                        <x-new-badge color="blue" variant="outline">Blue Outline</x-new-badge>
                        <x-new-badge color="yellow" variant="subtle">Yellow Subtle</x-new-badge>

                        <h2 class="text-xl font-semibold mb-4 mt-8">Status Badges</h2>
                        <x-new-status-badge status="active" />
                        <x-new-status-badge status="inactive" />
                        <x-new-status-badge status="pending" />
                        <x-new-status-badge status="error" />

                        <h2 class="text-xl font-semibold mb-4 mt-8">Category Badges</h2>
                        <x-new-category-badge category="sales" />
                        <x-new-category-badge category="purchase" />
                        <x-new-category-badge category="high" />

                        <h2 class="text-xl font-semibold mb-4 mt-8">Priority Badges</h2>
                        <x-new-priority-badge priority="low" />
                        <x-new-priority-badge priority="medium" />
                        <x-new-priority-badge priority="high" />
                        <x-new-priority-badge priority="critical" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

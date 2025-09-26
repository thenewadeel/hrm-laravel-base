<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">{{ $organization->name }}</h3>
        <x-badge :color="$organization->is_active ? 'green' : 'gray'">
            {{ $organization->is_active ? 'Active' : 'Inactive' }}
        </x-badge>
    </div>
    <p class="mt-2 text-gray-600">{{ $organization->description }}</p>
    <div class="mt-4 flex space-x-2">
        <x-button.link :href="route('organizations.show', $organization)">View</x-button.link>
        <x-button.link :href="route('organizations.edit', $organization)" variant="outline">Edit</x-button.link>
    </div>
</div>

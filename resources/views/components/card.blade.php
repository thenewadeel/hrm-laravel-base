// resources/views/components/company/card.blade.php
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">{{ $company->name }}</h3>
        <x-badge :color="$company->is_active ? 'green' : 'gray'">
            {{ $company->is_active ? 'Active' : 'Inactive' }}
        </x-badge>
    </div>
    <p class="mt-2 text-gray-600">{{ $company->description }}</p>
    <div class="mt-4 flex space-x-2">
        <x-button.link :href="route('organizations.show', $company)">View</x-button.link>
        <x-button.link :href="route('organizations.edit', $company)" variant="outline">Edit</x-button.link>
    </div>
</div>

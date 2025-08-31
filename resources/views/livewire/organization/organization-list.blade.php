{{-- resources/views/livewire/organization/organization-list.blade.php --}}

<div>
    <!-- Header with Search and Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 space-y-4 sm:space-y-0">
        <h2 class="text-2xl font-semibold text-gray-900">Organizations</h2>

        <div class="flex space-x-4">
            <!-- Search Input -->
            <div class="relative">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search organizations..."
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-c-magnifying-glass class="h-5 w-5 text-gray-400" />

                </div>
            </div>

            <!-- Create Button -->
            <x-button wire:click="$dispatch('openOrganizationModal')">
                <x-heroicon-o-plus class="h-5 w-5 mr-2" />
                New Organization
            </x-button>
        </div>
    </div>

    <!-- Results Count -->
    <div class="mb-4 text-sm text-gray-600">
        Showing {{ $organizations->firstItem() }} to {{ $organizations->lastItem() }} of {{ $organizations->total() }}
        results
    </div>

    <!-- Table -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('name')">
                        Name
                        <x-sort-indicator :field="'name'" :sortField="$sortField" :sortDirection="$sortDirection" />
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Description
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('is_active')">
                        Status
                        <x-sort-indicator :field="'is_active'" :sortField="$sortField" :sortDirection="$sortDirection" />
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($organizations as $organization)
                    <tr wire:key="organization-{{ $organization->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $organization->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 line-clamp-2">{{ $organization->description }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge :color="$organization->is_active ? 'green' : 'gray'">
                                {{ $organization->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <x-button wire:click="$dispatch('editOrganization', {{ $organization }})">
                                Edit
                            </x-button>
                            <x-button wire:click="$dispatch('deleteOrganization', {{ $organization->id }})"
                                variant="danger">
                                Delete
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <x-heroicon-o-building-office-2 class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm font-medium text-gray-900">No organizations found</p>
                            {{-- ← Check this line --}}
                            <p class="text-sm text-gray-500">Get started by creating a new organization.</p>
                            <div class="mt-4">
                                <x-button wire:click="$dispatch('openOrganizationModal')">
                                    Create Organization
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @endforelse


            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($organizations->hasPages())
        <div class="mt-6">
            {{ $organizations->links() }}
        </div>
    @endif

    <!-- Per Page Selector -->
    <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Show</span>
            <select wire:model="perPage" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="text-sm text-gray-600">entries</span>
        </div>
    </div>
</div>

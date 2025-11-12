<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🏪 {{ __('Stores') }} <span class="text-gray-500 text-lg">({{ $stores->total() }} stores)</span>
            </h2>
            <x-button.primary href="{{ route('inventory.stores.create') }}">
                <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                Add Store
            </x-button.primary>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('inventory.stores.index') }}">
                    <div class="bg-white p-4 rounded-lg border border-gray-200">
                        <div class="flex flex-col md:flex-row md:items-end space-y-4 md:space-y-0 md:space-x-4">
                            <!-- Search Input -->
                            <div class="flex-1">
                                <x-form.label for="search" value="Search Stores" />
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <x-heroicon-s-magnifying-glass class="h-4 w-4 text-gray-400" />
                                    </div>
                                    <x-form.input id="search" name="search" type="text"
                                        class="pl-10 block w-full" :value="request('search')"
                                        placeholder="Search by store name, code, or location..." />
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <x-form.label for="filter_status" value="Status" />
                                <x-form.select id="filter_status" name="filter_status"
                                    class="mt-1 block w-full md:w-40">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('filter_status') == 'active' ? 'selected' : '' }}>
                                        Active</option>
                                    <option value="inactive"
                                        {{ request('filter_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </x-form.select>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2">
                                <x-button.primary type="submit">
                                    Apply Filters
                                </x-button.primary>
                                <x-button.secondary href="{{ route('inventory.stores.index') }}">
                                    Clear
                                </x-button.secondary>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Stores Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($stores as $store)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200"
                        title={{ $store }}>
                        <div class="p-6">
                            <!-- Store Header -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <x-heroicon-s-building-storefront class="h-6 w-6 text-blue-600" />
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $store->name }}</h3>
                                        <p class="text-sm text-gray-500 font-mono">{{ $store->code }}</p>
                                    </div>
                                </div>
                                <x-status-badge :status="$store->is_active ? 'active' : 'inactive'" />
                            </div>

                            <!-- Store Details -->
                            <div class="space-y-3 text-sm text-gray-600 mb-4">
                                @if ($store->location)
                                    <div class="flex items-center">
                                        <x-heroicon-s-map-pin class="h-4 w-4 mr-2 text-gray-400" />
                                        <span class="truncate">{{ $store->location }}</span>
                                    </div>
                                @endif

                                @if ($store->organizationUnit)
                                    <div class="flex items-center">
                                        <x-heroicon-s-building-office class="h-4 w-4 mr-2 text-gray-400" />
                                        <span>{{ $store->organizationUnit->name }}</span>
                                    </div>
                                @endif

                                <div class="flex items-center">
                                    <x-heroicon-s-cube class="h-4 w-4 mr-2 text-gray-400" />
                                    <span>{{ $store->items_count ?? 0 }} items</span>
                                </div>

                                @if ($store->description)
                                    <div class="pt-2 border-t border-gray-100">
                                        <p class="text-gray-500 text-xs">{{ Str::limit($store->description, 100) }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Store Metrics -->
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <div class="grid grid-cols-2 gap-4 text-xs">
                                    <div class="text-center">
                                        <div class="font-semibold text-gray-900">Total Value</div>
                                        <div class="text-green-600 font-medium">
                                            ${{ number_format(($store->total_value ?? 0) / 100, 2) }}</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="font-semibold text-gray-900">Low Stock</div>
                                        <div class="text-orange-600 font-medium">{{ $store->low_stock_count ?? 0 }}
                                            items</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-between items-center">
                                <x-button.link href="{{ route('inventory.stores.show', $store) }}"
                                    class="text-blue-600 hover:text-blue-800">
                                    View Details
                                </x-button.link>
                                <div class="flex space-x-2">
                                    <x-button.link href="{{ route('inventory.stores.edit', $store) }}" size="sm">
                                        Edit
                                    </x-button.link>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3">
                        <div class="text-center py-12">
                            <x-heroicon-s-building-storefront class="mx-auto h-16 w-16 text-gray-400" />
                            <h3 class="mt-4 text-lg font-medium text-gray-900">No stores found</h3>
                            <p class="mt-2 text-sm text-gray-500">Get started by creating your first store location.</p>
                            <div class="mt-6">
                                <x-button.primary href="{{ route('inventory.stores.create') }}">
                                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                                    Add Store
                                </x-button.primary>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($stores->hasPages())
                <div class="mt-6">
                    {{ $stores->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

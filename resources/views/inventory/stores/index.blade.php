<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Stores') }}
            </h2>
            <x-button.primary href="{{ route('inventory.stores.create') }}">
                <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                Add Store
            </x-button.primary>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Store Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($stores as $store)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $store->name }}</h3>
                                <x-status-badge :status="$store->is_active ? 'active' : 'inactive'" />
                            </div>
                            
                            <div class="space-y-2 text-sm text-gray-600 mb-4">
                                <div class="flex items-center">
                                    <x-heroicon-s-map-pin class="h-4 w-4 mr-2" />
                                    {{ $store->location ?? 'No location set' }}
                                </div>
                                <div class="flex items-center">
                                    <x-heroicon-s-building-office class="h-4 w-4 mr-2" />
                                    {{ $store->organizationUnit->name ?? 'No unit assigned' }}
                                </div>
                                <div class="flex items-center">
                                    <x-heroicon-s-cube class="h-4 w-4 mr-2" />
                                    {{ $store->items_count }} items
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-900">
                                    Total Value: ${{ number_format($store->total_value / 100, 2) }}
                                </span>
                                <div class="flex space-x-2">
                                    <x-button.link href="{{ route('inventory.stores.show', $store) }}" size="sm">
                                        View
                                    </x-button.link>
                                    <x-button.link href="{{ route('inventory.stores.edit', $store) }}" size="sm">
                                        Edit
                                    </x-button.link>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <x-heroicon-s-building-storefront class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No stores</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first store.</p>
                        <div class="mt-6">
                            <x-button.primary href="{{ route('inventory.stores.create') }}">
                                <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                                Add Store
                            </x-button.primary>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
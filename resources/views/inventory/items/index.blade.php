<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Items') }}
            </h2>
            <x-button.primary href="{{ route('inventory.items.create') }}">
                <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                Add Item
            </x-button.primary>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filters -->
            <div class="mb-6">
                <x-advanced-search 
                    :filters="[
                        'category' => $categories,
                        'status' => ['active' => 'Active', 'inactive' => 'Inactive'],
                        'store' => $stores
                    ]"
                    placeholder="Search items by name, SKU, or description..."
                />
            </div>

            <!-- Items Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-data-table>
                        <x-slot name="header">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </x-slot>
                        
                        <x-slot name="body">
                            @forelse($items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <x-heroicon-s-cube class="h-6 w-6 text-gray-400" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                                <div class="text-sm text-gray-500">{{ Str::limit($item->description, 50) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->sku }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->category }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-inventory.quantity-indicator 
                                            :quantity="$item->total_quantity" 
                                            :reorderLevel="$item->reorder_level" 
                                        />
                                        <div class="text-xs text-gray-500 mt-1">{{ $item->total_quantity }} {{ $item->unit }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($item->selling_price)
                                            ${{ number_format($item->selling_price / 100, 2) }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-status-badge :status="$item->is_active ? 'active' : 'inactive'" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <x-button.link href="{{ route('inventory.items.show', $item) }}" size="sm">
                                                View
                                            </x-button.link>
                                            <x-button.link href="{{ route('inventory.items.edit', $item) }}" size="sm">
                                                Edit
                                            </x-button.link>
                                            <x-button.danger 
                                                onclick="confirm('Are you sure?') && document.getElementById('delete-form-{{ $item->id }}').submit()"
                                                size="sm"
                                            >
                                                Delete
                                            </x-button.danger>
                                            <form id="delete-form-{{ $item->id }}" 
                                                  action="{{ route('inventory.items.destroy', $item) }}" 
                                                  method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No items found. <x-button.link href="{{ route('inventory.items.create') }}">Create your first item</x-button.link>
                                    </td>
                                </tr>
                            @endforelse
                        </x-slot>
                    </x-data-table>

                    <!-- Pagination -->
                    @if($items->hasPages())
                        <div class="mt-4">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
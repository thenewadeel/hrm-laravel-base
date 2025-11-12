<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📦 {{ __('Stock Levels Report') }}
            </h2>
            <div class="flex space-x-2">
                {{-- <x-button.outline href="{{ route('inventory.reports.export', ['type' => 'stock_levels', 'format' => 'pdf']) }}?{{ http_build_query(request()->query()) }}"> --}}
                <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                Export PDF
                {{-- </x-button.outline> --}}
                {{-- <x-button.primary href="{{ route('inventory.reports.export', ['type' => 'stock_levels', 'format' => 'csv']) }}?{{ http_build_query(request()->query()) }}"> --}}
                <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                Export CSV
                {{-- </x-button.primary> --}}
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Report Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('inventory.reports.stock-levels') }}">
                    <div class="bg-white p-4 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <x-form.label for="store_id" value="Store" />
                                <x-form.select id="store_id" name="store_id" class="mt-1 block w-full">
                                    <option value="">All Stores</option>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}"
                                            {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </div>

                            <div>
                                <x-form.label for="category" value="Category" />
                                <x-form.select id="category" name="category" class="mt-1 block w-full">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category }}"
                                            {{ request('category') == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </div>

                            <div>
                                <x-form.label for="status" value="Stock Status" />
                                <x-form.select id="status" name="status" class="mt-1 block w-full">
                                    <option value="">All Status</option>
                                    <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>
                                        Low Stock</option>
                                    <option value="out_of_stock"
                                        {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock
                                    </option>
                                    <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In
                                        Stock</option>
                                </x-form.select>
                            </div>

                            <div class="flex items-end space-x-2">
                                <x-button.primary type="submit" class="w-full">
                                    Apply Filters
                                </x-button.primary>
                                <x-button.secondary href="{{ route('inventory.reports.stock-levels') }}">
                                    Reset
                                </x-button.secondary>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $totalItems }}</div>
                    <div class="text-sm text-gray-500">Total Items</div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $lowStockCount }}</div>
                    <div class="text-sm text-gray-500">Low Stock</div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $outOfStockCount }}</div>
                    <div class="text-sm text-gray-500">Out of Stock</div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
                    <div class="text-2xl font-bold text-green-600">${{ number_format($totalValue / 100, 2) }}</div>
                    <div class="text-sm text-gray-500">Total Value</div>
                </div>
            </div>

            <!-- Stock Levels Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Current Stock Levels</h3>
                        <div class="text-sm text-gray-500">
                            Generated: {{ now()->format('M j, Y g:i A') }}
                        </div>
                    </div>

                    @if ($items->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            SKU</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Category</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Store</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Current Stock</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reorder Level</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Value</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($items as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                        <x-heroicon-s-cube class="h-6 w-6 text-gray-400" />
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <a href="{{ route('inventory.items.show', $item) }}"
                                                                class="hover:text-blue-600">
                                                                {{ $item->name }}
                                                            </a>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ Str::limit($item->description, 30) }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                                {{ $item->sku }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->category ?? 'Uncategorized' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->store->name ?? 'Multiple Stores' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->total_quantity }} {{ $item->unit }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->reorder_level }} {{ $item->unit }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-inventory.quantity-indicator :quantity="$item->total_quantity" :reorderLevel="$item->reorder_level" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                @if ($item->cost_price)
                                                    ${{ number_format(($item->total_quantity * $item->cost_price) / 100, 2) }}
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($items->hasPages())
                            <div class="mt-4">
                                {{ $items->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-s-cube class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No items found</h3>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your filters to see more results.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Low Stock Alerts -->
            @if ($lowStockItems->count() > 0)
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-yellow-400 mr-2" />
                        <h3 class="text-lg font-semibold text-yellow-800">Low Stock Alerts</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($lowStockItems->take(6) as $item)
                            <div class="bg-white rounded-lg p-3 border border-yellow-200">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->sku }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-yellow-600 font-bold">{{ $item->total_quantity }}</div>
                                        <div class="text-xs text-gray-500">of {{ $item->reorder_level }}</div>
                                    </div>
                                </div>
                                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full"
                                        style="width: {{ min(100, ($item->total_quantity / $item->reorder_level) * 100) }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($lowStockItems->count() > 6)
                        <div class="mt-4 text-center">
                            <x-button.link href="{{ route('inventory.reports.low-stock') }}" class="text-yellow-700">
                                View all {{ $lowStockItems->count() }} low stock items →
                            </x-button.link>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

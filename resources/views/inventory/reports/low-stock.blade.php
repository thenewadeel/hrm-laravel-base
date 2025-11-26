<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ⚠️ {{ __('Low Stock Report') }}
            </h2>
            <div class="flex space-x-2">
                <x-button.outline href="{{ route('inventory.reports.download.low-stock') }}?{{ http_build_query(request()->query()) }}">
                <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                Export PDF
                </x-button.outline>
                <x-button.primary href="{{ route('inventory.items.create') }}">
                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                    Add Item
                </x-button.primary>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Critical Alerts -->
            @if ($outOfStockItems->count() > 0)
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-red-400 mr-2" />
                        <h3 class="text-lg font-semibold text-red-800">🚨 Critical: Out of Stock Items</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($outOfStockItems->take(6) as $item)
                            <div class="bg-white rounded-lg p-4 border border-red-300">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->sku }}</div>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        OUT OF STOCK
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Current Stock:</span>
                                        <span class="font-medium text-red-600">0 {{ $item->unit }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Reorder Level:</span>
                                        <span class="font-medium">{{ $item->reorder_level }} {{ $item->unit }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 flex space-x-2">
                                    <x-button.link
                                        href="{{ route('inventory.transactions.create') }}?type=receipt&item_id={{ $item->id }}"
                                        size="sm">
                                        Receive Stock
                                    </x-button.link>
                                    <x-button.link href="{{ route('inventory.items.edit', $item) }}" size="sm">
                                        Edit Item
                                    </x-button.link>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($outOfStockItems->count() > 6)
                        <div class="mt-4 text-center">
                            <span class="text-red-700 font-medium">+{{ $outOfStockItems->count() - 6 }} more out of
                                stock items</span>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Report Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('inventory.reports.low-stock') }}">
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
                                <x-form.label for="severity" value="Severity" />
                                <x-form.select id="severity" name="severity" class="mt-1 block w-full">
                                    <option value="">All Severity</option>
                                    <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>
                                        Critical (Out of Stock)</option>
                                    <option value="warning" {{ request('severity') == 'warning' ? 'selected' : '' }}>
                                        Warning (Low Stock)</option>
                                    <option value="info" {{ request('severity') == 'info' ? 'selected' : '' }}>Info
                                        (Near Reorder)</option>
                                </x-form.select>
                            </div>

                            <div class="flex items-end space-x-2">
                                <x-button.primary type="submit" class="w-full">
                                    Apply Filters
                                </x-button.primary>
                                <x-button.secondary href="{{ route('inventory.reports.low-stock') }}">
                                    Reset
                                </x-button.secondary>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-red-50 p-4 rounded-lg border border-red-200 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $outOfStockItems->count() }}</div>
                    <div class="text-sm text-red-700">Out of Stock</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $lowStockItems->count() }}</div>
                    <div class="text-sm text-yellow-700">Low Stock</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg border border-orange-200 text-center">
                    <div class="text-2xl font-bold text-orange-600">
                        {{ $outOfStockItems->count() + $lowStockItems->count() }}</div>
                    <div class="text-sm text-orange-700">Total Alerts</div>
                </div>
            </div>

            <!-- Low Stock Items -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">⚠️ Low Stock Items</h3>
                        <div class="text-sm text-gray-500">
                            {{ $lowStockItems->count() }} items below reorder level
                        </div>
                    </div>

                    @if ($lowStockItems->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Current Stock</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reorder Level</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Deficit</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Store</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($lowStockItems as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="flex-shrink-0 h-10 w-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                                        <x-heroicon-s-exclamation-triangle
                                                            class="h-6 w-6 text-yellow-600" />
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <a href="{{ route('inventory.items.show', $item) }}"
                                                                class="hover:text-blue-600">
                                                                {{ $item->name }}
                                                            </a>
                                                        </div>
                                                        <div class="text-sm text-gray-500">{{ $item->sku }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-yellow-600">
                                                {{ $item->total_quantity }} {{ $item->unit }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->reorder_level }} {{ $item->unit }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">
                                                -{{ max(0, $item->reorder_level - $item->total_quantity) }}
                                                {{ $item->unit }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->store->name ?? 'Multiple' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-inventory.quantity-indicator :quantity="$item->total_quantity" :reorderLevel="$item->reorder_level" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <x-button.link
                                                        href="{{ route('inventory.transactions.create') }}?type=receipt&item_id={{ $item->id }}"
                                                        size="sm">
                                                        Receive
                                                    </x-button.link>
                                                    <x-button.link href="{{ route('inventory.items.edit', $item) }}"
                                                        size="sm">
                                                        Edit
                                                    </x-button.link>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-s-check-circle class="mx-auto h-12 w-12 text-green-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No low stock items</h3>
                            <p class="mt-1 text-sm text-gray-500">All items are above their reorder levels.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Reorder Suggestions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <x-heroicon-s-light-bulb class="h-5 w-5 text-blue-400 mr-2" />
                    <h3 class="text-lg font-semibold text-blue-800">Reorder Suggestions</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($reorderSuggestions as $suggestion)
                        <div class="bg-white rounded-lg p-4 border border-blue-200">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $suggestion['item']->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $suggestion['item']->sku }}</div>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Suggested
                                </span>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Current Stock:</span>
                                    <span class="font-medium">{{ $suggestion['current'] }}
                                        {{ $suggestion['item']->unit }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Reorder Level:</span>
                                    <span class="font-medium">{{ $suggestion['reorder_level'] }}
                                        {{ $suggestion['item']->unit }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Suggested Order:</span>
                                    <span class="font-bold text-blue-600">{{ $suggestion['suggested_order'] }}
                                        {{ $suggestion['item']->unit }}</span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <x-button.primary
                                    href="{{ route('inventory.transactions.create') }}?type=receipt&item_id={{ $suggestion['item']->id }}"
                                    size="sm" class="w-full">
                                    Order {{ $suggestion['suggested_order'] }} {{ $suggestion['item']->unit }}
                                </x-button.primary>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if (empty($reorderSuggestions))
                    <p class="text-blue-700 text-center">No reorder suggestions at this time.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    {{--
- Store summary (total items, total value)
- Low stock items in this store
- Recent transactions for this store
- Stock level chart
- Quick item management

    --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🏪 {{ $store->name }} <span class="text-gray-500 font-mono text-lg">({{ $store->code }})</span>
            </h2>
            <div class="flex space-x-2">
                <x-button.secondary href="{{ route('inventory.stores.edit', $store) }}">
                    <x-heroicon-s-pencil class="w-4 h-4 mr-2" />
                    Edit Store
                </x-button.secondary>
                <x-button.primary href="{{ route('inventory.transactions.create') }}?store_id={{ $store->id }}">
                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                    New Transaction
                </x-button.primary>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Store Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <x-inventory.stock-card title="Total Items" value="{{ $store->items_count ?? 0 }}" trend="+8%"
                    trendColor="bg-green-100 text-green-800" description="Items in this store" icon="📦" />

                <x-inventory.stock-card title="Low Stock" value="{{ $lowStockCount ?? 0 }}" trend="+2%"
                    trendColor="bg-yellow-100 text-yellow-800" description="Below reorder level" icon="⚠️" />

                <x-inventory.stock-card title="Out of Stock" value="{{ $outOfStockCount ?? 0 }}" trend="-1%"
                    trendColor="bg-red-100 text-red-800" description="Need restocking" icon="❌" />

                <x-inventory.stock-card title="Total Value"
                    value="${{ number_format(($store->total_value ?? 0) / 100, 2) }}" trend="+12%"
                    trendColor="bg-blue-100 text-blue-800" description="Store inventory value" icon="💰" />
            </div>

            <!-- Store Details & Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Store Information -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">📋 Store Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Store Code</label>
                                        <p class="mt-1 text-sm text-gray-900 font-mono">{{ $store->code }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Location</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ $store->location ?? 'No location specified' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Status</label>
                                        <div class="mt-1">
                                            <x-status-badge :status="$store->is_active ? 'active' : 'inactive'" />
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Organization Unit</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ $store->organizationUnit->name ?? 'Not assigned' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Created</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ $store->created_at->format('M j, Y') }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Last Updated</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ $store->updated_at->format('M j, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if ($store->description)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <label class="text-sm font-medium text-gray-500">Description</label>
                                    <p class="mt-2 text-sm text-gray-700">{{ $store->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">🎯 Quick Actions</h3>
                            <div class="space-y-3">
                                <x-button.primary
                                    href="{{ route('inventory.transactions.create') }}?type=receipt&store_id={{ $store->id }}"
                                    class="w-full justify-center">
                                    <x-heroicon-s-arrow-down-tray class="w-4 h-4 mr-2" />
                                    Receive Stock
                                </x-button.primary>

                                <x-button.secondary
                                    href="{{ route('inventory.transactions.create') }}?type=issue&store_id={{ $store->id }}"
                                    class="w-full justify-center">
                                    <x-heroicon-s-arrow-up-tray class="w-4 h-4 mr-2" />
                                    Issue Items
                                </x-button.secondary>

                                <x-button.outline
                                    href="{{ route('inventory.transactions.create') }}?type=transfer&from_store_id={{ $store->id }}"
                                    class="w-full justify-center">
                                    {{-- <x-heroicon-s-arrow-right-left class="w-4 h-4 mr-2" /> --}}
                                    Transfer Items
                                </x-button.outline>

                                <x-button.outline
                                    href="{{ route('inventory.stock.count') }}?store_id={{ $store->id }}"
                                    class="w-full justify-center">
                                    <x-heroicon-s-clipboard-document-list class="w-4 h-4 mr-2" />
                                    Stock Count
                                </x-button.outline>
                            </div>
                        </div>
                    </div>

                    <!-- Store Statistics -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">📊 Store Statistics</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Total Items</span>
                                    <span class="font-medium">{{ $store->items_count ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Total Value</span>
                                    <span
                                        class="font-medium">${{ number_format(($store->total_value ?? 0) / 100, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Active Items</span>
                                    <span class="font-medium text-green-600">{{ $activeItemsCount ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Inactive Items</span>
                                    <span class="font-medium text-gray-600">{{ $inactiveItemsCount ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Low Stock Items -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Transactions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Recent Transactions</h3>
                            <x-button.link
                                href="{{ route('inventory.transactions.index') }}?store_id={{ $store->id }}"
                                size="sm">
                                View All
                            </x-button.link>
                        </div>
                        <div class="space-y-4">
                            {{-- @forelse($recentTransactions as $transaction)
                                <div class="flex items-center justify-between py-3 border-b border-gray-100"
                                    title="{{ json_encode($transaction) }}">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $transaction->reference ?? 'no ref' }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $transaction->type ?? 'no type' }} •
                                            {{ $transaction->items_count ?? 'unknown' }} items
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <x-status-badge :status="$transaction->status ?? 'nil'" />
                                        <p class="text-sm text-gray-500">
                                            {{ $transaction->created_at->diffForHumans() ?? 'unknown time' }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <x-heroicon-s-document-text class="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions</h3>
                                    <p class="mt-1 text-sm text-gray-500">This store has no transaction history yet.
                                    </p>
                                </div>
                            @endforelse --}}
                        </div>
                    </div>
                </div>

                <!-- Low Stock Items -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">⚠️ Low Stock Items</h3>
                            <x-button.link
                                href="{{ route('inventory.reports.low-stock') }}?store_id={{ $store->id }}"
                                size="sm">
                                View All
                            </x-button.link>
                        </div>
                        <div class="space-y-3" title="{{ json_encode($lowStockItems) }}">
                            @forelse($lowStockItems as $item)
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <div class="flex items-center">
                                        <x-heroicon-s-cube class="h-4 w-4 text-gray-400 mr-2" />
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $item->name ?? 'no item name' }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->sku ?? 'no SKU' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <x-inventory.quantity-indicator :quantity="$item->pivot->quantity ?? ($item->quantity ?? 0)" :reorderLevel="$item->reorder_level ?? 0" />
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $item->pivot->quantity ?? $item->quantity }} /
                                            {{ $item->reorder_level }} {{ $item->unit }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <x-heroicon-s-check-circle class="mx-auto h-12 w-12 text-green-400" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">All items in stock</h3>
                                    <p class="mt-1 text-sm text-gray-500">No low stock items in this store.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

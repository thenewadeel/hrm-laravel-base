<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inventory Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <x-inventory.stock-card title="Total Items" value="{{ $totalItems }}" trend="+12%"
                    trendColor="bg-green-100 text-green-800" description="Across all stores" />

                <x-inventory.stock-card title="Low Stock Items" value="{{ $lowStockItems }}" trend="+5%"
                    trendColor="bg-yellow-100 text-yellow-800" description="Need attention" />

                <x-inventory.stock-card title="Out of Stock" value="{{ $outOfStockItems }}" trend="-2%"
                    trendColor="bg-red-100 text-red-800" description="Requires restocking" />

                <x-inventory.stock-card title="Total Value" value="{{ number_format($totalValue, 2) }}" unit="USD"
                    trend="+8%" trendColor="bg-blue-100 text-blue-800" description="Inventory worth" />
            </div>

            <!-- Alerts Section -->
            <div class="mb-8">
                @if ($lowStockItems > 0)
                    <x-inventory.low-stock-alert level="warning" :items="$lowStockItems">
                        {{ $lowStockItems }} items are below reorder level and need attention.
                    </x-inventory.low-stock-alert>
                @endif

                @if ($outOfStockItems > 0)
                    <x-inventory.low-stock-alert level="danger" :items="$outOfStockItems">
                        {{ $outOfStockItems }} items are out of stock and require immediate restocking.
                    </x-inventory.low-stock-alert>
                @endif
            </div>

            <!-- Recent Activity & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Transactions -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Recent Transactions</h3>
                                <x-button.primary href="{{ route('transactions.index') }}" size="sm">
                                    View All
                                </x-button.primary>
                            </div>
                            <div class="space-y-4">
                                @forelse($recentTransactions as $transaction)
                                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $transaction->reference }}</p>
                                            <p class="text-sm text-gray-500">{{ $transaction->type }} •
                                                {{ $transaction->store->name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <x-status-badge :status="$transaction->status" />
                                            <p class="text-sm text-gray-500">
                                                {{ $transaction->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No recent transactions</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <x-button.primary href="{{ route('items.create') }}" class="w-full justify-center">
                                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                                    Add New Item
                                </x-button.primary>

                                <x-button.secondary href="{{ route('transactions.create') }}"
                                    class="w-full justify-center">
                                    <x-heroicon-s-arrow-path class="w-4 h-4 mr-2" />
                                    New Transaction
                                </x-button.secondary>

                                <x-button.outline href="{{ route('items.low-stock') }}" class="w-full justify-center">
                                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 mr-2" />
                                    View Low Stock
                                </x-button.outline>
                            </div>
                        </div>
                    </div>

                    <!-- Store Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Store Summary</h3>
                            <div class="space-y-3">
                                @foreach ($storeSummary as $store)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="font-medium">{{ $store->name }}</span>
                                        <span class="text-sm text-gray-500">{{ $store->items_count }} items</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

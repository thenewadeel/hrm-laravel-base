<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏠 {{ __('Inventory Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <x-inventory.stock-card title="Total Items" value="{{ $totalItems ?? 0 }}" trend="+12%"
                    trendColor="bg-green-100 text-green-800" description="Across all stores" icon="📦" />

                <x-inventory.stock-card title="Low Stock Items" value="{{ $lowStockItems ?? 0 }}" trend="+5%"
                    trendColor="bg-yellow-100 text-yellow-800" description="Need attention" icon="⚠️" />

                <x-inventory.stock-card title="Out of Stock" value="{{ $outOfStockItems ?? 0 }}" trend="-2%"
                    trendColor="bg-red-100 text-red-800" description="Requires restocking" icon="❌" />

                <x-inventory.stock-card title="Total Value" value="${{ number_format(($totalValue ?? 0) / 100, 2) }}"
                    trend="+8%" trendColor="bg-blue-100 text-blue-800" description="Inventory worth"
                    icon="💰" />
            </div>

            <!-- Alerts Section -->
            <div class="mb-8">
                @if ($lowStockItems->count() > 0)
                    <x-inventory.low-stock-alert level="warning" :items="$lowStockItems">
                        {{ $lowStockItems }} items are below reorder level and need attention.
                        <a href="{{ route('inventory.reports.low-stock') }}" class="font-medium underline">View low
                            stock report</a>
                    </x-inventory.low-stock-alert>
                @endif

                @if (($outOfStockItems ?? 0) > 0)
                    <x-inventory.low-stock-alert level="danger" :items="$outOfStockItems">
                        {{ $outOfStockItems }} items are out of stock and require immediate restocking.
                        <a href="{{ route('inventory.reports.low-stock') }}" class="font-medium underline">View out of
                            stock items</a>
                    </x-inventory.low-stock-alert>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <x-button.link href="{{ route('inventory.items.create') }}"
                    class="flex flex-col items-center justify-center py-4">
                    <span class="text-2xl mb-2">📦</span>
                    <span class="text-sm">Add Item</span>
                </x-button.link>

                <x-button.secondary href="{{ route('inventory.transactions.create') }}?type=receipt"
                    class="flex flex-col items-center justify-center py-4">
                    <span class="text-2xl mb-2">📥</span>
                    <span class="text-sm">Receive Stock</span>
                </x-button.secondary>

                <x-button.secondary href="{{ route('inventory.transactions.create') }}?type=issue"
                    class="flex flex-col items-center justify-center py-4">
                    <span class="text-2xl mb-2">📤</span>
                    <span class="text-sm">Issue Items</span>
                </x-button.secondary>

                <x-button.outline href="{{ route('inventory.reports.stock-levels') }}"
                    class="flex flex-col items-center justify-center py-4">
                    <span class="text-2xl mb-2">📊</span>
                    <span class="text-sm">View Reports</span>
                </x-button.outline>
            </div>

            <!-- Recent Activity & Store Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Transactions -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Recent Transactions</h3>
                                <x-button.primary href="{{ route('inventory.transactions.index') }}" size="sm">
                                    View All
                                </x-button.primary>
                            </div>
                            <div class="space-y-4">
                                @forelse($recentTransactions ?? [] as $transaction)
                                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $transaction->reference }}</p>
                                            <p class="text-sm text-gray-500">
                                                {{ $transaction->type }} •
                                                {{ $transaction->store->name ?? 'Unknown Store' }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <x-status-badge :status="$transaction->status" />
                                            <p class="text-sm text-gray-500">
                                                {{ $transaction->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <x-heroicon-s-document-text class="mx-auto h-12 w-12 text-gray-400" />
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions</h3>
                                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first
                                            transaction.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Store Summary & Quick Actions -->
                <div class="space-y-6">
                    <!-- Store Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Store Summary</h3>
                            <div class="space-y-3">
                                @forelse($storeSummary ?? [] as $store)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <div>
                                            <span class="font-medium text-gray-900">{{ $store->name }}</span>
                                            <p class="text-xs text-gray-500">{{ $store->location }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-medium text-gray-900">{{ $store->items_count }}
                                                items</span>
                                            <p class="text-xs text-gray-500">
                                                ${{ number_format($store->total_value / 100, 2) }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No stores configured</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Quick Reports -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Quick Reports</h3>
                            <div class="space-y-2">
                                <x-button.link href="{{ route('inventory.reports.low-stock') }}"
                                    class="w-full justify-start">
                                    ⚠️ Low Stock Report
                                </x-button.link>
                                <x-button.link href="{{ route('inventory.reports.movement') }}"
                                    class="w-full justify-start">
                                    📈 Movement Report
                                </x-button.link>
                                <x-button.link href="{{ route('inventory.reports.stock-levels') }}"
                                    class="w-full justify-start">
                                    📊 Stock Levels
                                </x-button.link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-primary leading-tight">
                📊 {{ __('Stock Management') }}
            </h2>
            <div class="flex space-x-3">
                <x-button.outline href="{{ route('inventory.reports.index') }}">
                    <x-heroicon-s-chart-bar class="w-4 h-4 mr-2" />
                    Reports
                </x-button.outline>
                <x-button.outline href="{{ route('inventory.index') }}">
                    <x-heroicon-s-arrow-left class="w-4 h-4 mr-2" />
                    Inventory Dashboard
                </x-button.outline>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <x-inventory.stock-card 
                    title="Total Stock Value" 
                    :value="'$' . number_format(($totalStockValue ?? 0) / 100, 2)" 
                    trend="+12%" 
                    trendColor="bg-green-100 text-green-800" 
                    description="Across all stores" 
                    icon="💰" 
                />

                <x-inventory.stock-card 
                    title="Pending Adjustments" 
                    :value="$pendingAdjustments ?? 0" 
                    trend="-5%" 
                    trendColor="bg-blue-100 text-blue-800" 
                    description="Awaiting approval" 
                    icon="⚙️" 
                />

                <x-inventory.stock-card 
                    title="Active Transfers" 
                    :value="$activeTransfers ?? 0" 
                    trend="+3%" 
                    trendColor="bg-yellow-100 text-yellow-800" 
                    description="In transit" 
                    icon="🚚" 
                />

                <x-inventory.stock-card 
                    title="Stock Counts This Month" 
                    :value="$monthlyCounts ?? 0" 
                    trend="+15%" 
                    trendColor="bg-purple-100 text-purple-800" 
                    description="Completed counts" 
                    icon="📋" 
                />
            </div>

            <!-- Alerts Section -->
            <div class="mb-8">
                @if (($criticalStockItems ?? 0) > 0)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="flex">
                            <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-red-400 mr-2" />
                            <div>
                                <h4 class="font-medium text-red-900">Critical Stock Levels</h4>
                                <p class="text-sm text-red-700 mt-1">
                                    {{ $criticalStockItems }} items are critically low and require immediate attention.
                                    <a href="{{ route('inventory.reports.low-stock') }}" class="font-medium underline">View critical stock report</a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (($pendingApprovals ?? 0) > 0)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <x-heroicon-s-clock class="h-5 w-5 text-yellow-400 mr-2" />
                            <div>
                                <h4 class="font-medium text-yellow-900">Pending Approvals</h4>
                                <p class="text-sm text-yellow-700 mt-1">
                                    {{ $pendingApprovals }} stock operations require your approval.
                                    <a href="#" class="font-medium underline">Review pending approvals</a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <x-button.link href="{{ route('inventory.stock.adjustment') }}" class="flex flex-col items-center justify-center py-4">
                    <span class="text-2xl mb-2">⚙️</span>
                    <span class="text-sm">Stock Adjustment</span>
                </x-button.link>

                <x-button.link href="{{ route('inventory.stock.count') }}" class="flex flex-col items-center justify-center py-4">
                    <span class="text-2xl mb-2">📋</span>
                    <span class="text-sm">Stock Count</span>
                </x-button.link>

                <x-button.link href="{{ route('inventory.stock.transfer') }}" class="flex flex-col items-center justify-center py-4">
                    <span class="text-2xl mb-2">🚚</span>
                    <span class="text-sm">Stock Transfer</span>
                </x-button.link>

                <x-button.outline href="{{ route('inventory.reports.stock-levels') }}" class="flex flex-col items-center justify-center py-4">
                    <span class="text-2xl mb-2">📊</span>
                    <span class="text-sm">Stock Reports</span>
                </x-button.outline>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Activities (2/3 width) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Recent Stock Adjustments -->
                    <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 surface border-b border-secondary">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-primary">Recent Stock Adjustments</h3>
                                <x-button.primary href="{{ route('inventory.stock.adjustment') }}" size="sm">
                                    New Adjustment
                                </x-button.primary>
                            </div>
                            <div class="space-y-4">
                                @forelse($recentAdjustments ?? [] as $adjustment)
                                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <p class="font-medium text-gray-900">{{ $adjustment->reference }}</p>
                                                <x-badge :status="$adjustment->status" class="ml-2" />
                                            </div>
                                            <p class="text-sm text-gray-500">
                                                {{ $adjustment->store->name ?? 'Unknown Store' }} •
                                                {{ $adjustment->adjustment_type }} •
                                                {{ $adjustment->items_count }} items •
                                                {{ $adjustment->total_variance ?? 0 }} units variance
                                            </p>
                                            @if($adjustment->notes)
                                                <p class="text-xs text-gray-400 mt-1">{{ Str::limit($adjustment->notes, 80) }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="text-sm text-gray-500">{{ $adjustment->created_at->diffForHumans() }}</p>
                                            <div class="flex space-x-2 mt-1">
                                                <x-button.link href="#" size="xs">View</x-button.link>
                                                @if($adjustment->status === 'pending')
                                                    <x-button.secondary href="#" size="xs">Approve</x-button.secondary>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <x-heroicon-s-cog class="mx-auto h-12 w-12 text-gray-400" />
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No recent adjustments</h3>
                                        <p class="mt-1 text-sm text-gray-500">Stock adjustments will appear here once created.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Recent Stock Transfers -->
                    <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 surface border-b border-secondary">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-primary">Recent Stock Transfers</h3>
                                <x-button.primary href="{{ route('inventory.stock.transfer') }}" size="sm">
                                    New Transfer
                                </x-button.primary>
                            </div>
                            <div class="space-y-4">
                                @forelse($recentTransfers ?? [] as $transfer)
                                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <p class="font-medium text-gray-900">{{ $transfer->reference }}</p>
                                                <x-badge :status="$transfer->status" class="ml-2" />
                                            </div>
                                            <p class="text-sm text-gray-500">
                                                {{ $transfer->fromStore->name ?? 'Unknown' }} → {{ $transfer->toStore->name ?? 'Unknown' }} •
                                                {{ $transfer->items_count }} items •
                                                ${{ number_format($transfer->total_value / 100, 2) }}
                                            </p>
                                            @if($transfer->expected_delivery_date)
                                                <p class="text-xs text-gray-400 mt-1">
                                                    Expected: {{ $transfer->expected_delivery_date->format('M j, Y') }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="text-sm text-gray-500">{{ $transfer->created_at->diffForHumans() }}</p>
                                            <div class="flex space-x-2 mt-1">
                                                <x-button.link href="#" size="xs">Track</x-button.link>
                                                @if($transfer->status === 'pending')
                                                    <x-button.secondary href="#" size="xs">Approve</x-button.secondary>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <x-heroicon-s-truck class="mx-auto h-12 w-12 text-gray-400" />
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No recent transfers</h3>
                                        <p class="mt-1 text-sm text-gray-500">Stock transfers will appear here once created.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Recent Stock Counts -->
                    <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 surface border-b border-secondary">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-primary">Recent Stock Counts</h3>
                                <x-button.primary href="{{ route('inventory.stock.count') }}" size="sm">
                                    New Count
                                </x-button.primary>
                            </div>
                            <div class="space-y-4">
                                @forelse($recentCounts ?? [] as $count)
                                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <p class="font-medium text-gray-900">{{ $count->reference }}</p>
                                                <x-badge :status="$count->status" class="ml-2" />
                                            </div>
                                            <p class="text-sm text-gray-500">
                                                {{ $count->store->name ?? 'Unknown Store' }} •
                                                {{ $count->count_type }} •
                                                {{ $count->items_counted }}/{{ $count->total_items }} items
                                                @if($count->variance_count > 0)
                                                    • <span class="text-yellow-600">{{ $count->variance_count }} variances</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="text-sm text-gray-500">{{ $count->created_at->diffForHumans() }}</p>
                                            <div class="flex space-x-2 mt-1">
                                                <x-button.link href="#" size="xs">View Report</x-button.link>
                                                @if($count->status === 'review')
                                                    <x-button.secondary href="#" size="xs">Review</x-button.secondary>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <x-heroicon-s-clipboard-document-list class="mx-auto h-12 w-12 text-gray-400" />
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No recent counts</h3>
                                        <p class="mt-1 text-sm text-gray-500">Stock counts will appear here once completed.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (1/3 width) -->
                <div class="space-y-6">
                    <!-- Stock Summary by Store -->
                    <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 surface border-b border-secondary">
                            <h3 class="text-lg font-semibold text-primary mb-4">Stock Summary by Store</h3>
                            <div class="space-y-3">
                                @forelse($storeStockSummary ?? [] as $store)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                        <div>
                                            <span class="font-medium text-gray-900">{{ $store->name }}</span>
                                            <p class="text-xs text-gray-500">{{ $store->total_items }} items</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-medium text-gray-900">
                                                ${{ number_format($store->total_value / 100, 0) }}K
                                            </span>
                                            <p class="text-xs text-gray-500">{{ $store->total_quantity }} units</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No store data available</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tasks -->
                    <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 surface border-b border-secondary">
                            <h3 class="text-lg font-semibold text-primary mb-4">Pending Tasks</h3>
                            <div class="space-y-3">
                                @if(($pendingApprovals ?? 0) > 0)
                                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                        <div class="flex items-center">
                                            <x-heroicon-s-clock class="h-5 w-5 text-yellow-600 mr-2" />
                                            <span class="text-sm font-medium text-yellow-900">Approvals Required</span>
                                        </div>
                                        <span class="text-lg font-bold text-yellow-900">{{ $pendingApprovals }}</span>
                                    </div>
                                @endif

                                @if(($overdueCounts ?? 0) > 0)
                                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                        <div class="flex items-center">
                                            <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-red-600 mr-2" />
                                            <span class="text-sm font-medium text-red-900">Overdue Counts</span>
                                        </div>
                                        <span class="text-lg font-bold text-red-900">{{ $overdueCounts }}</span>
                                    </div>
                                @endif

                                @if(($scheduledTransfers ?? 0) > 0)
                                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                        <div class="flex items-center">
                                            <x-heroicon-s-calendar class="h-5 w-5 text-blue-600 mr-2" />
                                            <span class="text-sm font-medium text-blue-900">Scheduled Transfers</span>
                                        </div>
                                        <span class="text-lg font-bold text-blue-900">{{ $scheduledTransfers }}</span>
                                    </div>
                                @endif

                                @if(($pendingApprovals ?? 0) == 0 && ($overdueCounts ?? 0) == 0 && ($scheduledTransfers ?? 0) == 0)
                                    <div class="text-center py-4">
                                        <x-heroicon-s-check-circle class="mx-auto h-8 w-8 text-green-500" />
                                        <p class="text-sm text-gray-500 mt-2">All caught up!</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Reports -->
                    <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 surface border-b border-secondary">
                            <h3 class="text-lg font-semibold text-primary mb-4">Quick Reports</h3>
                            <div class="space-y-2">
                                <x-button.link href="{{ route('inventory.reports.low-stock') }}" class="w-full justify-start">
                                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 mr-2" />
                                    Low Stock Alert
                                </x-button.link>
                                
                                <x-button.link href="{{ route('inventory.reports.movement') }}" class="w-full justify-start">
                                    <x-heroicon-s-arrow-path class="w-4 h-4 mr-2" />
                                    Stock Movement
                                </x-button.link>
                                
                                <x-button.link href="{{ route('inventory.reports.stock-levels') }}" class="w-full justify-start">
                                    <x-heroicon-s-chart-bar class="w-4 h-4 mr-2" />
                                    Stock Levels
                                </x-button.link>
                                
                                <x-button.outline href="{{ route('inventory.reports.index') }}" class="w-full justify-start">
                                    <x-heroicon-s-document-text class="w-4 h-4 mr-2" />
                                    All Reports
                                </x-button.outline>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
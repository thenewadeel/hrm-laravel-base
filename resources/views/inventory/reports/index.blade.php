<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📊 {{ __('Reports & Analytics') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Date Range Filter -->
            <div class="mb-8">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <form method="GET" action="{{ route('inventory.reports.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                            <div class="md:col-span-2">
                                <x-form.label for="date_range" value="Date Range" />
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <x-form.input 
                                            id="start_date" 
                                            name="start_date" 
                                            type="date" 
                                            class="mt-1 block w-full" 
                                            :value="request('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'))" 
                                        />
                                    </div>
                                    <div>
                                        <x-form.input 
                                            id="end_date" 
                                            name="end_date" 
                                            type="date" 
                                            class="mt-1 block w-full" 
                                            :value="request('end_date', \Carbon\Carbon::now()->format('Y-m-d'))" 
                                        />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <x-form.label for="store_id" value="Store" />
                                <x-form.select id="store_id" name="store_id" class="mt-1 block w-full">
                                    <option value="">All Stores</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </div>

                            <div>
                                <x-form.label for="category" value="Category" />
                                <x-form.select id="category" name="category" class="mt-1 block w-full">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </div>

                            <div class="flex space-x-2">
                                <x-button.primary type="submit" class="w-full">
                                    Apply Filters
                                </x-button.primary>
                                <x-button.secondary href="{{ route('inventory.reports.index') }}">
                                    Reset
                                </x-button.secondary>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <x-inventory.stock-card 
                    title="Total Transactions"
                    value="{{ $reportData['total_transactions'] ?? 0 }}"
                    trend="{{ $reportData['transaction_trend'] ?? '+0%' }}"
                    trendColor="{{ ($reportData['transaction_trend'] ?? '+0%')[0] === '+' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"
                    description="In selected period"
                    icon="📋"
                />
                
                <x-inventory.stock-card 
                    title="Items Moved"
                    value="{{ $reportData['total_quantity'] ?? 0 }}"
                    trend="{{ $reportData['quantity_trend'] ?? '+0%' }}"
                    trendColor="{{ ($reportData['quantity_trend'] ?? '+0%')[0] === '+' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"
                    description="Units transferred"
                    icon="🔄"
                />
                
                <x-inventory.stock-card 
                    title="Total Value"
                    value="${{ number_format(($reportData['total_value'] ?? 0) / 100, 2) }}"
                    trend="{{ $reportData['value_trend'] ?? '+0%' }}"
                    trendColor="{{ ($reportData['value_trend'] ?? '+0%')[0] === '+' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"
                    description="Transaction value"
                    icon="💰"
                />
                
                <x-inventory.stock-card 
                    title="Avg. Transaction"
                    value="${{ number_format(($reportData['avg_transaction_value'] ?? 0) / 100, 2) }}"
                    trend="{{ $reportData['avg_trend'] ?? '+0%' }}"
                    trendColor="{{ ($reportData['avg_trend'] ?? '+0%')[0] === '+' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"
                    description="Per transaction"
                    icon="📈"
                />
            </div>

            <!-- Report Types Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Stock Levels Report -->
                <a href="{{ route('inventory.reports.stock-levels') }}?{{ http_build_query(request()->query()) }}" 
                   class="block p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-lg transition-all duration-200 group">
                    <div class="text-center">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">📦</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Stock Levels</h3>
                        <p class="text-gray-600 mb-4">
                            Current inventory levels across all stores with reorder alerts
                        </p>
                        <div class="text-sm text-blue-600 font-medium">
                            View current stock • Low stock alerts • Reorder planning
                        </div>
                    </div>
                </a>

                <!-- Movement Report -->
                <a href="{{ route('inventory.reports.movement') }}?{{ http_build_query(request()->query()) }}" 
                   class="block p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-green-500 hover:shadow-lg transition-all duration-200 group">
                    <div class="text-center">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">📈</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Movement Report</h3>
                        <p class="text-gray-600 mb-4">
                            Track item movement, usage patterns, and transaction history
                        </p>
                        <div class="text-sm text-green-600 font-medium">
                            IN/OUT analysis • Usage trends • Transaction history
                        </div>
                    </div>
                </a>

                <!-- Low Stock Report -->
                <a href="{{ route('inventory.reports.low-stock') }}?{{ http_build_query(request()->query()) }}" 
                   class="block p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-red-500 hover:shadow-lg transition-all duration-200 group">
                    <div class="text-center">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">⚠️</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Low Stock Report</h3>
                        <p class="text-gray-600 mb-4">
                            Identify items below reorder levels and out-of-stock items
                        </p>
                        <div class="text-sm text-red-600 font-medium">
                            Critical alerts • Reorder suggestions • Stockout prevention
                        </div>
                    </div>
                </a>

                <!-- Valuation Report -->
                <a href="{{ route('inventory.reports.valuation') }}?{{ http_build_query(request()->query()) }}" 
                   class="block p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:shadow-lg transition-all duration-200 group">
                    <div class="text-center">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">💰</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Valuation Report</h3>
                        <p class="text-gray-600 mb-4">
                            Inventory value by category, store, and department
                        </p>
                        <div class="text-sm text-purple-600 font-medium">
                            Total value • Category breakdown • Store comparison
                        </div>
                    </div>
                </a>

                <!-- Transaction Summary -->
                <a href="{{ route('inventory.reports.transaction-summary') }}?{{ http_build_query(request()->query()) }}" 
                   class="block p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-orange-500 hover:shadow-lg transition-all duration-200 group">
                    <div class="text-center">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">📋</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Transaction Summary</h3>
                        <p class="text-gray-600 mb-4">
                            Summary of all transactions with filtering and export options
                        </p>
                        <div class="text-sm text-orange-600 font-medium">
                            Daily summary • Type analysis • Export data
                        </div>
                    </div>
                </a>

                <!-- Performance Metrics -->
                <a href="{{ route('inventory.reports.performance') }}?{{ http_build_query(request()->query()) }}" 
                   class="block p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-lg transition-all duration-200 group">
                    <div class="text-center">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">🚀</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Performance Metrics</h3>
                        <p class="text-gray-600 mb-4">
                            Key performance indicators and inventory health metrics
                        </p>
                        <div class="text-sm text-indigo-600 font-medium">
                            KPIs • Turnover rates • Stockout frequency
                        </div>
                    </div>
                </a>
            </div>

            <!-- Quick Insights -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activity -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">📈 Quick Insights</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-blue-900">Most Active Item</div>
                                    <div class="text-sm text-blue-700">{{ $reportData['most_active_item'] ?? 'N/A' }}</div>
                                </div>
                                <div class="text-2xl">🔥</div>
                            </div>
                            
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-green-900">Highest Value Transaction</div>
                                    <div class="text-sm text-green-700">${{ number_format(($reportData['highest_value_tx'] ?? 0) / 100, 2) }}</div>
                                </div>
                                <div class="text-2xl">💰</div>
                            </div>
                            
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-yellow-900">Busiest Store</div>
                                    <div class="text-sm text-yellow-700">{{ $reportData['busiest_store'] ?? 'N/A' }}</div>
                                </div>
                                <div class="text-2xl">🏪</div>
                            </div>
                            
                            <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-red-900">Items Need Reorder</div>
                                    <div class="text-sm text-red-700">{{ $reportData['reorder_items_count'] ?? 0 }} items</div>
                                </div>
                                <div class="text-2xl">⚠️</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">📤 Export Reports</h3>
                        <div class="space-y-3">
                            <x-button.outline href="{{ route('inventory.reports.export', ['type' => 'stock_levels', 'format' => 'pdf']) }}?{{ http_build_query(request()->query()) }}" class="w-full justify-start">
                                <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                                Export Stock Levels (PDF)
                            </x-button.outline>
                            
                            <x-button.outline href="{{ route('inventory.reports.export', ['type' => 'movement', 'format' => 'csv']) }}?{{ http_build_query(request()->query()) }}" class="w-full justify-start">
                                <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                                Export Movement Data (CSV)
                            </x-button.outline>
                            
                            <x-button.outline href="{{ route('inventory.reports.export', ['type' => 'valuation', 'format' => 'excel']) }}?{{ http_build_query(request()->query()) }}" class="w-full justify-start">
                                <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                                Export Valuation (Excel)
                            </x-button.outline>
                            
                            <x-button.outline href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'csv']) }}?{{ http_build_query(request()->query()) }}" class="w-full justify-start">
                                <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                                Export All Transactions (CSV)
                            </x-button.outline>
                        </div>
                        
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Export Tips</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• PDF: Best for printing and sharing</li>
                                <li>• CSV: Ideal for data analysis in spreadsheets</li>
                                <li>• Excel: Includes formatting and formulas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
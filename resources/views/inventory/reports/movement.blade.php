<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📈 {{ __('Movement Report') }}
            </h2>
            <div class="flex space-x-2">
                <x-button.outline href="{{ route('inventory.reports.export', ['type' => 'movement', 'format' => 'pdf']) }}?{{ http_build_query(request()->query()) }}">
                    <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                    Export PDF
                </x-button.outline>
                <x-button.primary href="{{ route('inventory.reports.export', ['type' => 'movement', 'format' => 'csv']) }}?{{ http_build_query(request()->query()) }}">
                    <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                    Export CSV
                </x-button.primary>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Report Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('inventory.reports.movement') }}">
                    <div class="bg-white p-4 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="md:col-span-2">
                                <x-form.label for="date_range" value="Date Range" />
                                <div class="grid grid-cols-2 gap-2">
                                    <x-form.input 
                                        id="start_date" 
                                        name="start_date" 
                                        type="date" 
                                        class="mt-1 block w-full" 
                                        :value="request('start_date', \Carbon\Carbon::now()->subDays(30)->format('Y-m-d'))" 
                                    />
                                    <x-form.input 
                                        id="end_date" 
                                        name="end_date" 
                                        type="date" 
                                        class="mt-1 block w-full" 
                                        :value="request('end_date', \Carbon\Carbon::now()->format('Y-m-d'))" 
                                    />
                                </div>
                            </div>

                            <div>
                                <x-form.label for="item_id" value="Item" />
                                <x-form.select id="item_id" name="item_id" class="mt-1 block w-full">
                                    <option value="">All Items</option>
                                    @foreach($allItems as $item)
                                        <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }} ({{ $item->sku }})
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </div>

                            <div>
                                <x-form.label for="transaction_type" value="Transaction Type" />
                                <x-form.select id="transaction_type" name="transaction_type" class="mt-1 block w-full">
                                    <option value="">All Types</option>
                                    <option value="receipt" {{ request('transaction_type') == 'receipt' ? 'selected' : '' }}>Receipt</option>
                                    <option value="issue" {{ request('transaction_type') == 'issue' ? 'selected' : '' }}>Issue</option>
                                    <option value="transfer" {{ request('transaction_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="adjustment" {{ request('transaction_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                </x-form.select>
                            </div>

                            <div class="flex items-end space-x-2">
                                <x-button.primary type="submit" class="w-full">
                                    Apply Filters
                                </x-button.primary>
                                <x-button.secondary href="{{ route('inventory.reports.movement') }}">
                                    Reset
                                </x-button.secondary>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Movement Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-green-50 p-4 rounded-lg border border-green-200 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $summary['total_received'] ?? 0 }}</div>
                    <div class="text-sm text-green-700">Total Received</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg border border-red-200 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $summary['total_issued'] ?? 0 }}</div>
                    <div class="text-sm text-red-700">Total Issued</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $summary['net_movement'] ?? 0 }}</div>
                    <div class="text-sm text-blue-700">Net Movement</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $summary['total_transactions'] ?? 0 }}</div>
                    <div class="text-sm text-purple-700">Transactions</div>
                </div>
            </div>

            <!-- Movement Chart Placeholder -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Movement Trends</h3>
                    <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
                        <div class="text-center text-gray-500">
                            <x-heroicon-s-chart-bar class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2">Movement chart visualization</p>
                            <p class="text-sm">(Chart would show received vs issued over time)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Movement Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Movement Details</h3>
                        <div class="text-sm text-gray-500">
                            Period: {{ \Carbon\Carbon::parse(request('start_date', \Carbon\Carbon::now()->subDays(30)))->format('M j, Y') }} 
                            - {{ \Carbon\Carbon::parse(request('end_date', \Carbon\Carbon::now()))->format('M j, Y') }}
                        </div>
                    </div>

                    @if($movements->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($movements as $movement)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $movement->transaction_date->format('M j, Y') }}
                                                <div class="text-xs text-gray-500">{{ $movement->transaction_date->format('g:i A') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $movement->item->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $movement->item->sku }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                                <a href="{{ route('inventory.transactions.show', $movement->transaction) }}" class="hover:text-blue-600">
                                                    {{ $movement->transaction->reference }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $typeIcons = [
                                                        'receipt' => '📥',
                                                        'issue' => '📤',
                                                        'transfer' => '🔄',
                                                        'adjustment' => '📊'
                                                    ];
                                                @endphp
                                                <div class="flex items-center">
                                                    <span class="text-lg mr-2">{{ $typeIcons[$movement->transaction->type] ?? '📄' }}</span>
                                                    <span class="text-sm text-gray-900 capitalize">{{ $movement->transaction->type }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $movement->store->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium 
                                                {{ $movement->transaction->type === 'receipt' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $movement->transaction->type === 'receipt' ? '+' : '-' }}{{ $movement->quantity }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format(($movement->quantity * $movement->unit_price) / 100, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($movements->hasPages())
                            <div class="mt-4">
                                {{ $movements->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-s-chart-bar class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No movement data found</h3>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or date range.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Top Movers -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <!-- Top Receipts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">📥 Top Received Items</h3>
                        <div class="space-y-3">
                            @forelse($topReceived as $item)
                                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                    <div class="flex items-center">
                                        <x-heroicon-s-cube class="h-4 w-4 text-green-600 mr-2" />
                                        <div>
                                            <div class="text-sm font-medium text-green-900">{{ $item->name }}</div>
                                            <div class="text-xs text-green-700">{{ $item->sku }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-green-600 font-bold">+{{ $item->total_quantity }}</div>
                                        <div class="text-xs text-green-700">{{ $item->unit }}</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No receipt data</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Top Issues -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">📤 Top Issued Items</h3>
                        <div class="space-y-3">
                            @forelse($topIssued as $item)
                                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                    <div class="flex items-center">
                                        <x-heroicon-s-cube class="h-4 w-4 text-red-600 mr-2" />
                                        <div>
                                            <div class="text-sm font-medium text-red-900">{{ $item->name }}</div>
                                            <div class="text-xs text-red-700">{{ $item->sku }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-red-600 font-bold">-{{ $item->total_quantity }}</div>
                                        <div class="text-xs text-red-700">{{ $item->unit }}</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No issue data</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
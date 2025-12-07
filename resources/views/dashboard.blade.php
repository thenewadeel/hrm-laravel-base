{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            Dashboard - {{ $organization?->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:p-4 rounded-lg shadow-md shadow-slate-700 border border-secondary">
            <h3 class="text-2xl font-semibold leading-tight mb-4 text-primary">
                Inventory Overview
            </h3>

            <!-- Quick Actions -->
            <div class="mb-6">
                <div class="surface overflow-hidden shadow rounded-lg p-6">
                    <h3 class="text-lg leading-6 font-medium text-primary mb-4">Quick Actions</h3>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('inventory.stores.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Store
                        </a>
                        <a href="{{ route('inventory.transactions.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            New Transaction
                        </a>
                        <a href="{{ route('inventory.items.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            Add Item
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Stores Card -->
                <div class="surface overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-muted" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted truncate">Stores</dt>
                                    <dd class="text-lg font-medium text-primary">{{ $stores->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Items Card -->
                <div class="surface overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-muted" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted truncate">Total Items</dt>
                                    <dd class="text-lg font-medium text-primary">{{ $totalItems }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alerts Card -->
                <div class="surface overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted truncate">Low Stock Items</dt>
                                    <dd class="text-lg font-medium text-primary">{{ $lowStockItems->count() }}</dd>
                                    @foreach ($lowStockItems as $lowStockItem)
                                        {{ $lowStockItem?->name }}
                                    @endforeach
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions Card -->
                <div class="surface overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-muted" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted truncate">Recent Transactions</dt>
                                    <dd class="text-lg font-medium text-primary">{{ $recentTransactions->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Stores List -->
                <div class="surface overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-secondary">
                        <h3 class="text-lg leading-6 font-medium text-primary">Stores Overview</h3>
                    </div>
                    <div class="p-6">
                        @if ($stores->count() > 0)
                            <div class="space-y-4">
                                @foreach ($stores as $store)
                                    <div class="flex items-center justify-between p-3 bg-tertiary rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-primary">{{ $store?->name }}</h4>
                                            <p class="text-sm text-secondary">{{ $store->location }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $store->items_count }} items
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center py-4">No stores yet. <a {{-- href="{{ route('stores.create') }}"  --}}
                                    class="text-blue-600 hover:text-blue-800">Add your first store</a></p>
                        @endif
                    </div>
                </div>

                <!-- Low Stock Alerts -->
                <div class="surface overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-secondary">
                        <h3 class="text-lg leading-6 font-medium text-primary">Low Stock Alerts</h3>
                    </div>
                    <div class="p-6">
                        @if ($lowStockItems->count() > 0)
                            <div class="space-y-3">
                                @foreach ($lowStockItems as $item)
                                    <div
                                        class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-red-900">{{ $item->name }}</h4>
                                            <p class="text-sm text-red-700">
                                                Low stock in multiple stores
                                            </p>
                                        </div>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Low Stock
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-green-600 text-center py-4">All items are well stocked! 🎉</p>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Recent Transactions -->
            @if ($recentTransactions->count() > 0)
                <div class="mt-6 surface overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-secondary">
                        <h3 class="text-lg leading-6 font-medium text-primary">Recent Transactions</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach ($recentTransactions as $transaction)
                                <div class="flex items-center justify-between p-3 bg-tertiary rounded-lg">
                                    <div>
                                        <h4 class="font-medium text-primary">{{ $transaction->reference }}</h4>
                                        <p class="text-sm text-secondary">{{ $transaction->store?->name }} •
                                            {{ $transaction->created_at->diffForHumans() }}</p>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>

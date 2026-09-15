<x-app-layout>
    <x-slot name="header">
        <x-page-header title="🏠 {{ __('Inventory Dashboard') }}" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <x-inventory.stock-card title="Total Items" :value="$totalItems ?? 0" trend="+12%"
                    trendColor="bg-success/10 text-success" description="Across all stores" icon="📦" />

                <x-inventory.stock-card title="Low Stock Items" :value="$lowStockItems->count()" trend="+5%"
                    trendColor="bg-warning/10 text-warning" description="Need attention" icon="⚠️" />

                <x-inventory.stock-card title="Out of Stock" :value="$outOfStockItems ?? 0" trend="-2%"
                    trendColor="bg-error/10 text-error" description="Requires restocking" icon="❌" />

                <x-inventory.stock-card title="Total Value"
                    :value="'$' . number_format(($totalValue ?? 0) / 100, 2)"
                    trend="+8%" trendColor="bg-info/10 text-info" description="Inventory worth" icon="💰" />
            </div>

            <div>
                @if ($lowStockItems->count() > 0)
                    <x-inventory.low-stock-alert level="warning" :items="$lowStockItems" title="{{ __('Low Stock Alert') }}">
                        {{ $lowStockItems->count() }} {{ __('items are below reorder level and need attention.') }}
                        <a href="{{ route('inventory.reports.low-stock') }}"
                            class="font-medium underline">{{ __('View low stock report') }}</a>
                    </x-inventory.low-stock-alert>
                @endif

                @if (($outOfStockItems ?? 0) > 0)
                    <x-inventory.low-stock-alert level="danger" :items="$outOfStockItems" title="{{ __('Out of Stock Alert') }}">
                        {{ $outOfStockItems }} {{ __('items are out of stock and require immediate restocking.') }}
                        <a href="{{ route('inventory.reports.low-stock') }}"
                            class="font-medium underline">{{ __('View out of stock items') }}</a>
                    </x-inventory.low-stock-alert>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <x-button.link href="{{ route('inventory.items.create') }}"
                    class="flex flex-col items-center justify-center py-4">
                    <span class="mb-2 text-2xl">📦</span>
                    <span class="text-sm">{{ __('Add Item') }}</span>
                </x-button.link>

                <x-button.link variant="secondary" href="{{ route('inventory.transactions.create') }}?type=receipt"
                    class="flex flex-col items-center justify-center py-4">
                    <span class="mb-2 text-2xl">📥</span>
                    <span class="text-sm">{{ __('Receive Stock') }}</span>
                </x-button.link>

                <x-button.link variant="secondary" href="{{ route('inventory.transactions.create') }}?type=issue"
                    class="flex flex-col items-center justify-center py-4">
                    <span class="mb-2 text-2xl">📤</span>
                    <span class="text-sm">{{ __('Issue Items') }}</span>
                </x-button.link>

                <x-button.link variant="outline" href="{{ route('inventory.reports.stock-levels') }}"
                    class="flex flex-col items-center justify-center py-4">
                    <span class="mb-2 text-2xl">📊</span>
                    <span class="text-sm">{{ __('View Reports') }}</span>
                </x-button.link>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <x-card title="{{ __('Recent Transactions') }}">
                        <x-slot name="actions">
                            <x-button.link href="{{ route('inventory.transactions.index') }}"
                                size="sm">{{ __('View All') }}</x-button.link>
                        </x-slot>

                        <div class="space-y-4">
                            @forelse ($recentTransactions ?? [] as $transaction)
                                <div class="flex items-center justify-between border-b border-secondary/60 py-3 last:border-0">
                                    <div>
                                        <p class="font-medium text-primary">{{ $transaction->reference }}</p>
                                        <p class="text-sm text-muted">
                                            {{ $transaction->type }} •
                                            {{ $transaction->store->name ?? 'Unknown Store' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <x-badge :status="$transaction->status" />
                                        <p class="text-sm text-muted">
                                            {{ $transaction->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center">
                                    <x-heroicon-o-document-text class="mx-auto size-12 text-muted" />
                                    <h3 class="mt-2 text-sm font-medium text-primary">{{ __('No transactions') }}</h3>
                                    <p class="mt-1 text-sm text-muted">{{ __('Get started by creating your first transaction.') }}
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </x-card>
                </div>

                <div class="space-y-6">
                    <x-card title="{{ __('Store Summary') }}">
                        <div class="space-y-3">
                            @forelse ($storeSummary ?? [] as $store)
                                <div class="flex items-center justify-between border-b border-secondary/60 py-2 last:border-0">
                                    <div>
                                        <span class="font-medium text-primary">{{ $store->name }}</span>
                                        <p class="text-xs text-muted">{{ $store->location }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-medium text-primary">{{ $store->items_count }}
                                            {{ __('items') }}</span>
                                        <p class="text-xs text-muted">
                                            ${{ number_format($store->total_value / 100, 2) }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="py-4 text-center text-muted">{{ __('No stores configured') }}</p>
                            @endforelse
                        </div>
                    </x-card>

                    <x-card title="{{ __('Quick Reports') }}">
                        <div class="space-y-2">
                            <x-button.link variant="ghost" href="{{ route('inventory.reports.low-stock') }}"
                                class="w-full justify-start">
                                ⚠️ {{ __('Low Stock Report') }}
                            </x-button.link>
                            <x-button.link variant="ghost" href="{{ route('inventory.reports.movement') }}"
                                class="w-full justify-start">
                                📈 {{ __('Movement Report') }}
                            </x-button.link>
                            <x-button.link variant="ghost" href="{{ route('inventory.reports.stock-levels') }}"
                                class="w-full justify-start">
                                📊 {{ __('Stock Levels') }}
                            </x-button.link>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
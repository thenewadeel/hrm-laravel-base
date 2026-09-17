<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-primary leading-tight">
                📦 {{ __('Item Details') }} <span class="text-muted text-lg">{{ $item->sku }}</span>
            </h2>
            <div class="flex space-x-2">
                <x-button.link href="{{ route('inventory.items.index') }}">
                    <x-heroicon-s-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back') }}
                </x-button.link>
                <x-button.primary href="{{ route('inventory.items.edit', $item) }}">
                    <x-heroicon-s-pencil class="w-4 h-4 mr-2" />
                    {{ __('Edit') }}
                </x-button.primary>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 h-16 w-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                <x-heroicon-s-cube class="h-8 w-8 text-gray-400" />
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-primary">{{ $item->name }}</h3>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-muted">
                                    <span class="font-mono">{{ $item->sku }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $item->category ?? 'Uncategorized' }}</span>
                                    @if ($item->head)
                                        <span>&bull;</span>
                                        <span>{{ $item->head->name }}</span>
                                    @endif
                                    <span>&bull;</span>
                                    <x-badge :status="$item->is_active ? 'active' : 'inactive'" />
                                </div>
                                @if ($item->description)
                                    <p class="mt-4 text-sm text-secondary">{{ $item->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="surface overflow-hidden shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="px-6 py-4 border-b border-secondary">
                        <h3 class="text-lg font-semibold text-primary">{{ __('Stock Levels Across Stores') }}</h3>
                    </div>
                    <div class="p-6">
                        @forelse ($item->stores as $store)
                            <div class="flex items-center justify-between py-3 border-b border-secondary last:border-0">
                                <div>
                                    <p class="text-sm font-medium text-primary">{{ $store->name }}</p>
                                    <p class="text-xs text-muted">{{ $store->location ?? '—' }}</p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    @if ($store->pivot->min_stock || $store->pivot->max_stock)
                                        <div class="text-right">
                                            <p class="text-xs text-muted">Min {{ $store->pivot->min_stock ?? 0 }} / Max {{ $store->pivot->max_stock ?? '∞' }}</p>
                                        </div>
                                    @endif
                                    <x-inventory.quantity-indicator
                                        :quantity="$store->pivot->quantity"
                                        :reorderLevel="$item->reorder_level" />
                                    <div class="text-right w-20">
                                        <p class="text-sm font-semibold text-primary">{{ $store->pivot->quantity }}</p>
                                        <p class="text-xs text-muted">{{ $item->unit }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <x-heroicon-s-home class="mx-auto h-10 w-10 text-muted" />
                                <p class="mt-2 text-sm text-muted">{{ __('No stock recorded in any store.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-secondary">
                            <h3 class="text-lg font-semibold text-primary">{{ __('Pricing') }}</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">{{ __('Cost Price') }}</span>
                                <span class="text-sm font-medium text-primary">{{ $item->formatted_cost_price }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">{{ __('Selling Price') }}</span>
                                <span class="text-sm font-medium text-primary">{{ $item->formatted_selling_price }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">{{ __('Total Stock Value') }}</span>
                                <span class="text-sm font-semibold text-primary">{{ $item->overall_cost }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-secondary">
                            <h3 class="text-lg font-semibold text-primary">{{ __('Configuration') }}</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">{{ __('Unit') }}</span>
                                <span class="text-sm font-medium text-primary">{{ $item->unit }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">{{ __('Reorder Level') }}</span>
                                <span class="text-sm font-medium text-primary">{{ $item->reorder_level ?? '—' }}</span>
                            </div>
                            @if ($item->organization)
                                <div class="flex justify-between">
                                    <span class="text-sm text-secondary">{{ __('Organization') }}</span>
                                    <span class="text-sm font-medium text-primary">{{ $item->organization->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-secondary flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-primary">{{ __('Recent Transactions') }}</h3>
                    <x-button.link href="{{ route('inventory.transactions.index') }}" size="sm">
                        {{ __('View All') }}
                    </x-button.link>
                </div>
                <div class="p-6">
                    @php
                        $recentTransactions = $item->transactionItems()
                            ->with(['transaction.store'])
                            ->latest()
                            ->limit(10)
                            ->get();
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-secondary">
                            <thead class="bg-tertiary">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Store') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-muted uppercase tracking-wider">{{ __('Quantity') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-muted uppercase tracking-wider">{{ __('Unit Price') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-secondary">
                                @forelse ($recentTransactions as $entry)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">
                                            {{ $entry->transaction?->transaction_date?->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-badge :status="$entry->transaction?->type ?? 'unknown'" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                            {{ $entry->transaction?->store?->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-primary">
                                            {{ $entry->quantity }} {{ $item->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-primary">
                                            ${{ number_format(($entry->unit_price ?? 0) / 100, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-muted">
                                            {{ __('No recent transactions for this item.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
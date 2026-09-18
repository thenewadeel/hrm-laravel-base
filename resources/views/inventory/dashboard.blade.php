{{-- resources/views/inventory/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="'Inventory Dashboard - '.($organization?->name ?? '')"
            description="{{ __('Overview of your stores, inventory, and recent activity.') }}"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div>
                <x-heading :as="'h2'" title="{{ __('Inventory Overview') }}" />
            </div>

            <x-card title="{{ __('Quick Actions') }}">
                <div class="flex flex-wrap gap-3">
                    <x-button.link href="{{ route('inventory.stores.create') }}">
                        <x-heroicon-o-plus class="mr-2 size-4" />
                        {{ __('Add Store') }}
                    </x-button.link>

                    <x-button.link variant="outline" href="{{ route('inventory.transactions.create') }}">
                        <x-heroicon-o-arrows-right-left class="mr-2 size-4" />
                        {{ __('New Transaction') }}
                    </x-button.link>

                    <x-button.link variant="ghost" href="{{ route('inventory.items.create') }}">
                        <x-heroicon-o-home class="mr-2 size-4" />
                        {{ __('Add Item') }}
                    </x-button.link>
                </div>
            </x-card>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat-card label="{{ __('Stores') }}" :value="$stores->count()" tone="info" data-stats="stores">
                    <x-slot name="icon">
                        <x-heroicon-o-document-text class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Total Items') }}" :value="$totalItems" tone="primary" data-stats="items">
                    <x-slot name="icon">
                        <x-heroicon-o-home class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Low Stock Items') }}"
                    :value="$lowStockItems->count()" tone="warning" data-stats="low-stock">
                    <x-slot name="icon">
                        <x-heroicon-o-exclamation-triangle class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Recent Transactions') }}"
                    :value="$recentTransactions->count()" tone="muted" data-stats="transactions">
                    <x-slot name="icon">
                        <x-heroicon-o-clipboard-document class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-card title="{{ __('Stores Overview') }}">
                    @if ($stores->count() > 0)
                        <div class="space-y-4">
                            @foreach ($stores as $store)
                                <div class="flex items-center justify-between rounded-lg bg-tertiary p-3">
                                    <div>
                                        <h4 class="font-medium text-primary">{{ $store?->name }}</h4>
                                        <p class="text-sm text-secondary">{{ $store->location }}</p>
                                    </div>
                                    <x-badge color="blue">{{ $store->items_count }} {{ __('items') }}</x-badge>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-4 text-center text-secondary">
                            {{ __('No stores yet.') }}
                            <a href="{{ route('inventory.stores.create') }}"
                                class="font-medium text-primary underline decoration-secondary underline-offset-2 hover:text-accent">
                                {{ __('Add your first store') }}
                            </a>
                        </p>
                    @endif
                </x-card>

                <x-card title="{{ __('Low Stock Alerts') }}">
                    @if ($lowStockItems->count() > 0)
                        <div class="space-y-3">
                            @foreach ($lowStockItems as $item)
                                <div class="flex items-center justify-between rounded-lg border border-error/25 bg-error/10 p-3">
                                    <div>
                                        <h4 class="font-medium text-primary">{{ $item->name }}</h4>
                                        <p class="text-sm text-error">
                                            {{ __('Low stock in multiple stores') }}
                                        </p>
                                    </div>
                                    <x-badge color="red">{{ __('Low Stock') }}</x-badge>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center py-4 text-success">{{ __('All items are well stocked!') }}</p>
                    @endif
                </x-card>
            </div>

            @if ($recentTransactions->count() > 0)
                <x-card title="{{ __('Recent Transactions') }}">
                    <div class="space-y-3">
                        @foreach ($recentTransactions as $transaction)
                            <div class="flex items-center justify-between rounded-lg bg-tertiary p-3">
                                <div>
                                    <h4 class="font-medium text-primary">{{ $transaction->reference }}</h4>
                                    <p class="text-sm text-secondary">{{ $transaction->store?->name }} •
                                        {{ $transaction->created_at->diffForHumans() }}</p>
                                </div>
                                <x-badge :color="strtolower($transaction->status) === 'completed' || strtolower($transaction->status) === 'approved' ? 'green' : 'gray'">
                                    {{ ucfirst($transaction->status) }}
                                </x-badge>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    </div>
</x-app-layout>
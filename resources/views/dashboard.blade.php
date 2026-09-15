{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="'Dashboard - '.($organization?->name ?? '')"
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
                        <svg class="mr-2 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ __('Add Store') }}
                    </x-button.link>

                    <x-button.link variant="outline" href="{{ route('inventory.transactions.create') }}">
                        <svg class="mr-2 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        {{ __('New Transaction') }}
                    </x-button.link>

                    <x-button.link variant="ghost" href="{{ route('inventory.items.create') }}">
                        <svg class="mr-2 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        {{ __('Add Item') }}
                    </x-button.link>
                </div>
            </x-card>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat-card label="{{ __('Stores') }}" :value="$stores->count()" tone="info">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Total Items') }}" :value="$totalItems" tone="primary">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Low Stock Items') }}"
                    :value="$lowStockItems->count()" tone="warning">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Recent Transactions') }}"
                    :value="$recentTransactions->count()" tone="muted">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
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
                        <p class="text-center py-4 text-success">{{ __('All items are well stocked! 🎉') }}</p>
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
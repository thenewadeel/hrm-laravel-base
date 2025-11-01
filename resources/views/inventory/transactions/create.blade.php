<x-app-layout>
    {{--
- Transaction type selector (Receipt, Issue, Transfer, Adjustment)
- Source/Destination store selection
- Reference number (auto-generated)
- Item selection with quantity and pricing
- Notes field
- Save as Draft/Finalize buttons

 --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📋 {{ __('New Transaction') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 bg-white border-b border-gray-200">
                    <!-- Transaction Type Selection -->
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Choose Transaction Type</h3>
                        <p class="text-gray-600">Select the type of transaction you want to create</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Receipt Transaction -->
                        <a href="{{ route('inventory.transactions.wizard') }}?type=receipt{{ request('store_id') ? '&store_id=' . request('store_id') : '' }}"
                            class="block p-6 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-md transition-all duration-200 group">
                            <div class="text-center">
                                <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">📥</div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Receive Stock</h4>
                                <p class="text-gray-600 text-sm">
                                    Record incoming items from suppliers, production, or returns. Increases inventory
                                    levels.
                                </p>
                                <div class="mt-4 text-xs text-blue-600 font-medium">
                                    Use for: Purchases, Returns, Production
                                </div>
                            </div>
                        </a>

                        <!-- Issue Transaction -->
                        <a href="{{ route('inventory.transactions.wizard') }}?type=issue{{ request('store_id') ? '&store_id=' . request('store_id') : '' }}"
                            class="block p-6 border-2 border-gray-200 rounded-lg hover:border-green-500 hover:shadow-md transition-all duration-200 group">
                            <div class="text-center">
                                <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">📤</div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Issue Items</h4>
                                <p class="text-gray-600 text-sm">
                                    Record outgoing items to departments, customers, or for usage. Decreases inventory
                                    levels.
                                </p>
                                <div class="mt-4 text-xs text-green-600 font-medium">
                                    Use for: Sales, Usage, Transfers Out
                                </div>
                            </div>
                        </a>

                        <!-- Transfer Transaction -->
                        <a href="{{ route('inventory.transactions.wizard') }}?type=transfer{{ request('store_id') ? '&from_store_id=' . request('store_id') : '' }}"
                            class="block p-6 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:shadow-md transition-all duration-200 group">
                            <div class="text-center">
                                <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">🔄</div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Transfer Items</h4>
                                <p class="text-gray-600 text-sm">
                                    Move items between different store locations. Maintains total inventory levels.
                                </p>
                                <div class="mt-4 text-xs text-purple-600 font-medium">
                                    Use for: Store Transfers, Relocations
                                </div>
                            </div>
                        </a>

                        <!-- Adjustment Transaction -->
                        <a href="{{ route('inventory.transactions.wizard') }}?type=adjustment{{ request('store_id') ? '&store_id=' . request('store_id') : '' }}"
                            class="block p-6 border-2 border-gray-200 rounded-lg hover:border-orange-500 hover:shadow-md transition-all duration-200 group">
                            <div class="text-center">
                                <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">📊</div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Stock Adjustment</h4>
                                <p class="text-gray-600 text-sm">
                                    Correct inventory levels after stock counts, damage, or discrepancies.
                                </p>
                                <div class="mt-4 text-xs text-orange-600 font-medium">
                                    Use for: Stock Counts, Damage, Corrections
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach ($quickStores as $store)
                                <div class="text-center">
                                    <p class="text-sm text-gray-600 mb-2">Quick receipt for</p>
                                    <x-button.outline
                                        href="{{ route('inventory.transactions.wizard') }}?type=receipt&store_id={{ $store->id }}"
                                        class="w-full">
                                        🏪 {{ $store->name }}
                                    </x-button.outline>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Cancel Button -->
                    <div class="mt-8 text-center">
                        <x-button.secondary href="{{ route('inventory.transactions.index') }}">
                            Cancel
                        </x-button.secondary>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

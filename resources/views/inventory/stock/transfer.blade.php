<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-primary leading-tight">
                🔄 {{ __('Stock Transfer') }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Back to Inventory
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Stock Transfer Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('inventory.stock.process-transfer') }}" method="POST">
                        @csrf

                        <!-- From Store -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">From Store</label>
                            <select id="from_store_id" name="from_store_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select source store</option>
                                @foreach($stores ?? [] as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- To Store -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700">To Store</label>
                            <select id="to_store_id" name="to_store_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select destination store</option>
                                @foreach($stores ?? [] as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700">Transfer Notes</label>
                            <textarea 
                                id="notes" 
                                name="notes" 
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter notes about this stock transfer..."
                            ></textarea>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('inventory.stock.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                Process Transfer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Transfers -->
            @if (isset($recentTransfers) && $recentTransfers->count() > 0)
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Stock Transfers</h3>
                        <div class="space-y-3">
                            @foreach($recentTransfers as $transfer)
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $transfer->reference }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $transfer->store->name ?? 'Unknown Store' }} → {{ $transfer->toStore->name ?? 'Unknown Store' }} •
                                            {{ $transfer->items_count ?? 0 }} items
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                        <p class="text-sm text-gray-500">
                                            {{ $transfer->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
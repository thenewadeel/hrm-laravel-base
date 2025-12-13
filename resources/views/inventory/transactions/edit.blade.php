@php
    $typeIcons = [
        'receipt' => '📥',
        'issue' => '📤',
        'transfer' => '🔄',
        'adjustment' => '📊',
    ];
    $typeTitles = [
        'receipt' => 'Receive Stock',
        'issue' => 'Issue Items',
        'transfer' => 'Transfer Items',
        'adjustment' => 'Stock Adjustment',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $typeIcons[$transaction->type] ?? '📋' }} Edit {{ $typeTitles[$transaction->type] ?? 'Transaction' }}
            </h2>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $transaction->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $transaction->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ ucfirst($transaction->status) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('inventory.transactions.update', $transaction) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="space-y-6">
                            <!-- Transaction Details -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">📋 Transaction Details</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <x-form.label for="reference" value="Reference Number *" />
                                        <x-form.input id="reference" name="reference" type="text"
                                            class="mt-1 block w-full" :value="old('reference', $transaction->reference)" required
                                            placeholder="e.g., REC-2024-001" />
                                        <p class="mt-1 text-sm text-gray-500">Unique identifier for this transaction</p>
                                        <x-form.input-error for="reference" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="transaction_date" value="Transaction Date *" />
                                        <x-form.input id="transaction_date" name="transaction_date"
                                            type="datetime-local" class="mt-1 block w-full" :value="old(
                                                'transaction_date',
                                                \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d\TH:i'),
                                            )"
                                            required />
                                        <x-form.input-error for="transaction_date" class="mt-2" />
                                    </div>

                                    <!-- Store Selection -->
                                    <div>
                                        <x-form.label for="store_id" value="Store *" />
                                        <x-form.select id="store_id" name="store_id" class="mt-1 block w-full"
                                            required>
                                            <option value="">Select Store</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}"
                                                    {{ old('store_id', $transaction->store_id) == $store->id ? 'selected' : '' }}>
                                                    {{ $store->name }}
                                                </option>
                                            @endforeach
                                        </x-form.select>
                                        <x-form.input-error for="store_id" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="notes" value="Notes" />
                                        <x-form.textarea id="notes" name="notes" class="mt-1 block w-full"
                                            rows="3"
                                            placeholder="Add any additional notes about this transaction...">{{ old('notes', $transaction->notes) }}</x-form.textarea>
                                        <x-form.input-error for="notes" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">📦 Transaction Items</h3>
                            <button type="button" id="add-item-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Item
                            </button>
                        </div>

                        <!-- Items Container -->
                        <div id="items-container" class="space-y-4">
                            @foreach ($transaction->items as $index => $item)
                                <div class="item-row border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <div class="flex justify-between items-start mb-3">
                                        <h4 class="text-sm font-medium text-gray-700">Item {{ $index + 1 }}</h4>
                                        <button type="button" class="remove-item-btn text-red-600 hover:text-red-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div class="md:col-span-2">
                                            <x-form.label :for="'items_' . $index . '_item_id'" value="Item *" />
                                            <x-form.select :id="'items_' . $index . '_item_id'" :name="'items[' . $index . '][item_id]'" class="mt-1 block w-full" required>
                                                <option value="">Select Item</option>
                                                @foreach ($items as $availableItem)
                                                    <option value="{{ $availableItem->id }}" 
                                                        {{ old('items.' . $index . '.item_id', $item->item_id) == $availableItem->id ? 'selected' : '' }}>
                                                        {{ $availableItem->name }} ({{ $availableItem->code ?? $availableItem->id }})
                                                    </option>
                                                @endforeach
                                            </x-form.select>
                                            <x-form.input-error :for="'items_' . $index . '_item_id'" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-form.label :for="'items_' . $index . '_quantity'" value="Quantity *" />
                                            <x-form.input :id="'items_' . $index . '_quantity'" :name="'items[' . $index . '][quantity]'" type="number" min="1" step="1" class="mt-1 block w-full" :value="old('items.' . $index . '.quantity', $item->quantity)" required />
                                            <x-form.input-error :for="'items_' . $index . '_quantity'" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-form.label :for="'items_' . $index . '_unit_price'" value="Unit Price" />
                                            <x-form.input :id="'items_' . $index . '_unit_price'" :name="'items[' . $index . '][unit_price]'" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('items.' . $index . '.unit_price', $item->unit_price)" />
                                            <x-form.input-error :for="'items_' . $index . '_unit_price'" class="mt-2" />
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <x-form.label :for="'items_' . $index . '_notes'" value="Notes" />
                                        <x-form.input :id="'items_' . $index . '_notes'" :name="'items[' . $index . '][notes]'" type="text" class="mt-1 block w-full" :value="old('items.' . $index . '.notes', $item->notes)" placeholder="Optional notes about this item" />
                                        <x-form.input-error :for="'items_' . $index . '_notes'" class="mt-2" />
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Items Template (hidden) -->
                        <template id="item-template">
                            <div class="item-row border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="text-sm font-medium text-gray-700">New Item</h4>
                                    <button type="button" class="remove-item-btn text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Item *</label>
                                        <select name="items[__INDEX__][item_id]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                            <option value="">Select Item</option>
                                            @foreach ($items as $availableItem)
                                                <option value="{{ $availableItem->id }}">
                                                    {{ $availableItem->name }} ({{ $availableItem->code ?? $availableItem->id }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Quantity *</label>
                                        <input type="number" name="items[__INDEX__][quantity]" min="1" step="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                                        <input type="number" name="items[__INDEX__][unit_price]" min="0" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <input type="text" name="items[__INDEX__][notes]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Optional notes about this item" />
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between space-x-3 mt-8 pt-8">
                    <a href="{{ route('inventory.transactions.show', $transaction) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        ← Cancel
                    </a>
                    <div class="space-x-3">
                        @if ($transaction->status === 'draft')
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Update Transaction
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let itemCount = {{ $transaction->items->count() }};

        document.getElementById('add-item-btn')?.addEventListener('click', function() {
            const template = document.getElementById('item-template');
            const container = document.getElementById('items-container');
            const clone = template.content.cloneNode(true);
            
            // Replace __INDEX__ with actual item count
            const html = new XMLSerializer().serializeToString(clone);
            const updatedHtml = html.replace(/__INDEX__/g, itemCount);
            
            container.insertAdjacentHTML('beforeend', updatedHtml);
            itemCount++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item-btn')) {
                e.target.closest('.item-row').remove();
            }
        });
    </script>
</x-app-layout>
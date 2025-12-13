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
                <span class="ml-2 text-sm font-normal text-gray-500">#{{ $transaction->reference }}</span>
            </h2>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $transaction->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $transaction->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ ucfirst($transaction->status) }}
                </span>
                <a href="{{ route('inventory.transactions.index') }}" class="text-gray-500 hover:text-gray-700">
                    ← Back to Transactions
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('inventory.transactions.update', $transaction) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Column: Transaction Details -->
                    <div class="lg:col-span-1 space-y-6">
                        
                        <!-- Transaction Type (Read-only) -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">📋 Transaction Type</h3>
                                <div class="flex items-center p-3 border rounded-lg bg-gray-50 border-gray-200">
                                    <span class="text-2xl mr-2">{{ $typeIcons[$transaction->type] ?? '📄' }}</span>
                                    <span class="font-medium">{{ $typeTitles[$transaction->type] ?? ucfirst($transaction->type) }}</span>
                                </div>
                                <input type="hidden" name="type" value="{{ $transaction->type }}">
                            </div>
                        </div>
                        
                        <!-- Basic Information -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">📝 Basic Information</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <x-form.label for="reference" value="Reference Number *" />
                                        <x-form.input id="reference" name="reference" type="text"
                                            class="mt-1 block w-full" 
                                            value="{{ old('reference', $transaction->reference) }}" 
                                            required placeholder="e.g., REC-2024-001" />
                                        <x-form.input-error for="reference" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="transaction_date" value="Transaction Date *" />
                                        <x-form.input id="transaction_date" name="transaction_date"
                                            type="datetime-local" class="mt-1 block w-full" 
                                            value="{{ old('transaction_date', \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d\TH:i')) }}"
                                            required />
                                        <x-form.input-error for="transaction_date" class="mt-2" />
                                    </div>

                                    <!-- Store Selection -->
                                    @if($transaction->type === 'transfer')
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <x-form.label for="from_store_id" value="From Store *" />
                                                <x-form.select id="from_store_id" name="from_store_id" class="mt-1 block w-full" required>
                                                    <option value="">Select Source Store</option>
                                                    @foreach ($stores as $store)
                                                        <option value="{{ $store->id }}"
                                                            {{ old('from_store_id', $transaction->from_store_id) == $store->id ? 'selected' : '' }}>
                                                            {{ $store->name }}
                                                        </option>
                                                    @endforeach
                                                </x-form.select>
                                                <x-form.input-error for="from_store_id" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-form.label for="to_store_id" value="To Store *" />
                                                <x-form.select id="to_store_id" name="to_store_id" class="mt-1 block w-full" required>
                                                    <option value="">Select Destination Store</option>
                                                    @foreach ($stores as $store)
                                                        <option value="{{ $store->id }}"
                                                            {{ old('to_store_id', $transaction->to_store_id) == $store->id ? 'selected' : '' }}>
                                                            {{ $store->name }}
                                                        </option>
                                                    @endforeach
                                                </x-form.select>
                                                <x-form.input-error for="to_store_id" class="mt-2" />
                                            </div>
                                        </div>
                                    @else
                                        <div>
                                            <x-form.label for="store_id" value="Store *" />
                                            <x-form.select id="store_id" name="store_id" class="mt-1 block w-full" required>
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
                                    @endif

                                    <!-- Type-specific fields -->
                                    @if($transaction->type === 'receipt')
                                        <div>
                                            <x-form.label for="supplier" value="Supplier/Vendor" />
                                            <x-form.input id="supplier" name="supplier" type="text"
                                                class="mt-1 block w-full" 
                                                value="{{ old('supplier', $transaction->supplier) }}"
                                                placeholder="e.g., Acme Supplies Inc." />
                                            <x-form.input-error for="supplier" class="mt-2" />
                                        </div>
                                    @endif

                                    @if($transaction->type === 'issue')
                                        <div>
                                            <x-form.label for="recipient" value="Recipient/Department" />
                                            <x-form.input id="recipient" name="recipient" type="text"
                                                class="mt-1 block w-full" 
                                                value="{{ old('recipient', $transaction->recipient) }}"
                                                placeholder="e.g., Production Department, John Doe" />
                                            <x-form.input-error for="recipient" class="mt-2" />
                                        </div>
                                    @endif

                                    @if($transaction->type === 'adjustment')
                                        <div>
                                            <x-form.label for="adjustment_reason" value="Adjustment Reason *" />
                                            <x-form.select id="adjustment_reason" name="adjustment_reason" class="mt-1 block w-full" required>
                                                <option value="">Select Reason</option>
                                                <option value="stock_count" {{ old('adjustment_reason', $transaction->adjustment_reason) == 'stock_count' ? 'selected' : '' }}>Stock Count</option>
                                                <option value="damaged" {{ old('adjustment_reason', $transaction->adjustment_reason) == 'damaged' ? 'selected' : '' }}>Damaged Goods</option>
                                                <option value="expired" {{ old('adjustment_reason', $transaction->adjustment_reason) == 'expired' ? 'selected' : '' }}>Expired Items</option>
                                                <option value="theft" {{ old('adjustment_reason', $transaction->adjustment_reason) == 'theft' ? 'selected' : '' }}>Theft/Loss</option>
                                                <option value="found" {{ old('adjustment_reason', $transaction->adjustment_reason) == 'found' ? 'selected' : '' }}>Found Items</option>
                                                <option value="other" {{ old('adjustment_reason', $transaction->adjustment_reason) == 'other' ? 'selected' : '' }}>Other</option>
                                            </x-form.select>
                                            <x-form.input-error for="adjustment_reason" class="mt-2" />
                                        </div>
                                    @endif

                                    <div>
                                        <x-form.label for="notes" value="Notes" />
                                        <x-form.textarea id="notes" name="notes" class="mt-1 block w-full" rows="3"
                                            placeholder="Add any additional notes about this transaction...">{{ old('notes', $transaction->notes) }}</x-form.textarea>
                                        <x-form.input-error for="notes" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Items Management -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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

                                <!-- Items Summary -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-600">Total Items:</span>
                                            <span id="total-items" class="font-bold text-gray-900 ml-1">0</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Total Quantity:</span>
                                            <span id="total-quantity" class="font-bold text-gray-900 ml-1">0</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Total Value:</span>
                                            <span id="total-value" class="font-bold text-gray-900 ml-1">0</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Avg Price:</span>
                                            <span id="avg-price" class="font-bold text-gray-900 ml-1">0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Items Container -->
                                <div id="items-container" class="space-y-4 max-h-96 overflow-y-auto">
                                    @if($transaction->items->count() > 0)
                                        @foreach ($transaction->items as $index => $item)
                                            <div class="item-row border border-gray-200 rounded-lg p-4 bg-gray-50" data-item-index="{{ $index }}">
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
                                                        <x-form.select :id="'items_' . $index . '_item_id'" :name="'items[' . $index . '][item_id]'" class="mt-1 block w-full item-select" required>
                                                            <option value="">Select Item</option>
                                                            @foreach ($items as $availableItem)
                                                                <option value="{{ $availableItem->id }}" 
                                                                    data-stock="{{ $availableItem->stock_quantity ?? 0 }}"
                                                                    {{ old('items.' . $index . '.item_id', $item->item_id) == $availableItem->id ? 'selected' : '' }}>
                                                                    {{ $availableItem->name }} ({{ $availableItem->code ?? $availableItem->id }})
                                                                </option>
                                                            @endforeach
                                                        </x-form.select>
                                                        <x-form.input-error :for="'items_' . $index . '_item_id'" class="mt-2" />
                                                    </div>
                                                    <div>
                                                        <x-form.label :for="'items_' . $index . '_quantity'" value="Quantity *" />
                                                        <x-form.input :id="'items_' . $index . '_quantity'" :name="'items[' . $index . '][quantity]'" type="number" min="1" step="1" class="mt-1 block w-full item-quantity" :value="old('items.' . $index . '.quantity', $item->quantity)" required />
                                                        <x-form.input-error :for="'items_' . $index . '_quantity'" class="mt-2" />
                                                    </div>
                                                    <div>
                                                        <x-form.label :for="'items_' . $index . '_unit_price'" value="Unit Price" />
                                                        <x-form.input :id="'items_' . $index . '_unit_price'" :name="'items[' . $index . '][unit_price]'" type="number" min="0" step="0.01" class="mt-1 block w-full item-price" :value="old('items.' . $index . '.unit_price', $item->unit_price)" />
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
                                    @endif
                                </div>

                                <!-- Items Template (hidden) -->
                                <template id="item-template">
                                    <div class="item-row border border-gray-200 rounded-lg p-4 bg-gray-50" data-item-index="__INDEX__">
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
                                                <select name="items[__INDEX__][item_id]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm item-select" required>
                                                    <option value="">Select Item</option>
                                                    @foreach ($items as $availableItem)
                                                        <option value="{{ $availableItem->id }}" data-stock="{{ $availableItem->stock_quantity ?? 0 }}">
                                                            {{ $availableItem->name }} ({{ $availableItem->code ?? $availableItem->id }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Quantity *</label>
                                                <input type="number" name="items[__INDEX__][quantity]" min="1" step="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm item-quantity" value="1" required />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                                                <input type="number" name="items[__INDEX__][unit_price]" min="0" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm item-price" value="0" />
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
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                            <div class="p-6 bg-white">
                                <div class="flex justify-between space-x-3">
                                    <a href="{{ route('inventory.transactions.show', $transaction) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        ← Cancel
                                    </a>
                                    <div class="space-x-3">
                                        @if($transaction->status === 'draft')
                                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Update Transaction
                                            </button>
                                            <button type="button" onclick="finalizeTransaction()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                ✅ Finalize Transaction
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden forms for finalize and cancel -->
    @if($transaction->status === 'draft')
    <form id="finalize-form" action="{{ route('inventory.transactions.finalize', $transaction) }}" method="POST" class="hidden">
        @csrf
    </form>
    <form id="cancel-form" action="{{ route('inventory.transactions.cancel', $transaction) }}" method="POST" class="hidden">
        @csrf
    </form>
    @endif

    <script>
        let itemCount = {{ $transaction->items->count() }};

        // Add item functionality
        document.getElementById('add-item-btn')?.addEventListener('click', function() {
            const template = document.getElementById('item-template');
            const container = document.getElementById('items-container');
            const clone = template.content.cloneNode(true);
            
            const html = new XMLSerializer().serializeToString(clone);
            const updatedHtml = html.replace(/__INDEX__/g, itemCount);
            
            container.insertAdjacentHTML('beforeend', updatedHtml);
            itemCount++;
            updateSummary();
        });

        // Remove item functionality
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item-btn')) {
                e.target.closest('.item-row').remove();
                updateSummary();
            }
        });

        // Update summary when items change
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-price')) {
                updateSummary();
            }
        });

        function updateSummary() {
            const itemRows = document.querySelectorAll('.item-row');
            let totalItems = itemRows.length;
            let totalQuantity = 0;
            let totalValue = 0;

            itemRows.forEach(row => {
                const quantity = parseFloat(row.querySelector('.item-quantity')?.value || 0);
                const price = parseFloat(row.querySelector('.item-price')?.value || 0);
                totalQuantity += quantity;
                totalValue += quantity * price;
            });

            document.getElementById('total-items').textContent = totalItems;
            document.getElementById('total-quantity').textContent = totalQuantity;
            document.getElementById('total-value').textContent = totalValue.toFixed(2);
            document.getElementById('avg-price').textContent = totalQuantity > 0 ? (totalValue / totalQuantity).toFixed(2) : '0.00';
        }

        // Finalize transaction
        function finalizeTransaction() {
            if (confirm('Are you sure you want to finalize this transaction? This will update inventory quantities and cannot be undone.')) {
                const finalizeForm = document.getElementById('finalize-form');
                if (finalizeForm) {
                    finalizeForm.submit();
                } else {
                    alert('Error: Finalize form not found. Please refresh the page and try again.');
                }
            }
        }

        // Initialize summary on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSummary();
        });
    </script>
</x-app-layout>
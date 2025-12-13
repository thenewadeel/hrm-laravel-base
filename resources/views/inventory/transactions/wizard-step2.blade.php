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
                {{ $typeIcons[$type] ?? '📋' }} {{ $typeTitles[$type] ?? 'New Transaction' }} • Step 2 of 3
            </h2>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center">1</div>
                    <span>Basic Info</span>
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center">2</div>
                    <span>Add Items</span>
                    <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center">3</div>
                    <span>Review</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Transaction Summary -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-medium text-blue-900 mb-2">Transaction Summary</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-blue-700 font-medium">Type:</span>
                        <span class="text-blue-900">{{ $typeTitles[$type] }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700 font-medium">Reference:</span>
                        <span class="text-blue-900">{{ $wizardData['reference'] }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700 font-medium">Date:</span>
                        <span class="text-blue-900">{{ \Carbon\Carbon::parse($wizardData['transaction_date'])->format('M d, Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700 font-medium">Store:</span>
                        @if($type === 'transfer')
                            <span class="text-blue-900">{{ $stores->find($wizardData['from_store_id'])->name }} → {{ $stores->find($wizardData['to_store_id'])->name }}</span>
                        @else
                            <span class="text-blue-900">{{ $stores->find($wizardData['store_id'])->name }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Add Items Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('inventory.transactions.wizard.step2.submit') }}" method="POST" id="items-form">
                        @csrf
                        <input type="hidden" name="step" value="2">

                        <div class="space-y-6">
                            <!-- Items Section -->
                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">📦 Add Items</h3>
                                    <button type="button" id="add-item-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add Item
                                    </button>
                                </div>

                                <!-- Items Container -->
                                <div id="items-container" class="space-y-4">
                                    <!-- Initial Item Row -->
                                    <div class="item-row border border-gray-200 rounded-lg p-4 bg-gray-50">
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div class="md:col-span-2">
                                                <x-form.label for="items_0_item_id" value="Item *" />
                                                <x-form.select id="items_0_item_id" name="items[0][item_id]" class="mt-1 block w-full" required>
                                                    <option value="">Select Item</option>
                                                    @foreach ($items as $item)
                                                        <option value="{{ $item->id }}" data-stock="{{ $item->stock_quantity ?? 0 }}">
                                                            {{ $item->name }} ({{ $item->code ?? $item->id }})
                                                        </option>
                                                    @endforeach
                                                </x-form.select>
                                                <x-form.input-error for="items_0_item_id" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-form.label for="items_0_quantity" value="Quantity *" />
                                                <x-form.input id="items_0_quantity" name="items[0][quantity]" type="number" min="1" step="1" class="mt-1 block w-full" required />
                                                <x-form.input-error for="items_0_quantity" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-form.label for="items_0_unit_price" value="Unit Price" />
                                                <x-form.input id="items_0_unit_price" name="items[0][unit_price]" type="number" min="0" step="0.01" class="mt-1 block w-full" />
                                                <x-form.input-error for="items_0_unit_price" class="mt-2" />
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <x-form.label for="items_0_notes" value="Notes" />
                                            <x-form.input id="items_0_notes" name="items[0][notes]" type="text" class="mt-1 block w-full" placeholder="Optional notes about this item" />
                                            <x-form.input-error for="items_0_notes" class="mt-2" />
                                        </div>
                                    </div>
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
                                                    @foreach ($items as $item)
                                                        <option value="{{ $item->id }}" data-stock="{{ $item->stock_quantity ?? 0 }}">
                                                            {{ $item->name }} ({{ $item->code ?? $item->id }})
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
                        <div class="flex justify-between space-x-3 mt-8 pt-8 border-t border-gray-200">
                            <a href="{{ route('inventory.transactions.wizard', ['type' => $type]) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                ← Back to Basic Info
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Create Transaction →
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let itemCount = 1;

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
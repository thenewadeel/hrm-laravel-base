<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-primary leading-tight">
                ⚙️ {{ __('Stock Adjustment') }}
            </h2>
            <div class="flex space-x-3">
                <x-button.outline href="{{ route('inventory.index') }}">
                    <x-heroicon-s-arrow-left class="w-4 h-4 mr-2" />
                    Back to Inventory
                </x-button.outline>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Adjustment Form -->
            <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 surface border-b border-secondary">
                    <form action="{{ route('inventory.stock.process-adjustment') }}" method="POST" x-data="{ 
                        items: [],
                        totalAdjustments: 0,
                        
                        addItem() {
                            this.items.push({
                                id: Date.now(),
                                item_id: null,
                                current_quantity: 0,
                                adjustment_quantity: 0,
                                new_quantity: 0,
                                reason: '',
                                notes: ''
                            });
                        },
                        
                        removeItem(index) {
                            this.items.splice(index, 1);
                            this.calculateTotal();
                        },
                        
                        updateItemQuantity(index) {
                            const item = this.items[index];
                            const current = parseFloat(item.current_quantity) || 0;
                            const adjustment = parseFloat(item.adjustment_quantity) || 0;
                            item.new_quantity = current + adjustment;
                            this.calculateTotal();
                        },
                        
                        calculateTotal() {
                            this.totalAdjustments = this.items.reduce((sum, item) => {
                                return sum + (parseFloat(item.adjustment_quantity) || 0);
                            }, 0);
                        },
                        
                        loadItemDetails(item, event) {
                            const itemId = event.target.value;
                            if (!itemId) return;
                            
                            // Fetch item details via API
                            fetch(`/api/inventory/items/${itemId}/details`)
                                .then(response => response.json())
                                .then(data => {
                                    item.current_quantity = data.quantity || 0;
                                    this.updateItemQuantity(this.items.indexOf(item));
                                });
                        }
                    }">
                        @csrf
                        @method('POST')

                        <!-- Store Selection -->
                        <div class="mb-6">
                            <x-form-group label="Store" for="store_id" required>
                                <x-form.select 
                                    id="store_id" 
                                    name="store_id" 
                                    required
                                >
                                    <option value="">Select store for adjustment</option>
                                    @foreach($stores ?? [] as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </x-form.select>
                                <x-form.help text="Select the store where the adjustment will be made." />
                            </x-form-group>
                        </div>

                        <!-- Adjustment Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <x-form-group label="Adjustment Date" for="adjustment_date" required>
                                <x-form.date 
                                    id="adjustment_date" 
                                    name="adjustment_date" 
                                    required
                                    :value="now()->format('Y-m-d')"
                                />
                            </x-form-group>

                            <x-form-group label="Adjustment Type" for="adjustment_type" required>
                                <x-form.select 
                                    id="adjustment_type" 
                                    name="adjustment_type" 
                                    required
                                >
                                    <option value="">Select adjustment reason</option>
                                    <option value="increase">Stock In (Increase)</option>
                                    <option value="decrease">Stock Out (Decrease)</option>
                                    <option value="damage">Damage/Loss</option>
                                    <option value="expiry">Expiry</option>
                                    <option value="theft">Theft</option>
                                    <option value="correction">System Correction</option>
                                    <option value="other">Other</option>
                                </x-form.select>
                            </x-form-group>
                        </div>

                        <!-- Reference Number -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <x-form-group label="Reference Number" for="reference">
                                <x-form.input 
                                    id="reference" 
                                    name="reference" 
                                    placeholder="e.g., ADJ-2024-001"
                                    :value="'ADJ-' . now()->format('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)"
                                />
                                <x-form.help text="Auto-generated reference for this adjustment." />
                            </x-form-group>

                            <x-form-group label="Approval Required?" for="requires_approval">
                                <x-form.select 
                                    id="requires_approval" 
                                    name="requires_approval"
                                >
                                    <option value="0" selected>No Approval Required</option>
                                    <option value="1">Manager Approval Required</option>
                                    <option value="2">Director Approval Required</option>
                                </x-form.select>
                            </x-form-group>
                        </div>

                        <!-- Items Adjustment Section -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-primary">Items to Adjust</h3>
                                <x-button.secondary type="button" @click="addItem()" x-bind:disabled="items.length >= 20">
                                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                                    Add Item
                                </x-button.secondary>
                            </div>

                            <!-- Items Container -->
                            <div class="space-y-4" x-show="items.length > 0">
                                <template x-for="(item, index) in items" :key="item.id">
                                    <div class="border border-secondary rounded-lg p-4 bg-gray-50">
                                        <div class="flex justify-between items-center mb-4">
                                            <h4 class="font-medium text-primary">Item #<span x-text="index + 1"></span></h4>
                                            <x-button.danger 
                                                type="button" 
                                                size="sm" 
                                                @click="removeItem(index)"
                                                x-show="items.length > 1"
                                            >
                                                <x-heroicon-s-trash class="w-4 h-4" />
                                            </x-button.danger>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                            <!-- Item Selection -->
                                            <div class="lg:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                                                <select 
                                                    x-model="item.item_id"
                                                    @change="loadItemDetails(item, $event)"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    required
                                                >
                                                    <option value="">Select an item</option>
                                                    @foreach($items ?? [] as $item_option)
                                                        <option value="{{ $item_option->id }}">{{ $item_option->name }} ({{ $item_option->sku }})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Current Quantity -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Quantity</label>
                                                <input 
                                                    type="number" 
                                                    x-model="item.current_quantity"
                                                    readonly
                                                    class="w-full rounded-md border-gray-300 bg-gray-100"
                                                />
                                            </div>

                                            <!-- Adjustment Quantity -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Quantity</label>
                                                <input 
                                                    type="number" 
                                                    x-model="item.adjustment_quantity"
                                                    @input="updateItemQuantity(index)"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    step="0.01"
                                                    placeholder="+ or -"
                                                    required
                                                />
                                                <p class="text-xs text-gray-500 mt-1">Use + to increase, - to decrease</p>
                                            </div>

                                            <!-- New Quantity -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">New Quantity</label>
                                                <input 
                                                    type="number" 
                                                    :value="item.new_quantity"
                                                    readonly
                                                    class="w-full rounded-md border-gray-300 bg-blue-50"
                                                />
                                            </div>

                                            <!-- Adjustment Reason -->
                                            <div class="lg:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Specific Reason</label>
                                                <input 
                                                    type="text" 
                                                    x-model="item.reason"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    placeholder="e.g., Damaged in transit, Expired items, System correction"
                                                />
                                            </div>

                                            <!-- Item Notes -->
                                            <div class="lg:col-span-1">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                                <textarea 
                                                    x-model="item.notes"
                                                    rows="2"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    placeholder="Additional notes for this item adjustment"
                                                ></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Empty State -->
                            <div x-show="items.length === 0" class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                <x-heroicon-s-cube class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No items added</h3>
                                <p class="mt-1 text-sm text-gray-500">Add items to adjust their stock quantities.</p>
                                <div class="mt-6">
                                    <x-button.secondary type="button" @click="addItem()">
                                        <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                                        Add First Item
                                    </x-button.secondary>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Section -->
                        <div x-show="items.length > 0" class="mb-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="font-medium text-blue-900 mb-2">Adjustment Summary</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-blue-700">Total Items:</span>
                                        <span class="font-medium text-blue-900 ml-2" x-text="items.length"></span>
                                    </div>
                                    <div>
                                        <span class="text-blue-700">Total Quantity Change:</span>
                                        <span class="font-medium text-blue-900 ml-2" x-text="totalAdjustments"></span>
                                    </div>
                                    <div>
                                        <span class="text-blue-700">Adjustment Type:</span>
                                        <span class="font-medium text-blue-900 ml-2" x-text="document.getElementById('adjustment_type').value || 'Not selected'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Overall Notes -->
                        <div class="mb-6">
                            <x-form-group label="Adjustment Notes" for="notes">
                                <textarea 
                                    id="notes" 
                                    name="notes" 
                                    rows="4"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Enter detailed notes about this adjustment for audit trail purposes..."
                                ></textarea>
                                <x-form.help text="These notes will be recorded in the audit trail for compliance and tracking purposes." />
                            </x-form-group>
                        </div>

                        <!-- Hidden Items Data -->
                        <input type="hidden" name="items_data" :value="JSON.stringify(items)" x-show="false">

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 pt-6 border-t border-secondary">
                            <x-button.outline type="button" onclick="window.history.back()">
                                Cancel
                            </x-button.outline>
                            
                            <x-button.primary 
                                type="submit" 
                                x-bind:disabled="items.length === 0 || !document.getElementById('store_id').value || !document.getElementById('adjustment_type').value"
                                x-bind:class="{ 'opacity-50 cursor-not-allowed': items.length === 0 || !document.getElementById('store_id').value || !document.getElementById('adjustment_type').value }"
                            >
                                <x-heroicon-s-check class="w-4 h-4 mr-2" />
                                Process Adjustment
                            </x-button.primary>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Adjustments -->
            @if (isset($recentAdjustments) && $recentAdjustments->count() > 0)
                <div class="mt-8 surface overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 surface border-b border-secondary">
                        <h3 class="text-lg font-semibold text-primary mb-4">Recent Stock Adjustments</h3>
                        <div class="space-y-3">
                            @foreach($recentAdjustments as $adjustment)
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $adjustment->reference }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $adjustment->store->name ?? 'Unknown Store' }} • 
                                            {{ $adjustment->adjustment_type }} •
                                            {{ $adjustment->items_count }} items
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <x-badge :status="$adjustment->status" />
                                        <p class="text-sm text-gray-500">
                                            {{ $adjustment->created_at->diffForHumans() }}</p>
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
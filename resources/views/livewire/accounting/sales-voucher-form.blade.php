<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Create Sales Voucher</h3>
    
    <form wire:submit="createSalesVoucher">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Entry Date</label>
                <input type="date" wire:model.live="entry_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('entry_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <select wire:model.live="customer_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                @error('customer_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
                <input type="text" wire:model.live="invoice_number" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('invoice_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input type="date" wire:model.live="due_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input type="text" wire:model.live="description" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        
        <!-- Line Items -->
        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <label class="block text-sm font-medium text-gray-700">Line Items</label>
                <button type="button" wire:click="addLineItem" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                    Add Item
                </button>
            </div>
            
            <div class="space-y-2">
                @foreach($line_items as $index => $item)
                    <div class="flex gap-2 items-center">
                        <input type="text" wire:model.live="line_items.{{ $index }}.description" 
                               placeholder="Description" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="number" wire:model.live="line_items.{{ $index }}.quantity" 
                               step="0.01" placeholder="Qty" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="number" wire:model.live="line_items.{{ $index }}.unit_price" 
                               step="0.01" placeholder="Price" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <button type="button" wire:click="removeLineItem({{ $index }})" 
                                class="px-2 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600">
                            Remove
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Totals -->
        <div class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal</label>
                    <div class="text-lg font-semibold">{{ number_format($this->calculateSubtotal(), 2) }}</div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax Amount</label>
                    <input type="number" wire:model.live="tax_amount" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('tax_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                    <div class="text-lg font-bold text-blue-600">{{ number_format($this->calculateTotal(), 2) }}</div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Create Sales Voucher
            </button>
        </div>
    </form>
</div>

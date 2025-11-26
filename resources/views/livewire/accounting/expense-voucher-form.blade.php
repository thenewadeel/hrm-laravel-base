<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Create Expense Voucher</h3>
    
    <form wire:submit="createExpenseVoucher">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Entry Date</label>
                <input type="date" wire:model.live="entry_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('entry_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expense Account</label>
                <select wire:model.live="expense_account_code" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Expense Account</option>
                    @foreach($expenseAccounts as $account)
                        <option value="{{ $account->code }}">{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
                @error('expense_account_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                <input type="number" wire:model.live="amount" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                <input type="text" wire:model.live="reference" placeholder="Invoice #, Receipt #, etc." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('reference') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input type="text" wire:model.live="description" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model.live="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Additional notes or details..."></textarea>
            @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        
        <!-- Amount Summary -->
        <div class="mb-4 p-4 bg-gray-50 rounded">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Total Expense Amount:</span>
                <span class="text-lg font-bold text-red-600">{{ number_format($this->amount, 2) }}</span>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Create Expense Voucher
            </button>
        </div>
    </form>
</div>

<div>
    <form wire:submit="save" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tax Rate Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           wire:model="taxRate.name" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           required>
                    @error('taxRate.name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tax Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="code" 
                           wire:model="taxRate.code" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           required>
                    @error('taxRate.code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tax Type <span class="text-red-500">*</span>
                    </label>
                    <select id="type" 
                            wire:model="taxRate.type" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                        <option value="">Select Tax Type</option>
                        @foreach($taxTypes as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('taxRate.type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tax Rate (%) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="rate" 
                           wire:model="taxRate.rate" 
                           step="0.0001"
                           min="0"
                           max="100"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           required>
                    @error('taxRate.rate')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tax_jurisdiction_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tax Jurisdiction
                    </label>
                    <select id="tax_jurisdiction_id" 
                            wire:model="taxRate.tax_jurisdiction_id" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Jurisdiction</option>
                        @foreach($jurisdictions as $jurisdiction)
                            <option value="{{ $jurisdiction->id }}">{{ $jurisdiction->name }} ({{ $jurisdiction->getTypeDisplayName() }})</option>
                        @endforeach
                    </select>
                    @error('taxRate.tax_jurisdiction_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="gl_account_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        GL Account Code
                    </label>
                    <input type="text" 
                           id="gl_account_code" 
                           wire:model="taxRate.gl_account_code" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    @error('taxRate.gl_account_code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Description
                </label>
                <textarea id="description" 
                          wire:model="taxRate.description" 
                          rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                @error('taxRate.description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Configuration -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Configuration</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="effective_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Effective Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="effective_date" 
                           wire:model="taxRate.effective_date" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           required>
                    @error('taxRate.effective_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        End Date
                    </label>
                    <input type="date" 
                           id="end_date" 
                           wire:model="taxRate.end_date" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    @error('taxRate.end_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           wire:model="taxRate.is_active" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        Active
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_compound" 
                           wire:model="taxRate.is_compound" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_compound" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        Compound Tax (applied on amount after other taxes)
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('accounting.tax-rates.index') }}" 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{ $taxRate->exists ? 'Update' : 'Create' }} Tax Rate
            </button>
        </div>
    </form>
</div>

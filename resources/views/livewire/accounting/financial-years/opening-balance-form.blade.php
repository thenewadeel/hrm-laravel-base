<div>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Opening Balances
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Set opening balances for {{ $financialYear->name }} ({{ $financialYear->start_date->format('M d, Y') }} - {{ $financialYear->end_date->format('M d, Y') }})
                </p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('accounting.financial-years.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Financial Years
                </a>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Filter by Account Type
                    </label>
                    <select wire:model.live="account_type_filter" 
                            class="w-full md:w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach($accountTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button wire:click.prevent="loadBalances" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Opening Balances Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <form wire:submit.prevent="save" class="p-6">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Enter opening balances for each account. Only accounts with non-zero balances need to be entered.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Account Code
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Account Name
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Debit Amount
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Credit Amount
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Description
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($balances as $index => $balance)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $balance['account_code'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $balance['account_name'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($balance['account_type'] === 'asset')
                                                bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @elseif($balance['account_type'] === 'liability')
                                                bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @elseif($balance['account_type'] === 'equity')
                                                bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($balance['account_type'] === 'revenue')
                                                bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                            @elseif($balance['account_type'] === 'expense')
                                                bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @endif">
                                            {{ ucfirst($balance['account_type']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <input type="number" 
                                               step="0.01"
                                               min="0"
                                               wire:model="balances.{{ $index }}.debit_amount"
                                               class="w-24 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm @error('balances.' . $index . '.debit_amount') border-red-500 @enderror">
                                        @error('balances.' . $index . '.debit_amount')
                                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <input type="number" 
                                               step="0.01"
                                               min="0"
                                               wire:model="balances.{{ $index }}.credit_amount"
                                               class="w-24 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm @error('balances.' . $index . '.credit_amount') border-red-500 @enderror">
                                        @error('balances.' . $index . '.credit_amount')
                                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <input type="text" 
                                               wire:model="balances.{{ $index }}.description"
                                               class="w-48 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm @error('balances.' . $index . '.description') border-red-500 @enderror"
                                               placeholder="Optional description">
                                        @error('balances.' . $index . '.description')
                                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No accounts found for the selected filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                @if(count($balances) > 0)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Debits</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ number_format(collect($balances)->sum('debit_amount'), 2) }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Credits</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ number_format(collect($balances)->sum('credit_amount'), 2) }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Difference</p>
                                <p class="text-lg font-semibold 
                                    @if(abs(collect($balances)->sum('debit_amount') - collect($balances)->sum('credit_amount')) > 0.01)
                                        text-red-600 dark:text-red-400
                                    @else
                                        text-green-600 dark:text-green-400
                                    @endif">
                                    {{ number_format(collect($balances)->sum('debit_amount') - collect($balances)->sum('credit_amount'), 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('accounting.financial-years.index') }}" 
                       class="px-6 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-medium rounded-lg transition-colors">
                        <span wire:loading.remove>
                            Save Opening Balances
                        </span>
                        <span wire:loading>
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

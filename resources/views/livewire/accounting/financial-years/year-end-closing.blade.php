<div>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Year-End Closing
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Close {{ $financialYear->name }} and generate closing entries
                </p>
            </div>
            <a href="{{ route('accounting.financial-years.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Financial Years
            </a>
        </div>

        <!-- Financial Year Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Financial Year</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $financialYear->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Period</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $financialYear->start_date->format('M d, Y') }} - {{ $financialYear->end_date->format('M d, Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                        @if($financialYear->status === 'active')
                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @else
                            bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                        @endif">
                        {{ ucfirst($financialYear->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        @if(!$closingSummary)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">Preview Closing Summary</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Click the button below to preview the year-end closing summary before proceeding.
                    </p>
                    <div class="mt-6">
                        <button wire:click.prevent="previewClosing" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-medium rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span wire:loading.remove>Preview Closing Summary</span>
                            <span wire:loading>
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Generating...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Closing Summary -->
        @if($closingSummary)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Closing Summary</h3>
                    
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <p class="text-sm text-green-600 dark:text-green-400">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                                {{ number_format($closingSummary['total_revenue'], 2) }}
                            </p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                            <p class="text-sm text-red-600 dark:text-red-400">Total Expenses</p>
                            <p class="text-2xl font-bold text-red-900 dark:text-red-100">
                                {{ number_format($closingSummary['total_expenses'], 2) }}
                            </p>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                            <p class="text-sm text-blue-600 dark:text-blue-400">Net Income</p>
                            <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                {{ number_format($closingSummary['net_income'], 2) }}
                            </p>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                            <p class="text-sm text-purple-600 dark:text-purple-400">Total Assets</p>
                            <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                                {{ number_format($closingSummary['total_assets'], 2) }}
                            </p>
                        </div>
                    </div>

                    <!-- Trial Balance Preview -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Trial Balance Preview</h4>
                        <div class="overflow-x-auto max-h-64 overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Account</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Debit</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Credit</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($closingSummary['trial_balance'] as $account)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-900 dark:text-white">
                                                {{ $account['account']->code }} - {{ $account['account']->name }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-900 dark:text-white">
                                                {{ number_format($account['total_debit'], 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-900 dark:text-white">
                                                {{ number_format($account['total_credit'], 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <span class="inline-flex items-center">
                                                    {{ number_format(abs($account['closing_balance']), 2) }}
                                                    <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $account['balance_type'] }}
                                                    </span>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Carry Forward Options -->
                    @if($availableFinancialYears->count() > 0)
                        <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Carry Forward Balances</h4>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                        You can carry forward balance sheet account balances to the next financial year.
                                    </p>
                                    <div class="mt-3 flex items-center space-x-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" wire:model="carry_forward" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                            <span class="ml-2 text-sm text-yellow-800 dark:text-yellow-200">Carry forward balances</span>
                                        </label>
                                        @if($carry_forward)
                                            <select wire:model="newFinancialYearId" 
                                                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                                <option value="">Select target financial year</option>
                                                @foreach($availableFinancialYears as $fy)
                                                    <option value="{{ $fy->id }}">{{ $fy->name }} ({{ $fy->start_date->format('Y') }})</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Confirmation -->
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-red-800 dark:text-red-200">Important Notice</h4>
                                <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                    Year-end closing will create closing journal entries and mark this financial year as closed. This action cannot be undone.
                                </p>
                                <div class="mt-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="confirmClose" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        <span class="ml-2 text-sm text-red-800 dark:text-red-200">I understand this action cannot be undone</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between">
                        <button wire:click.prevent="$set('closingSummary', null)" 
                                class="px-6 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                            Back to Preview
                        </button>
                        <button wire:click.prevent="closeFinancialYear" 
                                wire:loading.attr="disabled"
                                :disabled="!{{ $confirmClose }}"
                                class="px-6 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors">
                            <span wire:loading.remove>
                                Close Financial Year
                            </span>
                            <span wire:loading>
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Closing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

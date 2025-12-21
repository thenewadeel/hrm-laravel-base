<div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Bank Statements</h2>
            <p class="text-gray-600 dark:text-gray-400">Manage and reconcile bank statements</p>
        </div>
        <a href="{{ route('accounting.bank-statements.import') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
            Import Statement
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <input wire:model.live="search" 
                   type="text" 
                   placeholder="Search statements..." 
                   class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            
            <select wire:model.live="filterBankAccount" 
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All Accounts</option>
                @foreach($bankAccounts as $bankAccount)
                    <option value="{{ $bankAccount->id }}">{{ $bankAccount->account_name }} ({{ $bankAccount->bank_name }})</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus" 
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All Statuses</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterDateRange" 
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Custom Range</option>
                <option value="current_month">Current Month</option>
                <option value="last_month">Last Month</option>
                <option value="current_year">Current Year</option>
                <option value="last_30_days">Last 30 Days</option>
            </select>

            <input wire:model.live="startDate" 
                   type="date" 
                   class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">

            <input wire:model.live="endDate" 
                   type="date" 
                   class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        </div>

        <div class="flex justify-between items-center mt-4">
            <div class="flex gap-2">
                <button wire:click="resetFilters" 
                        class="px-3 py-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    Reset Filters
                </button>
                <select wire:model.live="perPage" 
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Bank Statements Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Statement Details
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Bank Account
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Balance
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Transactions
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($bankStatements as $bankStatement)
                        <tr wire:key="bank-statement-{{ $bankStatement->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $bankStatement->statement_number }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $bankStatement->statement_date->format('M j, Y') }}
                                    </div>
                                    @if($bankStatement->notes)
                                        <div class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $bankStatement->notes }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $bankStatement->bankAccount->account_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $bankStatement->bankAccount->bank_name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    ${{ number_format($bankStatement->closing_balance, 2) }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Closing Balance
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($bankStatement->bankTransactions->isNotEmpty())
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $bankStatement->bankTransactions->count() }} transactions
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Total: ${{ number_format($bankStatement->bankTransactions->sum('amount'), 2) }}
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        No transactions
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $bankStatement->status === 'reconciled' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $bankStatement->status === 'imported' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                    {{ $bankStatement->status === 'partial' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}">
                                    {{ $statuses[$bankStatement->status] ?? $bankStatement->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('accounting.bank-reconciliation.reconcile', [$bankStatement->bank_account_id, $bankStatement->id]) }}" 
                                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Reconcile
                                    </a>
                                    @if($bankStatement->status !== 'reconciled')
                                        <button wire:click="markAsReconciled({{ $bankStatement->id }})" 
                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                            Mark Reconciled
                                        </button>
                                    @endif
                                    <button wire:click="deleteBankStatement({{ $bankStatement->id }})" 
                                            wire:confirm="Are you sure you want to delete this bank statement?"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No bank statements found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($bankStatements->hasPages())
        <div class="mt-6">
            {{ $bankStatements->links() }}
        </div>
    @endif
</div>
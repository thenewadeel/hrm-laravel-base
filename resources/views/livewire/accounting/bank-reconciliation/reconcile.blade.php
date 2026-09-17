<div>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Bank Reconciliation</h2>
            <div class="flex gap-2">
                <button wire:click="startReconciliation" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">Start Reconciliation</button>
                <button wire:click="autoMatchTransactions" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">Auto-Match</button>
            </div>
        </div>

        {{-- Account Selection --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Account</label>
                    <select wire:model.live="bankAccountId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="">Select bank account...</option>
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Statement</label>
                    <select wire:model.live="bankStatementId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="">Latest (no statement)</option>
                        @foreach($bankStatements as $statement)
                            <option value="{{ $statement->id }}">Statement {{ $statement->statement_date?->format('M d, Y') }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Balance Summary --}}
        @if($bankAccountId)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Statement Balance</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">${{ number_format($statementBalance ?? 0, 2) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Book Balance</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">${{ number_format($bookBalance ?? 0, 2) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Outstanding Deposits</div>
                    <div class="text-xl font-bold text-green-600 dark:text-green-400">${{ number_format($outstandingDeposits, 2) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Outstanding Withdrawals</div>
                    <div class="text-xl font-bold text-red-600 dark:text-red-400">${{ number_format($outstandingWithdrawals, 2) }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Difference</div>
                    <div class="text-2xl font-bold {{ abs($difference) <= 0.01 ? 'text-green-600 dark:text-green-400' : ($difference > 0 ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400') }}">
                        ${{ number_format($difference, 2) }}
                    </div>
                </div>
                @if(abs($difference) > 0.01)
                    <div class="mt-2 text-xs text-red-600 dark:text-red-400">
                        Difference must be reconciled before completing. Match pending transactions below.
                    </div>
                @endif
            </div>
        @endif

        {{-- Pending Transactions --}}
        @if($bankAccountId && $pendingTransactions->count())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Transactions ({{ $pendingTransactions->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($pendingTransactions as $transaction)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $transaction->transaction_date?->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $transaction->description }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $transaction->transaction_type === 'debit' ? 'Debit' : 'Credit' }}</td>
                                    <td class="px-6 py-4 text-sm text-right {{ $transaction->transaction_type === 'debit' ? 'text-red-600' : 'text-green-600' }}">${{ number_format($transaction->amount, 2) }}</td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <button wire:click="openMatchModal({{ $transaction->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">Match</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- All Transactions --}}
        @if($bankAccountId)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Account Transactions</h3>
                    <button wire:click="completeReconciliation" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">Complete Reconciliation</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Matched</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($bankTransactions as $transaction)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $transaction->transaction_date?->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $transaction->description }}</td>
                                    <td class="px-6 py-4 text-sm text-right {{ $transaction->transaction_type === 'debit' ? 'text-red-600' : 'text-green-600' }}">${{ number_format($transaction->amount, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $transaction->status === 'reconciled' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        {{ $transaction->matchedLedgerEntry ? 'Yes' : 'No' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        @if($transaction->matchedLedgerEntry && $transaction->status !== 'reconciled')
                                            <button wire:click="unmatchTransaction({{ $transaction->id }})" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400">Unmatch</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No transactions for this account.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($bankTransactions->hasPages())
                    <div class="bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $bankTransactions->links() }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Notes --}}
        @if($bankAccountId)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reconciliation Notes</label>
                <textarea wire:model="reconciliationNotes" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"></textarea>
            </div>
        @endif
    </div>

    {{-- Match Modal --}}
    @if($showMatchModal && $currentBankTransaction)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Match Transaction</h3>
                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">{{ $currentBankTransaction->description }}</span>
                            &mdash; ${{ number_format($currentBankTransaction->amount, 2) }}
                            ({{ $currentBankTransaction->transaction_type }})
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $currentBankTransaction->transaction_date?->format('M d, Y') }}
                        </div>
                    </div>

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Ledger Entry</label>
                    <select wire:model.live="selectedLedgerEntryId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="">Select entry...</option>
                        @foreach($availableLedgerEntries as $entry)
                            <option value="{{ $entry->id }}">
                                {{ $entry->entry_date?->format('M d, Y') }} - ${{ number_format($entry->amount, 2) }} - {{ $entry->account?->name ?? 'General' }}
                            </option>
                        @endforeach
                    </select>
                    @if(!$availableLedgerEntries->count())
                        <div class="mt-2 text-sm text-yellow-600 dark:text-yellow-400">No matching ledger entries found. Try auto-match or resolve manually.</div>
                    @endif

                    <div class="flex justify-end gap-3 pt-4">
                        <button wire:click="closeModal" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Cancel</button>
                        <button wire:click="matchTransaction" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Match Transaction</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-message', ([payload]) => {
                const { message, type } = typeof payload === 'object' ? payload : { message: payload, type: 'info' };
                const toast = document.createElement('div');
                const colors = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
                toast.className = `fixed top-4 right-4 z-50 px-4 py-3 text-white rounded-lg shadow-lg ${colors}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
            });
        });
    </script>
</div>
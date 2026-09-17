<div>
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Import Bank Statement</h2>
            <button wire:click="cancel" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">&larr; Back</button>
        </div>

        <form wire:submit="importStatement" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Account *</label>
                    <select wire:model="bankAccountId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select account...</option>
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_name }} ({{ $account->account_number }})</option>
                        @endforeach
                    </select>
                    @error('bankAccountId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statement Number *</label>
                    <input type="text" wire:model="statementNumber" placeholder="e.g. STMT-2026-001" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                    @error('statementNumber') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statement File (CSV, XLSX, XLS) *</label>
                    <input type="file" wire:model="statementFile" accept=".csv,.txt,.xlsx,.xls"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-blue-700" />
                    @error('statementFile') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    @if($isImporting)
                        <div class="mt-2 text-sm text-blue-600 dark:text-blue-400" wire:loading.remove wire:target="statementFile">
                            Processing file...
                        </div>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statement Date *</label>
                    <input type="date" wire:model="statementDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" />
                    @error('statementDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period Start *</label>
                    <input type="date" wire:model="periodStartDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" />
                    @error('periodStartDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period End *</label>
                    <input type="date" wire:model="periodEndDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" />
                    @error('periodEndDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Opening Balance *</label>
                    <input type="number" wire:model="openingBalance" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" />
                    @error('openingBalance') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Closing Balance *</label>
                    <input type="number" wire:model="closingBalance" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" />
                    @error('closingBalance') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"></textarea>
            </div>

            {{-- Import Preview --}}
            @if($showPreview && count($importPreview))
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Preview (first {{ count($importPreview) }} of total transactions)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Description</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($importPreview as $txn)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $txn['transaction_date'] }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $txn['description'] }}</td>
                                        <td class="px-4 py-2 text-sm text-right {{ $txn['transaction_type'] === 'debit' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                            {{ $txn['transaction_type'] === 'debit' ? '-' : '+' }}${{ number_format($txn['amount'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" wire:click="cancel" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Import Statement</button>
            </div>
        </form>
    </div>

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
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 shadow-sm border-b border-slate-200 dark:border-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Cash Management Demo</h1>
                    <p class="mt-2 text-slate-600 dark:text-slate-400">Manage cash receipts and payments with double-entry accounting</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button 
                        wire:click="setMode('receipt')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors {{ $mode === 'receipt' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}"
                    >
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Cash Receipt
                    </button>
                    <button 
                        wire:click="setMode('payment')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors {{ $mode === 'payment' ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}"
                    >
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                        Cash Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Section -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                    <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">
                            {{ $mode === 'receipt' ? 'Create Cash Receipt' : 'Create Cash Payment' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            {{ $mode === 'receipt' ? 'Record money received from customers or other sources' : 'Record money paid to vendors or expenses' }}
                        </p>
                    </div>

                    <div class="p-6">
                        <!-- Success/Error Messages -->
                        @if(session()->has('receipt_success'))
                            <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                                <p class="text-emerald-800 dark:text-emerald-200">{{ session('receipt_success') }}</p>
                            </div>
                        @endif

                        @if(session()->has('payment_success'))
                            <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                                <p class="text-emerald-800 dark:text-emerald-200">{{ session('payment_success') }}</p>
                            </div>
                        @endif

                        @if(session()->has('receipt_error'))
                            <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg">
                                <p class="text-rose-800 dark:text-rose-200">{{ session('receipt_error') }}</p>
                            </div>
                        @endif

                        @if(session()->has('payment_error'))
                            <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg">
                                <p class="text-rose-800 dark:text-rose-200">{{ session('payment_error') }}</p>
                            </div>
                        @endif

                        <form wire:submit="{{ $mode === 'receipt' ? 'createReceipt' : 'createPayment' }}">
                            <!-- Cash Receipt Form -->
                            @if($mode === 'receipt')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Received From <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            wire:model.live="receiptData.received_from"
                                            type="text" 
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="Customer name or source"
                                        >
                                        @error('receiptData.received_from')
                                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Amount <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            wire:model.live="receiptData.amount"
                                            type="number" 
                                            step="0.01"
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="0.00"
                                        >
                                        @error('receiptData.amount')
                                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Cash Account <span class="text-rose-500">*</span>
                                        </label>
                                        <select 
                                            wire:model.live="receiptData.cash_account_id"
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-slate-700 dark:text-white"
                                        >
                                            <option value="">Select cash account</option>
                                            @foreach($cashAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                            @endforeach
                                        </select>
                                        @error('receiptData.cash_account_id')
                                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Credit Account <span class="text-rose-500">*</span>
                                        </label>
                                        <select 
                                            wire:model.live="receiptData.credit_account_id"
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-slate-700 dark:text-white"
                                        >
                                            <option value="">Select credit account</option>
                                            @foreach($revenueAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                            @endforeach
                                        </select>
                                        @error('receiptData.credit_account_id')
                                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Date <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            wire:model.live="receiptData.date"
                                            type="date" 
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-slate-700 dark:text-white"
                                        >
                                        @error('receiptData.date')
                                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Description
                                        </label>
                                        <input 
                                            wire:model.live="receiptData.description"
                                            type="text" 
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="Optional description"
                                        >
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        Notes
                                    </label>
                                    <textarea 
                                        wire:model.live="receiptData.notes"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-slate-700 dark:text-white"
                                        placeholder="Additional notes (optional)"
                                    ></textarea>
                                </div>
                            @endif

                            <!-- Cash Payment Form -->
                            @if($mode === 'payment')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Paid To <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            wire:model.live="paymentData.paid_to"
                                            type="text" 
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="Vendor name or recipient"
                                        >
                                        @error('paymentData.paid_to')
                                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Amount <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            wire:model.live="paymentData.amount"
                                            type="number" 
                                            step="0.01"
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="0.00"
                                        >
                                        @error('paymentData.amount')
                                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Cash Account <span class="text-rose-500">*</span>
                                        </label>
                                        <select 
                                            wire:model.live="paymentData.cash_account_id"
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-700 dark:text-white"
                                        >
                                            <option value="">Select cash account</option>
                                            @foreach($cashAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                            @endforeach
                                        </select>
                                        @error('paymentData.cash_account_id')
                                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Debit Account <span class="text-rose-500">*</span>
                                        </label>
                                        <select 
                                            wire:model.live="paymentData.debit_account_id"
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-700 dark:text-white"
                                        >
                                            <option value="">Select debit account</option>
                                            @foreach($expenseAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                            @endforeach
                                        </select>
                                        @error('paymentData.debit_account_id')
                                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Date <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            wire:model.live="paymentData.date"
                                            type="date" 
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-700 dark:text-white"
                                        >
                                        @error('paymentData.date')
                                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Purpose
                                        </label>
                                        <input 
                                            wire:model.live="paymentData.purpose"
                                            type="text" 
                                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-700 dark:text-white"
                                            placeholder="Optional purpose"
                                        >
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        Notes
                                    </label>
                                    <textarea 
                                        wire:model.live="paymentData.notes"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 dark:bg-slate-700 dark:text-white"
                                        placeholder="Additional notes (optional)"
                                    ></textarea>
                                </div>
                            @endif

                            <div class="mt-8 flex justify-end">
                                <button 
                                    type="submit"
                                    class="px-6 py-3 {{ $mode === 'receipt' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }} text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $mode === 'receipt' ? 'focus:ring-emerald-500' : 'focus:ring-rose-500' }}"
                                >
                                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ $mode === 'receipt' ? 'Create Receipt' : 'Create Payment' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                    <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Recent Transactions</h3>
                    </div>
                    
                    <div class="p-6">
                        <!-- Recent Receipts -->
                        @if($recentReceipts->count() > 0)
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Recent Receipts</h4>
                                <div class="space-y-3">
                                    @foreach($recentReceipts as $receipt)
                                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-medium text-slate-900 dark:text-white">{{ $receipt->received_from }}</p>
                                                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $receipt->receipt_number }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format($receipt->amount, 2) }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $receipt->date->format('M d, Y') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Recent Payments -->
                        @if($recentPayments->count() > 0)
                            <div>
                                <h4 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Recent Payments</h4>
                                <div class="space-y-3">
                                    @foreach($recentPayments as $payment)
                                        <div class="p-3 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-medium text-slate-900 dark:text-white">{{ $payment->paid_to }}</p>
                                                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $payment->voucher_number }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="font-semibold text-rose-600 dark:text-rose-400">${{ number_format($payment->amount, 2) }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $payment->date->format('M d, Y') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($recentReceipts->count() === 0 && $recentPayments->count() === 0)
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-slate-600 dark:text-slate-400">No transactions yet</p>
                                <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">Create your first cash receipt or payment</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

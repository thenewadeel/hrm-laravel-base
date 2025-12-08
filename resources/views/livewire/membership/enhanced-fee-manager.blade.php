<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-8">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Enhanced Statistics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 mb-10">
        <!-- Total Revenue -->
        <div class="bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white hover:shadow-2xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-base font-semibold">Total Revenue</p>
                    <p class="text-4xl font-bold mt-2">${{ number_format($feeStatistics['paid_amount'] ?? 0, 2) }}</p>
                    <p class="text-blue-100 text-sm mt-2">{{ $feeStatistics['paid_fees'] ?? 0 }} transactions</p>
                </div>
                <div class="bg-blue-700 bg-opacity-50 rounded-full p-4 shadow-lg">
                    <svg class="h-10 w-10 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Outstanding Amount -->
        <div class="bg-gradient-to-br from-orange-500 via-orange-600 to-red-600 rounded-2xl shadow-xl p-8 text-white hover:shadow-2xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-base font-semibold">Outstanding</p>
                    <p class="text-4xl font-bold mt-2">${{ number_format($feeStatistics['outstanding_amount'] ?? 0, 2) }}</p>
                    <p class="text-orange-100 text-sm mt-2">{{ $feeStatistics['pending_fees'] ?? 0 }} pending</p>
                </div>
                <div class="bg-orange-700 bg-opacity-50 rounded-full p-4 shadow-lg">
                    <svg class="h-10 w-10 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Overdue Fees -->
        <div class="bg-gradient-to-br from-red-500 via-red-600 to-pink-600 rounded-2xl shadow-xl p-8 text-white hover:shadow-2xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-base font-semibold">Overdue Fees</p>
                    <p class="text-4xl font-bold mt-2">{{ $overdueFees->count() }}</p>
                    <p class="text-red-100 text-sm mt-2">${{ number_format($overdueFees->sum('amount'), 2) }} value</p>
                </div>
                <div class="bg-red-700 bg-opacity-50 rounded-full p-4 shadow-lg">
                    <svg class="h-10 w-10 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Collection Rate -->
        <div class="bg-gradient-to-br from-green-500 via-green-600 to-emerald-600 rounded-2xl shadow-xl p-8 text-white hover:shadow-2xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-base font-semibold">Collection Rate</p>
                    <p class="text-4xl font-bold mt-2">{{ number_format($feeStatistics['collection_rate'] ?? 0, 1) }}%</p>
                    <p class="text-green-100 text-sm mt-2">This month</p>
                </div>
                <div class="bg-green-700 bg-opacity-50 rounded-full p-4 shadow-lg">
                    <svg class="h-10 w-10 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Analytics Toggle -->
        <div class="bg-gradient-to-br from-purple-500 via-purple-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white hover:shadow-2xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-base font-semibold">Analytics</p>
                    <p class="text-xl font-bold mt-1">View Reports</p>
                    <p class="text-purple-100 text-sm mt-2">Revenue insights</p>
                </div>
                <button wire:click="toggleAnalytics" class="bg-purple-700 bg-opacity-50 rounded-full p-4 hover:bg-opacity-70 transition shadow-lg hover:shadow-xl">
                    <svg class="h-10 w-10 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard (Hidden by default) -->
    @if($showAnalytics)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-10">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Revenue Analytics</h3>
                <button wire:click="showAnalytics = false" class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Monthly Revenue Trend -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-xl p-6 shadow-inner">
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Monthly Revenue Trend</h4>
                    <div class="space-y-2">
                        @foreach($revenueAnalytics['monthly_trend'] as $data)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($data['month'])->format('M Y') }}</span>
                                <div class="flex items-center">
                                    <div class="w-32 bg-gray-200 dark:bg-gray-600 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($data['revenue'] / 10000) * 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($data['revenue'], 0) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Revenue by Fee Type -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-xl p-6 shadow-inner">
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Revenue by Fee Type</h4>
                    <div class="space-y-2">
                        @foreach($revenueAnalytics['revenue_by_type'] as $data)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $data['fee_type'])) }}</span>
                                <div class="flex items-center">
                                    <div class="w-32 bg-gray-200 dark:bg-gray-600 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(100, ($data['revenue'] / 5000) * 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($data['revenue'], 0) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Aging Analysis -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-xl p-6 shadow-inner">
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Aging Analysis</h4>
                    <div class="space-y-2">
                        @foreach($revenueAnalytics['aging_analysis'] as $period => $amount)
                            @if($amount > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $period)) }}</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($amount, 2) }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-xl p-6 shadow-inner">
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Payment Methods</h4>
                    <div class="space-y-2">
                        @foreach($revenueAnalytics['payment_methods'] as $data)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $data['payment_method'])) }}</span>
                                <div class="flex items-center">
                                    <div class="w-32 bg-gray-200 dark:bg-gray-600 rounded-full h-2 mr-2">
                                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ min(100, ($data['total'] / 8000) * 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($data['total'], 0) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Enhanced Actions Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl mb-8">
        <div class="p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-6 lg:space-y-0">
                <div class="flex flex-wrap gap-4">
                    <button wire:click="showCreateFeeForm" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                        <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Fee
                    </button>
                    <button wire:click="generateOverdueFees" wire:confirm="Generate overdue fees for all pending subscriptions?" class="px-6 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-xl hover:from-orange-700 hover:to-orange-800 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                        <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Generate Overdue
                    </button>
                    <button wire:click="showReminderModal" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                        <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Send Reminders
                    </button>
                </div>
                <div class="flex gap-4">
                    <button wire:click="exportData('csv')" class="px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                        <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export CSV
                    </button>
                    <button wire:click="exportData('pdf')" class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                        <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Create Fee Form -->
    @if($showCreateForm)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl mb-8">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Fee</h3>
                    <button wire:click="hideCreateFeeForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="createFee">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <!-- Member Selection -->
                        @if(!$member)
                            <div>
                                <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                    Member <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="member_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-base" required>
                                    <option value="">Select Member</option>
                                    @foreach(\App\Models\Membership\Member::where('organization_id', auth()->user()->current_organization_id)->get() as $memberOption)
                                        <option value="{{ $memberOption->id }}">{{ $memberOption->full_name }} ({{ $memberOption->membership_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Fee Type -->
                        <div>
                                <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                    Fee Type <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="fee_type" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-base" required>
                                @foreach($feeTypes as $value => $label)
                                    @if($value !== 'all')
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Amount -->
                        <div>
                                <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                    Amount <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-gray-500 text-lg">$</span>
                                    <input type="number" wire:model="amount" min="0.01" step="0.01" class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-base" required />
                                </div>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="description" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-base" required />
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="due_date" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-base" required />
                        </div>

                        <!-- Recurring Options -->
                        <div class="md:col-span-2">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" wire:model="recurring" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Recurring Fee</label>
                            </div>

                            @if($recurring)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Frequency</label>
                                        <select wire:model="recurring_frequency" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                            @foreach($recurringFrequencies as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                                        <input type="date" wire:model="recurring_end_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                        <textarea wire:model="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" wire:click="hideCreateFeeForm" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" wire:loading.attr="disabled">
                            <span wire:loading.remove>Create Fee</span>
                            <span wire:loading>Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Enhanced Payment Processing Modal -->
    @if($showPaymentForm)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Process Payment</h3>
                        <button wire:click="hidePaymentForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="processPayment">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Payment Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Payment Amount <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                                    <input type="number" wire:model="payment_amount" min="0.01" step="0.01" class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" required />
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Payment Method <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="payment_method" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" required>
                                    @foreach($paymentMethods as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Reference -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Reference</label>
                                <input type="text" wire:model="payment_reference" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" />
                            </div>

                            <!-- Payment Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Notes</label>
                                <textarea wire:model="payment_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                            </div>
                        </div>

                        <!-- Additional Options -->
                        <div class="mt-6 space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="send_receipt" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Send receipt to member</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="generate_invoice" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Generate invoice</label>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" wire:click="hidePaymentForm" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" wire:loading.attr="disabled">
                                <span wire:loading.remove>Process Payment</span>
                                <span wire:loading>Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Invoice Modal -->
    @if($showInvoiceModal && $selectedFee)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-lg bg-white dark:bg-gray-800">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Invoice #{{ $invoiceNumber }}</h3>
                    <div class="flex space-x-3">
                        <button wire:click="printInvoice" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print
                        </button>
                        <button wire:click="showInvoiceModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="invoice-content" class="p-8 bg-white dark:bg-gray-800">
                    <!-- Invoice Header -->
                    <div class="border-b-2 border-gray-200 dark:border-gray-700 pb-6 mb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">INVOICE</h2>
                                <p class="text-gray-600 dark:text-gray-400">{{ $invoiceNumber }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Date: {{ now()->format('M j, Y') }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Due: {{ $selectedFee->due_date->format('M j, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bill To -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Bill To:</h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ $selectedFee->member->full_name }}</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ $selectedFee->member->email }}</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ $selectedFee->member->phone }}</p>
                        @if($selectedFee->member->address)
                            <p class="text-gray-600 dark:text-gray-400">{{ $selectedFee->member->address }}</p>
                        @endif
                    </div>

                    <!-- Invoice Items -->
                    <div class="mb-6">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-2 text-gray-700 dark:text-gray-300">Description</th>
                                    <th class="text-right py-2 text-gray-700 dark:text-gray-300">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-4">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $selectedFee->description }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $selectedFee->fee_type)) }}</p>
                                    </td>
                                    <td class="text-right py-4 font-medium text-gray-900 dark:text-white">${{ number_format($selectedFee->amount, 2) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 dark:border-gray-700">
                                    <th class="text-right py-4 text-gray-700 dark:text-gray-300">Total:</th>
                                    <th class="text-right py-4 text-xl font-bold text-gray-900 dark:text-white">${{ number_format($selectedFee->amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment Instructions</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Please make payment by the due date to avoid late fees. Accepted payment methods include cash, bank transfer, credit card, and online payment.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Receipt Modal -->
    @if($showReceiptModal && $selectedFee)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-lg bg-white dark:bg-gray-800">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Receipt #{{ $receiptNumber }}</h3>
                    <div class="flex space-x-3">
                        <button wire:click="printReceipt" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print
                        </button>
                        <button wire:click="showReceiptModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="receipt-content" class="p-8 bg-white dark:bg-gray-800">
                    <!-- Receipt Header -->
                    <div class="border-b-2 border-gray-200 dark:border-gray-700 pb-6 mb-6">
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">PAYMENT RECEIPT</h2>
                            <p class="text-gray-600 dark:text-gray-400">{{ $receiptNumber }}</p>
                            <p class="text-gray-600 dark:text-gray-400">{{ now()->format('M j, Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Member:</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $selectedFee->member->full_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Payment Method:</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $selectedFee->payment_method)) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Amount Paid:</p>
                                <p class="font-medium text-gray-900 dark:text-white">${{ number_format($selectedFee->paid_amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Payment Date:</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $selectedFee->paid_date->format('M j, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Details -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Fee Details</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $selectedFee->description }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $selectedFee->fee_type)) }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Original Amount: ${{ number_format($selectedFee->amount, 2) }}</p>
                        </div>
                    </div>

                    <!-- Thank You Message -->
                    <div class="text-center">
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">Thank you for your payment!</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">This receipt serves as proof of payment for your records.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reminder Modal -->
    @if($showReminderModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Send Payment Reminders</h3>
                        <button wire:click="hideReminderModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reminder Type</label>
                            <select wire:model="reminderType" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="both">Email & SMS</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message</label>
                            <textarea wire:model="reminderMessage" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                <strong>Note:</strong> Select fees from the table below before sending reminders. Only pending and overdue fees will be included.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button wire:click="hideReminderModal" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button wire:click="sendReminders" class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            Send Reminders
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Enhanced Filters and Search -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <input type="text" wire:model.live="search" placeholder="Search fees by member name, description..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white" />
                </div>

                <!-- Status Filter -->
                <div>
                    <select wire:model.live="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Fee Type Filter -->
                <div>
                    <select wire:model.live="feeType" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        @foreach($feeTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div>
                    <select wire:model.live="dateRange" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        @foreach($dateRanges as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Fees Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sort('created_at')" class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
                                Date Created
                                @if($sortBy === 'created_at')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sort('amount')" class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
                                Amount
                                @if($sortBy === 'amount')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($fees as $fee)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedFeeIds" value="{{ $fee->id }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $fee->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $fee->member->full_name }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $fee->member->membership_number }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $fee->description }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ ucfirst(str_replace('_', ' ', $fee->fee_type)) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                ${{ number_format($fee->amount, 2) }}
                                @if($fee->paid_amount > 0)
                                    <div class="text-xs text-green-600">Paid: ${{ number_format($fee->paid_amount, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $fee->due_date->format('M j, Y') }}
                                @if($fee->is_overdue)
                                    <div class="text-xs text-red-600">{{ $fee->days_overdue }} days overdue</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($fee->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($fee->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @elseif($fee->status === 'waived') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                    @elseif($fee->status === 'overdue') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @endif">
                                    {{ ucfirst($fee->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    @if($fee->status === 'pending' || $fee->status === 'overdue')
                                        <button wire:click="showPaymentForm({{ $fee->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Process Payment">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="generateInvoice($fee)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Generate Invoice">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="waiveFee({{ $fee->id }}, 'Waived by admin')" wire:confirm="Waive this fee?" class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300" title="Waive Fee">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    @if($fee->status === 'paid')
                                        <button wire:click="generateReceipt($fee)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Generate Receipt">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No fees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Enhanced Pagination -->
        @if($fees->hasPages())
            <div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    {{ $fees->links() }}
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ $fees->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $fees->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $fees->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        {{ $fees->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Payments Widget -->
    @if($recentPayments && $recentPayments->count() > 0)
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Payments</h3>
                <div class="space-y-3">
                    @foreach($recentPayments as $payment)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="bg-green-100 dark:bg-green-900 rounded-full p-2 mr-3">
                                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $payment->member->full_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->description }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($payment->paid_amount, 2) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->paid_date->format('M j, Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
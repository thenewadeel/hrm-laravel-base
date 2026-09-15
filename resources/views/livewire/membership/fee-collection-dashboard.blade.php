<div>
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Fee Collection Dashboard</h2>
        <p class="text-gray-600 dark:text-gray-400">Monitor payments, track defaulters, and analyze collection trends</p>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Members</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $dashboardStats['total_members'] }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">{{ $dashboardStats['active_members'] }} active</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <x-heroicon-o-user-group class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Collected</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">${{ number_format($dashboardStats['total_collected'], 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This month</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                    <x-heroicon-o-face-smile class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pending Amount</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">${{ number_format($dashboardStats['pending_amount'], 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Awaiting payment</p>
                </div>
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <x-heroicon-o-clock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Defaulters</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $dashboardStats['defaulters_count'] }}</p>
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">${{ number_format($dashboardStats['overdue_amount'], 0) }} overdue</p>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900 rounded-lg">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
            </div>
        </div>
    </div>

    <!-- Collection Rate -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Collection Rate</h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">Current Month</span>
        </div>
        <div class="relative">
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-8">
                <div class="bg-gradient-to-r from-green-400 to-green-600 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium" style="width: {{ $dashboardStats['collection_rate'] }}%">
                    {{ $dashboardStats['collection_rate'] }}%
                </div>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $dashboardStats['collection_rate'] }}%</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Success Rate</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ round(100 - $dashboardStats['collection_rate']) }}%</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pending Rate</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">+5.2%</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">vs Last Month</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Payments -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Payments</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($recentPayments as $payment)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="text-2xl">{{ $this->getPaymentMethodIcon($payment['payment_method']) }}</div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $payment['member_name'] }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $payment['member_id'] }} • {{ $payment['fee_type'] }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600 dark:text-green-400">${{ number_format($payment['amount'], 0) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Defaulters List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Payment Defaulters</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($defaulters as $defaulter)
                        <div class="flex items-center justify-between p-3 bg-{{ $getDefaulterStatusColor($defaulter['status']) }}-50 dark:bg-{{ $getDefaulterStatusColor($defaulter['status']) }}-900/20 rounded-lg border border-{{ $getDefaulterStatusColor($defaulter['status']) }}-200 dark:border-{{ $getDefaulterStatusColor($defaulter['status']) }}-800">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-{{ $getDefaulterStatusColor($defaulter['status']) }}-100 dark:bg-{{ $getDefaulterStatusColor($defaulter['status']) }}-900 rounded-full flex items-center justify-center">
                                    <span class="text-{{ $getDefaulterStatusColor($defaulter['status']) }}-600 dark:text-{{ $getDefaulterStatusColor($defaulter['status']) }}-400 font-bold text-sm">
                                        {{ $defaulter['days_overdue'] }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $defaulter['member_name'] }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $defaulter['member_id'] }} • {{ $defaulter['days_overdue'] }} days overdue</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-red-600 dark:text-red-400">${{ number_format($defaulter['overdue_amount'], 0) }}</p>
                                <div class="flex space-x-1 mt-1">
                                    <button wire:click="sendReminder({{ $defaulter['id'] }})" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                        Remind
                                    </button>
                                    <button wire:click="escalateDefaulter({{ $defaulter['id'] }})" class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                        Escalate
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Monthly Collection Trends</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($monthlyTrends as $trend)
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $trend['month'] }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    Collected: ${{ number_format($trend['collected'], 0) }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ round(($trend['collected'] / ($trend['collected'] + $trend['pending'])) * 100) }}%"></div>
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <p class="text-sm text-yellow-600 dark:text-yellow-400">${{ number_format($trend['pending'], 0) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex flex-wrap gap-3">
        <button wire:click="generateReport" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4 inline mr-2" />
            Generate Report
        </button>
        
        <button wire:click="exportData" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
            <x-heroicon-o-arrow-up-tray class="w-4 h-4 inline mr-2" />
            Export Data
        </button>
        
        <button class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
            <x-heroicon-o-bell class="w-4 h-4 inline mr-2" />
            Send Reminders
        </button>
        
        <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <x-heroicon-o-envelope class="w-4 h-4 inline mr-2" />
            Email Statements
        </button>
    </div>

    <!-- Event Listeners -->
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('show-notification', function (event) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                    event.type === 'success' ? 'bg-green-500 text-white' : 
                    event.type === 'warning' ? 'bg-yellow-500 text-white' : 
                    event.type === 'error' ? 'bg-red-500 text-white' : 'bg-gray-500 text-white'
                }`;
                notification.textContent = event.message;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 3000);
            });
        });
    </script>
</div>
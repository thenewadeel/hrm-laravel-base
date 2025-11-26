<div>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Payroll Dashboard</h2>
            <div class="flex items-center space-x-4">
                <input type="month" 
                       wire:model.live="selected_period" 
                       class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                <button wire:click="processPayroll" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-calculator mr-2"></i>Process Payroll
                </button>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-lg p-3">
                        <i class="fas fa-users text-blue-600 dark:text-blue-300"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Employees</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $summary['total_employees'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-lg p-3">
                        <i class="fas fa-dollar-sign text-green-600 dark:text-green-300"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Net Pay</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($summary['total_net_pay'], 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900 rounded-lg p-3">
                        <i class="fas fa-clock text-yellow-600 dark:text-yellow-300"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Approvals</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $pendingIncrements + $pendingLoans + $pendingAdvances }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900 rounded-lg p-3">
                        <i class="fas fa-hand-holding-usd text-purple-600 dark:text-purple-300"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Loans</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $summary['employee_breakdown']->count() ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payroll Summary - {{ $selected_period }}</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Basic Salary</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($summary['total_basic_salary'], 2) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Gross Pay</p>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">${{ number_format($summary['total_gross_pay'], 2) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Net Pay</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">${{ number_format($summary['total_net_pay'], 2) }}</p>
                    </div>
                </div>

                <!-- Breakdown -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3">Earnings Breakdown</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Basic Salary</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($summary['total_basic_salary'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Allowances</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($summary['total_allowances'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-semibold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                                <span>Gross Pay</span>
                                <span>${{ number_format($summary['total_gross_pay'], 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3">Deductions Breakdown</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Tax</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($summary['total_tax'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Other Deductions</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($summary['total_deductions'] - $summary['total_tax'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-semibold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                                <span>Total Deductions</span>
                                <span>${{ number_format($summary['total_deductions'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Items -->
        @if ($pendingIncrements > 0 || $pendingLoans > 0 || $pendingAdvances > 0)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 mr-3"></i>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Pending Approvals</h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                            You have {{ $pendingIncrements }} increment(s), {{ $pendingLoans }} loan(s), and {{ $pendingAdvances }} advance(s) pending approval.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

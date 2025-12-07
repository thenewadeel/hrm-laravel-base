<div>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-primary">Payroll Dashboard</h2>
            <div class="flex items-center space-x-4">
                <input type="month" 
                       wire:model.live="selected_period" 
                       class="px-3 py-2 border border-primary rounded-lg focus:ring-2 focus:ring-blue-500">
                <button wire:click="processPayroll" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-calculator mr-2"></i>Process Payroll
                </button>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="surface p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-secondary">Total Employees</p>
                        <p class="text-2xl font-semibold text-primary">{{ $summary['total_employees'] }}</p>
                    </div>
                </div>
            </div>

            <div class="surface p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <i class="fas fa-dollar-sign text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-secondary">Total Net Pay</p>
                        <p class="text-2xl font-semibold text-primary">${{ number_format($summary['total_net_pay'], 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="surface p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-secondary">Pending Approvals</p>
                        <p class="text-2xl font-semibold text-primary">{{ $pendingIncrements + $pendingLoans + $pendingAdvances }}</p>
                    </div>
                </div>
            </div>

            <div class="surface p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <i class="fas fa-hand-holding-usd text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-secondary">Active Loans</p>
                        <p class="text-2xl font-semibold text-primary">{{ $summary['employee_breakdown']->count() ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Summary -->
        <div class="surface rounded-lg shadow">
            <div class="px-6 py-4 border-b border-secondary">
                <h3 class="text-lg font-semibold text-primary">Payroll Summary - {{ $selected_period }}</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <p class="text-sm font-medium text-secondary mb-2">Basic Salary</p>
                        <p class="text-3xl font-bold text-primary">${{ number_format($summary['total_basic_salary'], 2) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-secondary mb-2">Gross Pay</p>
                        <p class="text-3xl font-bold text-blue-600">${{ number_format($summary['total_gross_pay'], 2) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-secondary mb-2">Net Pay</p>
                        <p class="text-3xl font-bold text-green-600">${{ number_format($summary['total_net_pay'], 2) }}</p>
                    </div>
                </div>

                <!-- Breakdown -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-secondary mb-3">Earnings Breakdown</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">Basic Salary</span>
                                <span class="text-sm font-medium text-primary">${{ number_format($summary['total_basic_salary'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">Allowances</span>
                                <span class="text-sm font-medium text-primary">${{ number_format($summary['total_allowances'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-semibold text-primary pt-2 border-t border-secondary">
                                <span>Gross Pay</span>
                                <span>${{ number_format($summary['total_gross_pay'], 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-secondary mb-3">Deductions Breakdown</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">Tax</span>
                                <span class="text-sm font-medium text-primary">${{ number_format($summary['total_tax'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">Other Deductions</span>
                                <span class="text-sm font-medium text-primary">${{ number_format($summary['total_deductions'] - $summary['total_tax'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-semibold text-primary pt-2 border-t border-secondary">
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
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-yellow-800">Pending Approvals</h4>
                        <p class="text-sm text-yellow-700 mt-1">
                            You have {{ $pendingIncrements }} increment(s), {{ $pendingLoans }} loan(s), and {{ $pendingAdvances }} advance(s) pending approval.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

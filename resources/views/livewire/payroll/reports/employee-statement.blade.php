<!-- Employee Statement -->
<div>
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $reportData['summary']['total_advances'] }}</div>
            <div class="text-sm text-blue-600 dark:text-blue-400 mt-1">Total Advances</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">${{ number_format($reportData['summary']['total_amount'], 0) }}</div>
            <div class="text-sm text-green-600 dark:text-green-400 mt-1">Total Amount</div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">${{ number_format($reportData['summary']['total_balance'], 0) }}</div>
            <div class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">Outstanding Balance</div>
        </div>
        
        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">${{ number_format($reportData['summary']['total_repaid'], 0) }}</div>
            <div class="text-sm text-purple-600 dark:text-purple-400 mt-1">Total Repaid</div>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border border-orange-200 dark:border-orange-800">
            <div class="text-xl font-bold text-orange-600 dark:text-orange-400">{{ $reportData['summary']['pending_count'] }}</div>
            <div class="text-sm text-orange-600 dark:text-orange-400 mt-1">Pending Approval</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
            <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ $reportData['summary']['active_count'] }}</div>
            <div class="text-sm text-green-600 dark:text-green-400 mt-1">Active Advances</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
            <div class="text-xl font-bold text-gray-600 dark:text-gray-400">{{ $reportData['summary']['completed_count'] }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Completed Advances</div>
        </div>
    </div>

    <!-- Employee Info (if specific employee selected) -->
    @if($reportData['employee'])
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Employee Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Name:</span>
                    <span class="ml-2 text-gray-900 dark:text-white">{{ $reportData['employee']->first_name }} {{ $reportData['employee']->last_name }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Employee ID:</span>
                    <span class="ml-2 text-gray-900 dark:text-white">{{ $reportData['employee']->employee_id }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Department:</span>
                    <span class="ml-2 text-gray-900 dark:text-white">{{ $reportData['employee']->department ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Advances Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reference</th>
                    @if(!$reportData['employee'])
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employee</th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monthly Deduction</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Request Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($reportData['advances'] as $advance)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $advance->advance_reference }}
                        </td>
                        @if(!$reportData['employee'])
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $advance->employee->first_name }} {{ $advance->employee->last_name }}
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($advance->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($advance->balance_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($advance->monthly_deduction, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($advance->months_repaid / $advance->repayment_months) * 100 }}%"></div>
                                </div>
                                <span class="text-xs">{{ $advance->months_repaid }}/{{ $advance->repayment_months }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($advance->status)
                                @case('pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                    @break
                                @case('approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Approved
                                    </span>
                                    @break
                                @case('active')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                    @break
                                @case('completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Completed
                                    </span>
                                    @break
                                @default
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ ucfirst($advance->status) }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $advance->request_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <a href="/hrm/advances#{{ $advance->id }}" class="text-blue-600 hover:text-blue-900">
                                        View Details
                                    </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($reportData['advances']->count() === 0)
        <div class="text-center py-8">
            <div class="text-gray-500 dark:text-gray-400">No advances found for the selected criteria.</div>
        </div>
    @endif
</div>
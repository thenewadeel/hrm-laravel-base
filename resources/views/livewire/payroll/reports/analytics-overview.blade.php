<!-- Analytics Overview -->
@if(empty($reportData))
    <div class="text-center py-8">
        <div class="text-gray-500 dark:text-gray-400">No data available. Please ensure you are logged in and have access to an organization.</div>
    </div>
@else
    <div>
    <!-- Overview Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $reportData['overview']['total_advances'] }}</div>
            <div class="text-sm text-blue-600 dark:text-blue-400 mt-1">Total Advances</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">${{ number_format($reportData['overview']['total_amount_disbursed'], 0) }}</div>
            <div class="text-sm text-green-600 dark:text-green-400 mt-1">Total Disbursed</div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">${{ number_format($reportData['overview']['total_outstanding'], 0) }}</div>
            <div class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">Total Outstanding</div>
        </div>
        
        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $reportData['overview']['active_advances'] }}</div>
            <div class="text-sm text-purple-600 dark:text-purple-400 mt-1">Active Advances</div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $reportData['overview']['completed_advances'] }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Completed</div>
        </div>
        
        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border border-orange-200 dark:border-orange-800">
            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $reportData['overview']['pending_advances'] }}</div>
            <div class="text-sm text-orange-600 dark:text-orange-400 mt-1">Pending Approval</div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Metrics</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($reportData['performance_metrics']['avg_advance_amount'], 0) }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Average Advance Amount</div>
            </div>
            
            <div class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['performance_metrics']['avg_repayment_period'], 1) }} months</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Average Repayment Period</div>
            </div>
            
            <div class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['performance_metrics']['completion_rate'], 1) }}%</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</div>
            </div>
            
            <div class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($reportData['performance_metrics']['avg_monthly_deduction'], 0) }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Average Monthly Deduction</div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Monthly Trends (Last 6 Months)</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Month</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requested</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Approved</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount Requested</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount Approved</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($reportData['trends']['monthly_data']->take(6) as $month)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $month['month_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $month['advances_requested'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $month['advances_approved'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${{ number_format($month['amount_requested'], 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${{ number_format($month['amount_approved'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endif
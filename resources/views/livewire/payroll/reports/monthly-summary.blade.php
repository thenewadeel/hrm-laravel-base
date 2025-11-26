<!-- Monthly Summary -->
<div>
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $reportData['summary']['total_requested'] }}</div>
            <div class="text-sm text-blue-600 dark:text-blue-400 mt-1">Total Requested</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $reportData['summary']['total_approved'] }}</div>
            <div class="text-sm text-green-600 dark:text-green-400 mt-1">Total Approved</div>
        </div>
        
        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">${{ number_format($reportData['summary']['total_amount_requested'], 0) }}</div>
            <div class="text-sm text-purple-600 dark:text-purple-400 mt-1">Amount Requested</div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">${{ number_format($reportData['summary']['total_amount_approved'], 0) }}</div>
            <div class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">Amount Approved</div>
        </div>
    </div>

    <!-- Monthly Trend Chart -->
    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Monthly Trend</h3>
        <div class="h-64 flex items-end justify-between space-x-2">
            @foreach($reportData['monthly_data'] as $month)
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-blue-500 rounded-t" style="height: {{ $month['amount_requested'] > 0 ? ($month['amount_requested'] / $reportData['monthly_data']->max('amount_requested')) * 100 : 0 }}%"></div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-2 text-center">
                        <div>{{ \Carbon\Carbon::parse($month['month'])->format('M') }}</div>
                        <div class="font-semibold">${{ number_format($month['amount_requested'], 0) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Monthly Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Month</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requested</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Approved</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Approval Rate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount Requested</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount Approved</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pending Approval</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Average Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($reportData['monthly_data'] as $month)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $month['month_name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $month['advances_requested'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $month['advances_approved'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            @if($month['advances_requested'] > 0)
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($month['advances_approved'] / $month['advances_requested']) * 100 }}%"></div>
                                    </div>
                                    <span class="text-xs">{{ round(($month['advances_approved'] / $month['advances_requested']) * 100, 1) }}%</span>
                                </div>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($month['amount_requested'], 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($month['amount_approved'], 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($month['pending_approval'] > 0) bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $month['pending_approval'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            @if($month['advances_requested'] > 0)
                                ${{ number_format($month['amount_requested'] / $month['advances_requested'], 0) }}
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Key Insights -->
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
        <h4 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">Key Insights</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800 dark:text-blue-200">
            <div>
                <strong>Peak Month:</strong> 
                {{ $reportData['monthly_data']->sortByDesc('amount_requested')->first()['month_name'] }} 
                (${{ number_format($reportData['monthly_data']->sortByDesc('amount_requested')->first()['amount_requested'], 0) }})
            </div>
            <div>
                <strong>Average Monthly Requests:</strong> 
                {{ round($reportData['summary']['total_requested'] / 12, 1) }} advances
            </div>
            <div>
                <strong>Overall Approval Rate:</strong> 
                @if($reportData['summary']['total_requested'] > 0)
                    {{ round(($reportData['summary']['total_approved'] / $reportData['summary']['total_requested']) * 100, 1) }}%
                @else
                    0%
                @endif
            </div>
            <div>
                <strong>Average Advance Size:</strong> 
                @if($reportData['summary']['total_requested'] > 0)
                    ${{ number_format($reportData['summary']['total_amount_requested'] / $reportData['summary']['total_requested'], 0) }}
                @else
                    $0
                @endif
            </div>
        </div>
    </div>
</div>
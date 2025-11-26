<!-- Department Report -->
<div>
    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $reportData['summary']['total_departments'] }}</div>
            <div class="text-sm text-blue-600 dark:text-blue-400 mt-1">Total Departments</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $reportData['summary']['total_advances'] }}</div>
            <div class="text-sm text-green-600 dark:text-green-400 mt-1">Total Advances</div>
        </div>
        
        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">${{ number_format($reportData['summary']['total_amount'], 0) }}</div>
            <div class="text-sm text-purple-600 dark:text-purple-400 mt-1">Total Amount</div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">${{ number_format($reportData['summary']['total_balance'], 0) }}</div>
            <div class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">Total Outstanding</div>
        </div>
    </div>

    <!-- Department Comparison Chart -->
    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Department Comparison</h3>
        <div class="h-64 flex items-end justify-between space-x-2">
            @foreach($reportData['departments']->sortByDesc('total_amount') as $dept)
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-gradient-to-t from-blue-500 to-blue-400 rounded-t" style="height: {{ $dept['total_amount'] > 0 ? ($dept['total_amount'] / $reportData['departments']->max('total_amount')) * 100 : 0 }}%"></div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-2 text-center">
                        <div class="truncate w-full">{{ \Illuminate\Support\Str::limit($dept['department'], 8) }}</div>
                        <div class="font-semibold">${{ number_format($dept['total_amount'], 0) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Department Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employees</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Advances</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Outstanding</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Average Advance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Adv/Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status Breakdown</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($reportData['departments']->sortByDesc('total_amount') as $dept)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $dept['department'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $dept['employee_count'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $dept['total_advances'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($dept['total_amount'], 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($dept['total_balance'], 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($dept['avg_advance_amount'], 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $dept['employee_count'] > 0 ? round($dept['total_advances'] / $dept['employee_count'], 1) : 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <div class="flex space-x-1">
                                @if($dept['active_advances'] > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        {{ $dept['active_advances'] }} Active
                                    </span>
                                @endif
                                @if($dept['pending_advances'] > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                        {{ $dept['pending_advances'] }} Pending
                                    </span>
                                @endif
                                @if($dept['completed_advances'] > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                        {{ $dept['completed_advances'] }} Completed
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Department Insights -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
            <h4 class="font-semibold text-green-900 dark:text-green-300 mb-2">Top Department by Advances</h4>
            @php
                $topDept = $reportData['departments']->sortByDesc('total_advances')->first();
            @endphp
            @if($topDept)
                <div class="text-sm text-green-800 dark:text-green-200">
                    <div class="font-semibold">{{ $topDept['department'] }}</div>
                    <div>{{ $topDept['total_advances'] }} advances (${{ number_format($topDept['total_amount'], 0) }})</div>
                    <div>{{ round(($topDept['total_advances'] / $reportData['summary']['total_advances']) * 100, 1) }}% of total advances</div>
                </div>
            @endif
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
            <h4 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">Highest Average Advance</h4>
            @php
                $highestAvg = $reportData['departments']->sortByDesc('avg_advance_amount')->first();
            @endphp
            @if($highestAvg)
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <div class="font-semibold">{{ $highestAvg['department'] }}</div>
                    <div>${{ number_format($highestAvg['avg_advance_amount'], 0) }} average</div>
                    <div>{{ $highestAvg['total_advances'] }} total advances</div>
                </div>
            @endif
        </div>
    </div>

    @if($reportData['departments']->count() === 0)
        <div class="text-center py-8">
            <div class="text-gray-500 dark:text-gray-400">No department data available.</div>
        </div>
    @endif
</div>
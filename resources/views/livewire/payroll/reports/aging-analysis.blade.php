<!-- Aging Analysis -->
<div>
    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $reportData['total_active_advances'] }}</div>
            <div class="text-sm text-blue-600 dark:text-blue-400 mt-1">Active Advances</div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">${{ number_format($reportData['total_outstanding'], 0) }}</div>
            <div class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">Total Outstanding</div>
        </div>
    </div>

    <!-- Aging Buckets -->
    <div class="space-y-6">
        @foreach($reportData['aging_buckets'] as $bucketName => $bucket)
            @if($bucket['advances']->count() > 0)
                <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                    <div class="bg-gradient-to-r 
                        @if($bucketName === '0-30') from-green-500 to-green-600
                        @elseif($bucketName === '31-60') from-yellow-500 to-yellow-600
                        @elseif($bucketName === '61-90') from-orange-500 to-orange-600
                        @elseif($bucketName === '91-180') from-red-500 to-red-600
                        @else from-red-700 to-red-800
                        @endif
                        text-white p-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold">{{ $bucketName }} Days Outstanding</h3>
                            <div class="text-right">
                                <div class="text-2xl font-bold">{{ $bucket['advances']->count() }} advances</div>
                                <div class="text-sm">${{ number_format($bucket['total'], 0) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-600">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reference</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Balance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Days Outstanding</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monthly Deduction</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Progress</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($bucket['advances'] as $advance)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $advance->employee->first_name }} {{ $advance->employee->last_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $advance->advance_reference }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            ${{ number_format($advance->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                            ${{ number_format($advance->balance_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($advance->first_deduction_month)->diffInDays(now()) }}
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    @if($reportData['total_active_advances'] === 0)
        <div class="text-center py-8">
            <div class="text-gray-500 dark:text-gray-400">No active advances found.</div>
        </div>
    @endif
</div>
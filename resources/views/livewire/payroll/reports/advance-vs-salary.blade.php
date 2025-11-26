<!-- Advance vs Salary Analysis -->
<div>
    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $reportData['summary']['employees_with_advances'] }}/{{ $reportData['summary']['total_employees'] }}</div>
            <div class="text-sm text-blue-600 dark:text-blue-400 mt-1">Employees with Advances</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($reportData['summary']['avg_advance_to_salary_ratio'], 2) }}</div>
            <div class="text-sm text-green-600 dark:text-green-400 mt-1">Avg Advance/Salary Ratio</div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">${{ number_format($reportData['summary']['total_balance'], 0) }}</div>
            <div class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">Total Outstanding</div>
        </div>
    </div>

    <!-- Risk Distribution -->
    <div class="bg-white dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Risk Distribution</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $lowRisk = $reportData['employees']->filter(fn($e) => $e['balance_to_salary_ratio'] < 0.5)->count();
                $mediumRisk = $reportData['employees']->filter(fn($e) => $e['balance_to_salary_ratio'] >= 0.5 && $e['balance_to_salary_ratio'] < 1)->count();
                $highRisk = $reportData['employees']->filter(fn($e) => $e['balance_to_salary_ratio'] >= 1 && $e['balance_to_salary_ratio'] < 2)->count();
                $criticalRisk = $reportData['employees']->filter(fn($e) => $e['balance_to_salary_ratio'] >= 2)->count();
                $totalWithAdvances = $reportData['employees']->filter(fn($e) => $e['advance_count'] > 0)->count();
            @endphp
            
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $lowRisk }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Low Risk (&lt;50%)</div>
                <div class="text-xs text-gray-500">{{ $totalWithAdvances > 0 ? round(($lowRisk / $totalWithAdvances) * 100, 1) : 0 }}%</div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $mediumRisk }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Medium Risk (50-100%)</div>
                <div class="text-xs text-gray-500">{{ $totalWithAdvances > 0 ? round(($mediumRisk / $totalWithAdvances) * 100, 1) : 0 }}%</div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $highRisk }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">High Risk (100-200%)</div>
                <div class="text-xs text-gray-500">{{ $totalWithAdvances > 0 ? round(($highRisk / $totalWithAdvances) * 100, 1) : 0 }}%</div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600">{{ $criticalRisk }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Critical Risk (&gt;200%)</div>
                <div class="text-xs text-gray-500">{{ $totalWithAdvances > 0 ? round(($criticalRisk / $totalWithAdvances) * 100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>

    <!-- Employee Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monthly Salary</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Advances</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Outstanding</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Advance Count</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Advance/Salary</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Balance/Salary</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Risk Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Avg Advance</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($reportData['employees']->sortByDesc('balance_to_salary_ratio') as $emp)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $emp['employee']->first_name }} {{ $emp['employee']->last_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($emp['monthly_salary'], 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($emp['total_advances'], 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                            ${{ number_format($emp['total_balance'], 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $emp['advance_count'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ number_format($emp['advance_to_salary_ratio'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ number_format($emp['balance_to_salary_ratio'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($emp['balance_to_salary_ratio'] < 0.5)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Low
                                </span>
                            @elseif($emp['balance_to_salary_ratio'] < 1)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Medium
                                </span>
                            @elseif($emp['balance_to_salary_ratio'] < 2)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                    High
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Critical
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            ${{ number_format($emp['avg_advance_amount'], 0) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Key Insights -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
            <h4 class="font-semibold text-red-900 dark:text-red-300 mb-2">Critical Risk Employees</h4>
            @php
                $criticalEmployees = $reportData['employees']->filter(fn($e) => $e['balance_to_salary_ratio'] >= 2);
            @endphp
            <div class="text-sm text-red-800 dark:text-red-200">
                <div class="text-2xl font-bold">{{ $criticalEmployees->count() }}</div>
                <div>employees with outstanding balance > 200% of monthly salary</div>
                @if($criticalEmployees->count() > 0)
                    <div class="mt-2">
                        <strong>Total at risk:</strong> ${{ number_format($criticalEmployees->sum('total_balance'), 0) }}
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
            <h4 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">Advance Utilization</h4>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <div class="text-2xl font-bold">{{ $totalWithAdvances > 0 ? round(($totalWithAdvances / $reportData['summary']['total_employees']) * 100, 1) : 0 }}%</div>
                <div>of employees have taken advances</div>
                <div class="mt-2">
                    <strong>Average per employee:</strong> ${{ number_format($reportData['summary']['total_advances'] / max($totalWithAdvances, 1), 0) }}
                </div>
            </div>
        </div>

        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
            <h4 class="font-semibold text-green-900 dark:text-green-300 mb-2">Salary Impact</h4>
            <div class="text-sm text-green-800 dark:text-green-200">
                <div class="text-2xl font-bold">{{ number_format(($reportData['summary']['total_balance'] / max($reportData['summary']['total_monthly_salary'], 1)) * 100, 1) }}%</div>
                <div>of total monthly payroll</div>
                <div class="mt-2">
                    <strong>Monthly deductions:</strong> ${{ number_format($reportData['employees']->sum(fn($e) => $e['advance_count'] > 0 ? $e['avg_advance_amount'] / 12 : 0), 0) }}
                </div>
            </div>
        </div>
    </div>

    @if($reportData['employees']->count() === 0)
        <div class="text-center py-8">
            <div class="text-gray-500 dark:text-gray-400">No employee data available for the selected period.</div>
        </div>
    @endif
</div>
<!-- Outstanding Advances -->
<div>
    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $reportData['summary']['total_count'] }}</div>
            <div class="text-sm text-blue-600 dark:text-blue-400 mt-1">Total Outstanding</div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">${{ number_format($reportData['summary']['total_outstanding'], 0) }}</div>
            <div class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">Total Amount</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $reportData['summary']['low_risk_count'] }}</div>
            <div class="text-sm text-green-600 dark:text-green-400 mt-1">Low Risk</div>
        </div>
        
        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border border-orange-200 dark:border-orange-800">
            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $reportData['summary']['high_risk_count'] }}</div>
            <div class="text-sm text-orange-600 dark:text-orange-400 mt-1">High Risk</div>
        </div>
        
        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $reportData['summary']['critical_risk_count'] }}</div>
            <div class="text-sm text-red-600 dark:text-red-400 mt-1">Critical Risk</div>
        </div>
    </div>

    <!-- Risk Categories -->
    <div class="space-y-6">
        @foreach(['critical' => ['Critical Risk (> 3 months salary)', 'from-red-700 to-red-800', 'text-red-100'], 
                  'high' => ['High Risk (2-3 months salary)', 'from-orange-600 to-orange-700', 'text-orange-100'], 
                  'medium' => ['Medium Risk (1-2 months salary)', 'from-yellow-600 to-yellow-700', 'text-yellow-100'], 
                  'low' => ['Low Risk (< 1 month salary)', 'from-green-600 to-green-700', 'text-green-100']] as $riskKey => [$riskLabel, $gradientClass, $textClass])
            @php
                $riskAdvances = $reportData['risk_categories'][$riskKey];
                $riskTotal = $riskAdvances->sum('balance_amount');
            @endphp
            
            @if($riskAdvances->count() > 0)
                <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                    <div class="bg-gradient-to-r {{ $gradientClass }} {{ $textClass }} p-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold">{{ $riskLabel }}</h3>
                            <div class="text-right">
                                <div class="text-2xl font-bold">{{ $riskAdvances->count() }} advances</div>
                                <div>${{ number_format($riskTotal, 0) }}</div>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monthly Salary</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Balance/Salary Ratio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monthly Deduction</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Months Remaining</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Progress</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($riskAdvances as $advance)
                                    @php
                                        $monthlySalary = $advance->employee->salaryStructure?->basic_salary ?? 0;
                                        $ratio = $monthlySalary > 0 ? $advance->balance_amount / $monthlySalary : 0;
                                        $monthsRemaining = $advance->remaining_months;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $advance->employee->first_name }} {{ $advance->employee->last_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $advance->advance_reference }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            ${{ number_format($advance->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                            ${{ number_format($advance->balance_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            ${{ number_format($monthlySalary, 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($ratio < 0.5) bg-green-100 text-green-800
                                                @elseif($ratio < 1) bg-yellow-100 text-yellow-800
                                                @elseif($ratio < 2) bg-orange-100 text-orange-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ number_format($ratio, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            ${{ number_format($advance->monthly_deduction, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $monthsRemaining }}
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

    <!-- Risk Summary Chart -->
    <div class="mt-6 bg-white dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Risk Distribution</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $totalWithRisk = $reportData['summary']['total_count'];
            @endphp
            <div class="text-center">
                <div class="h-32 flex items-end justify-center">
                    <div class="w-full bg-green-500 rounded-t" style="height: {{ $totalWithRisk > 0 ? ($reportData['summary']['low_risk_count'] / $totalWithRisk) * 100 : 0 }}%"></div>
                </div>
                <div class="mt-2">
                    <div class="font-semibold text-green-600">{{ $reportData['summary']['low_risk_count'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Low Risk</div>
                    <div class="text-xs text-gray-500">{{ $totalWithRisk > 0 ? round(($reportData['summary']['low_risk_count'] / $totalWithRisk) * 100, 1) : 0 }}%</div>
                </div>
            </div>
            
            <div class="text-center">
                <div class="h-32 flex items-end justify-center">
                    <div class="w-full bg-yellow-500 rounded-t" style="height: {{ $totalWithRisk > 0 ? ($reportData['summary']['medium_risk_count'] / $totalWithRisk) * 100 : 0 }}%"></div>
                </div>
                <div class="mt-2">
                    <div class="font-semibold text-yellow-600">{{ $reportData['summary']['medium_risk_count'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Medium Risk</div>
                    <div class="text-xs text-gray-500">{{ $totalWithRisk > 0 ? round(($reportData['summary']['medium_risk_count'] / $totalWithRisk) * 100, 1) : 0 }}%</div>
                </div>
            </div>
            
            <div class="text-center">
                <div class="h-32 flex items-end justify-center">
                    <div class="w-full bg-orange-500 rounded-t" style="height: {{ $totalWithRisk > 0 ? ($reportData['summary']['high_risk_count'] / $totalWithRisk) * 100 : 0 }}%"></div>
                </div>
                <div class="mt-2">
                    <div class="font-semibold text-orange-600">{{ $reportData['summary']['high_risk_count'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">High Risk</div>
                    <div class="text-xs text-gray-500">{{ $totalWithRisk > 0 ? round(($reportData['summary']['high_risk_count'] / $totalWithRisk) * 100, 1) : 0 }}%</div>
                </div>
            </div>
            
            <div class="text-center">
                <div class="h-32 flex items-end justify-center">
                    <div class="w-full bg-red-500 rounded-t" style="height: {{ $totalWithRisk > 0 ? ($reportData['summary']['critical_risk_count'] / $totalWithRisk) * 100 : 0 }}%"></div>
                </div>
                <div class="mt-2">
                    <div class="font-semibold text-red-600">{{ $reportData['summary']['critical_risk_count'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Critical Risk</div>
                    <div class="text-xs text-gray-500">{{ $totalWithRisk > 0 ? round(($reportData['summary']['critical_risk_count'] / $totalWithRisk) * 100, 1) : 0 }}%</div>
                </div>
            </div>
        </div>
    </div>

    @if($reportData['summary']['total_count'] === 0)
        <div class="text-center py-8">
            <div class="text-gray-500 dark:text-gray-400">No outstanding advances found.</div>
        </div>
    @endif
</div>
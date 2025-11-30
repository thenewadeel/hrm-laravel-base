<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📊 Analytics
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Organization analytics and performance metrics
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Analytics Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Analytics</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Track your organization's performance and trends
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Headcount Trend -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Headcount Trend</h3>
                    <div class="mt-5">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-6 gap-4">
                                @foreach($analytics['headcount_trend']['labels'] as $index => $label)
                                    <div class="text-center">
                                        <div class="text-sm text-gray-500">{{ $label }}</div>
                                        <div class="text-lg font-semibold text-gray-900">{{ $analytics['headcount_trend']['data'][$index] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Performance Metrics</h3>
                    <div class="mt-5">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-500">Attendance Rate</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">95.2%</div>
                                <div class="mt-1 text-sm text-green-600">↑ 2.1% from last month</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-500">Productivity</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">87.5%</div>
                                <div class="mt-1 text-sm text-green-600">↑ 1.3% from last month</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-500">Employee Satisfaction</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">4.2/5</div>
                                <div class="mt-1 text-sm text-yellow-600">→ Same as last month</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Performance -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Department Performance</h3>
                    <div class="mt-5">
                        <div class="space-y-4">
                            @foreach($analytics['department_performance']['labels'] as $index => $department)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900">{{ $department }}</div>
                                        <div class="mt-1">
                                            <div class="flex items-center">
                                                <span class="text-sm text-gray-500">Productivity:</span>
                                                <span class="ml-2 text-sm font-medium text-gray-900">{{ $analytics['department_performance']['productivity'][$index] }}%</span>
                                            </div>
                                            <div class="flex items-center mt-1">
                                                <span class="text-sm text-gray-500">Efficiency:</span>
                                                <span class="ml-2 text-sm font-medium text-gray-900">{{ $analytics['department_performance']['efficiency'][$index] }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="h-16 w-16 rounded-full border-4 border-blue-500 flex items-center justify-center">
                                            <span class="text-sm font-medium text-blue-600">{{ round(($analytics['department_performance']['productivity'][$index] + $analytics['department_performance']['efficiency'][$index]) / 2) }}%</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
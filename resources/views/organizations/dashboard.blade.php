<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🏢 {{ $organization->name }} Dashboard
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Overview of your organization's performance and activities
                </p>
            </div>
            <div class="flex space-x-2">
                <x-button.outline>
                    <x-heroicon-s-cog-6-tooth class="w-4 h-4 mr-2" />
                    Settings
                </x-button.outline>
                <x-button.primary>
                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                    Generate Report
                </x-button.primary>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
                <x-dashboard.stat-card title="Total Units" :value="$summary['total_units']" icon="🏢" trend="+2"
                    trendDirection="up" />
                <x-dashboard.stat-card title="Employees" :value="$summary['total_employees']" icon="👥" trend="+12"
                    trendDirection="up" />
                <x-dashboard.stat-card title="New Enrollments" :value="$summary['new_enrollments']" icon="📥" trend="+3"
                    trendDirection="up" />
                <x-dashboard.stat-card title="Attendance Rate" :value="$summary['attendance_rate'] . '%'" icon="✅" trend="+2.5%"
                    trendDirection="up" />
                <x-dashboard.stat-card title="Active Projects" :value="$summary['active_projects']" icon="🚀" trend="+1"
                    trendDirection="up" />
                <x-dashboard.stat-card title="Completion Rate" :value="$summary['completion_rate'] . '%'" icon="📊" trend="+5.2%"
                    trendDirection="up" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Attendance Chart -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold flex items-center">
                                📈 Attendance Overview
                                <span class="ml-2 text-sm font-normal text-gray-500">Last 7 days</span>
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="h-64">
                                <!-- Chart placeholder -->
                                <div class="w-full h-full bg-gray-50 rounded-lg flex items-center justify-center">
                                    <div class="text-center text-gray-500">
                                        <x-heroicon-s-chart-bar class="mx-auto h-12 w-12 text-gray-400" />
                                        <p class="mt-2">Attendance chart visualization</p>
                                        <p class="text-sm">Present: {{ array_sum($attendanceData['present']) }} |
                                            Absent: {{ array_sum($attendanceData['absent']) }} |
                                            Late: {{ array_sum($attendanceData['late']) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Unit Performance -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold">🏆 Unit Performance</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Employees</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Attendance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Productivity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Projects</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($unitPerformance as $unit)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-medium text-gray-900">{{ $unit['name'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $unit['employees'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                        <div class="bg-green-500 h-2 rounded-full"
                                                            style="width: {{ $unit['attendance'] }}%"></div>
                                                    </div>
                                                    <span class="text-sm font-medium">{{ $unit['attendance'] }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                        <div class="bg-blue-500 h-2 rounded-full"
                                                            style="width: {{ $unit['productivity'] }}%"></div>
                                                    </div>
                                                    <span
                                                        class="text-sm font-medium">{{ $unit['productivity'] }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $unit['projects'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold">⚡ Quick Actions</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <x-button.primary class="w-full justify-start">
                                <x-heroicon-s-user-plus class="w-4 h-4 mr-2" />
                                Add New Employee
                            </x-button.primary>
                            <x-button.secondary class="w-full justify-start">
                                <x-heroicon-s-document-plus class="w-4 h-4 mr-2" />
                                Create Unit
                            </x-button.secondary>
                            <x-button.secondary class="w-full justify-start">
                                <x-heroicon-s-calendar class="w-4 h-4 mr-2" />
                                Schedule Event
                            </x-button.secondary>
                            <x-button.outline class="w-full justify-start">
                                <x-heroicon-s-chart-bar class="w-4 h-4 mr-2" />
                                Generate Report
                            </x-button.outline>
                        </div>
                    </div>

                    <!-- Recent Enrollments -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-semibold">🆕 Recent Enrollments</h3>
                                <x-button.link size="sm">View All</x-button.link>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach ($recentEnrollments as $enrollment)
                                <div class="p-4 hover:bg-gray-50">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full" src="{{ $enrollment['avatar'] }}"
                                            alt="">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $enrollment['name'] }}
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $enrollment['position'] }}</div>
                                            <div class="text-xs text-gray-400">{{ $enrollment['unit'] }}</div>
                                        </div>
                                        <div class="ml-auto text-right">
                                            <div class="text-xs text-gray-500">
                                                {{ $enrollment['enrollment_date']->diffForHumans() }}</div>
                                            <div class="text-xs text-green-600 font-medium">Active</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold">📋 Quick Stats</h3>
                        </div>
                        <div class="p-6 grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ $quickStats['pending_requests'] }}
                                </div>
                                <div class="text-sm text-blue-700">Pending Requests</div>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                                <div class="text-2xl font-bold text-yellow-600">{{ $quickStats['upcoming_leaves'] }}
                                </div>
                                <div class="text-sm text-yellow-700">Upcoming Leaves</div>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ $quickStats['training_sessions'] }}
                                </div>
                                <div class="text-sm text-green-700">Training Sessions</div>
                            </div>
                            <div class="text-center p-4 bg-purple-50 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">
                                    {{ $quickStats['active_recruitments'] }}</div>
                                <div class="text-sm text-purple-700">Active Recruitments</div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold">📅 Upcoming Events</h3>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach ($upcomingEvents as $event)
                                <div class="p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $event['title'] }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $event['date']->format('M j') }} • {{ $event['time'] }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                {{ $event['attendees'] }} attendees
                                            </div>
                                        </div>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucfirst($event['type']) }}
                                        </span>
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

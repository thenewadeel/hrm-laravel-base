<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🏢 Team Reports
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Record of your team's performance
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Team Reports & Analytics
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive reports and insights for your team</p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <!-- Report Period -->
                    <div>
                        <label for="report-period" class="sr-only">Report Period</label>
                        <select id="report-period" onchange="loadReports(this.value)"
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_quarter">This Quarter</option>
                            <option value="last_quarter">Last Quarter</option>
                            <option value="this_year">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <button onclick="generateComprehensiveReport()"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Generate Report
                    </button>
                </div>
            </div>

            <!-- Custom Date Range (Hidden by Default) -->
            <div id="custom-date-range" class="hidden bg-white p-4 rounded-lg shadow mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label for="start-date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="start-date"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="end-date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="end-date"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <button onclick="loadCustomReports()"
                            class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Apply Dates
                        </button>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- Team Attendance Rate -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Team Attendance</dt>
                                    <dd class="text-lg font-semibold text-gray-900" id="team-attendance-rate">0%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Hours -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Avg. Daily Hours</dt>
                                    <dd class="text-lg font-semibold text-gray-900" id="avg-daily-hours">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overtime Hours -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Overtime Hours</dt>
                                    <dd class="text-lg font-semibold text-gray-900" id="overtime-hours">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Days -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Leave Days Taken</dt>
                                    <dd class="text-lg font-semibold text-gray-900" id="leave-days">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Detailed Reports -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Attendance Trend Chart -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Attendance Trend</h3>
                    <div id="attendance-trend-chart" class="h-64">
                        <!-- Chart will be rendered here -->
                        <div class="flex items-center justify-center h-full text-gray-500">
                            Loading attendance trend...
                        </div>
                    </div>
                </div>

                <!-- Leave Distribution -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Leave Distribution</h3>
                    <div id="leave-distribution-chart" class="h-64">
                        <!-- Chart will be rendered here -->
                        <div class="flex items-center justify-center h-full text-gray-500">
                            Loading leave distribution...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Team Performance Summary
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Employee</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Present Days</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Late Days</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Absent Days</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Avg. Hours/Day</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Overtime Hours</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Leave Days</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Performance</th>
                            </tr>
                        </thead>
                        <tbody id="team-performance-body" class="bg-white divide-y divide-gray-200">
                            <!-- Data will be loaded via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Loading State -->
                <div id="reports-loading" class="p-8 text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-sm text-gray-500">Loading team performance data...</p>
                </div>
            </div>

            <!-- Export Options -->
            <div class="mt-8 bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Export Reports</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button onclick="exportAttendanceReport()"
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Attendance Report
                    </button>
                    <button onclick="exportLeaveReport()"
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Leave Report
                    </button>
                    <button onclick="exportPerformanceReport()"
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Performance Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load reports on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadReports('this_month');
        });

        function loadReports(period) {
            showReportsLoading();

            if (period === 'custom') {
                document.getElementById('custom-date-range').classList.remove('hidden');
                return;
            } else {
                document.getElementById('custom-date-range').classList.add('hidden');
            }

            fetch(`/portal/manager/reports/data?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateMetrics(data.metrics);
                        renderCharts(data.charts);
                        renderPerformanceTable(data.performance);
                    }
                    hideReportsLoading();
                })
                .catch(error => {
                    console.error('Error loading reports:', error);
                    hideReportsLoading();
                });
        }

        function loadCustomReports() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;

            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }

            showReportsLoading();

            fetch(`/portal/manager/reports/data?start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateMetrics(data.metrics);
                        renderCharts(data.charts);
                        renderPerformanceTable(data.performance);
                    }
                    hideReportsLoading();
                });
        }

        function updateMetrics(metrics) {
            document.getElementById('team-attendance-rate').textContent = metrics.attendance_rate + '%';
            document.getElementById('avg-daily-hours').textContent = metrics.avg_daily_hours + ' hrs';
            document.getElementById('overtime-hours').textContent = metrics.overtime_hours + ' hrs';
            document.getElementById('leave-days').textContent = metrics.leave_days;
        }

        function renderCharts(charts) {
            // Implementation for rendering charts using Chart.js or other library
            console.log('Rendering charts:', charts);
            // This would typically use a charting library like Chart.js
        }

        function renderPerformanceTable(performance) {
            const tbody = document.getElementById('team-performance-body');

            tbody.innerHTML = performance.map(employee => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-gray-600 font-medium text-xs">${employee.initials}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${employee.name}</div>
                        <div class="text-sm text-gray-500">${employee.position}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${employee.present_days}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${employee.late_days}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${employee.absent_days}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${employee.avg_hours_per_day}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${employee.overtime_hours}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${employee.leave_days}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getPerformanceClass(employee.performance)}">
                    ${employee.performance}%
                </span>
            </td>
        </tr>
    `).join('');
        }

        function getPerformanceClass(performance) {
            if (performance >= 90) return 'bg-green-100 text-green-800';
            if (performance >= 80) return 'bg-yellow-100 text-yellow-800';
            return 'bg-red-100 text-red-800';
        }

        function showReportsLoading() {
            document.getElementById('reports-loading').classList.remove('hidden');
        }

        function hideReportsLoading() {
            document.getElementById('reports-loading').classList.add('hidden');
        }

        function generateComprehensiveReport() {
            const period = document.getElementById('report-period').value;
            let url = `/portal/manager/reports/comprehensive?period=${period}`;

            if (period === 'custom') {
                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                if (startDate && endDate) {
                    url += `&start_date=${startDate}&end_date=${endDate}`;
                }
            }

            window.open(url, '_blank');
        }

        function exportAttendanceReport() {
            const period = document.getElementById('report-period').value;
            window.open(`/portal/manager/reports/attendance/export?period=${period}`, '_blank');
        }

        function exportLeaveReport() {
            const period = document.getElementById('report-period').value;
            window.open(`/portal/manager/reports/leave/export?period=${period}`, '_blank');
        }

        function exportPerformanceReport() {
            const period = document.getElementById('report-period').value;
            window.open(`/portal/manager/reports/performance/export?period=${period}`, '_blank');
        }
    </script>
</x-app-layout>

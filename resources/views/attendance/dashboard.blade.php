<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏠 {{ __('Attendance & Time Tracking') }}
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Attendance Dashboard
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Manage attendance sync and resolve exceptions before payroll
                        processing</p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Sync Biometric Data
                    </button>
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Export for Payroll
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                <!-- Present Today -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Present Today</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $presentToday ?? '0' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Absent Today -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Absent Today</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $absentToday ?? '0' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Late Arrivals -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Late Arrivals</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $lateToday ?? '0' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Hours -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 6v1m0-1v1" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Hours</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $totalHours ?? '0' }} hrs</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Exceptions Section -->
            @if (request('show_exceptions'))
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-yellow-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-yellow-800">Attendance Exceptions</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Review and resolve attendance exceptions before payroll processing.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Exception Stats -->
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="text-sm font-medium text-gray-500">Late Arrivals</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $lateExceptions ?? '0' }}</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="text-sm font-medium text-gray-500">Missed Punches</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $missedPunchExceptions ?? '0' }}
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="text-sm font-medium text-gray-500">Total Minutes Late</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $totalLateMinutes ?? '0' }} min
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Attendance Summary Section -->
            @if (request('employee_id'))
                <div class="bg-white shadow sm:rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Attendance Summary
                        </h3>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Present Days</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $presentDays ?? '0' }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Absent Days</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $absentDays ?? '0' }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Late Days</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $lateDays ?? '0' }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Total Hours</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $summaryTotalHours ?? '0' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <form method="GET" action="{{ route('attendance.dashboard') }}">
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Date Range -->

                            <!-- In the filters section, add display of selected date range -->
                            @if (isset($startDateObj) && isset($endDateObj))
                                <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-700">
                                        Showing records from <strong>{{ $startDateObj->format('M j, Y') }}</strong> to
                                        <strong>{{ $endDateObj->format('M j, Y') }}</strong>
                                    </p>
                                </div>
                            @endif
                            <div class="sm:col-span-2">
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start
                                    Date</label>
                                <div class="mt-1">
                                    <input type="date" id="start_date" name="start_date"
                                        value="{{ request('start_date') }}"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <div class="mt-1">
                                    <input type="date" id="end_date" name="end_date"
                                        value="{{ request('end_date') }}"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>

                            <!-- Employee Filter -->
                            <div class="sm:col-span-2">
                                <label for="employee_id"
                                    class="block text-sm font-medium text-gray-700">Employee</label>
                                <select id="employee_id" name="employee_id"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">All Employees</option>
                                    <!-- Employee options would be populated dynamically -->
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-3">
                            <a href="{{ route('attendance.dashboard') }}"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Clear
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Attendance Grid -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Attendance Records
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">Review and manage attendance exceptions before payroll lock
                    </p>
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
                                    Date</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Punch In</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Punch Out</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Hours</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($attendanceRecords ?? [] as $record)
                                <tr
                                    class="@if ($record['status'] === 'late') bg-yellow-50 @elseif($record['status'] === 'absent') bg-red-50 @elseif($record['status'] === 'missed_punch') bg-orange-50 @endif">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-gray-600 font-medium text-sm">
                                                        {{ substr($record['employee_name'] ?? 'Unknown', 0, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $record['employee_name'] ?? 'Unknown Employee' }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $record['department'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($record['record_date'])->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record['punch_in'] ? \Carbon\Carbon::parse($record['punch_in'])->format('h:i A') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record['punch_out'] ? \Carbon\Carbon::parse($record['punch_out'])->format('h:i A') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record['total_hours'] ? $record['total_hours'] . ' hrs' : '0.0 hrs' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusConfig = [
                                                'present' => [
                                                    'class' => 'bg-green-100 text-green-800',
                                                    'label' => 'Present',
                                                ],
                                                'late' => [
                                                    'class' => 'bg-yellow-100 text-yellow-800',
                                                    'label' => 'LATE (' . ($record['late_minutes'] ?? 0) . 'm)',
                                                ],
                                                'absent' => [
                                                    'class' => 'bg-red-100 text-red-800',
                                                    'label' => 'ABSENT (LOP)',
                                                ],
                                                'missed_punch' => [
                                                    'class' => 'bg-orange-100 text-orange-800',
                                                    'label' => 'MISSED PUNCH',
                                                ],
                                            ];
                                            $config = $statusConfig[$record['status']] ?? [
                                                'class' => 'bg-gray-100 text-gray-800',
                                                'label' => $record['status'],
                                            ];
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['class'] }}">
                                            {{ $config['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if ($record['status'] === 'missed_punch' || $record['status'] === 'late')
                                            <button class="text-blue-600 hover:text-blue-900">Regularize Time</button>
                                        @elseif($record['status'] === 'absent')
                                            <button class="text-blue-600 hover:text-blue-900 mr-3">Apply Leave</button>
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No attendance records found for the selected criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

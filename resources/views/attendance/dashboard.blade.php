<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    🏠 {{ __('Attendance & Time Tracking') }}
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Monitor attendance, track time, and manage exceptions
                </p>
            </div>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-primary sm:text-3xl sm:truncate">
                        Attendance Dashboard
                    </h2>
                    <p class="mt-1 text-sm text-secondary">Manage attendance sync and resolve exceptions before payroll processing</p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <form method="POST" action="{{ route('attendance.biometric-sync') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Sync Biometric Data
                        </button>
                    </form>
                    <form method="GET" action="{{ route('attendance.export-payroll') }}" class="inline">
                        <input type="hidden" name="period" value="{{ isset($startDateObj) ? $startDateObj->format('Y-m') : now()->format('Y-m') }}">
                        @if(request('employee_id'))
                            <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                        @endif
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export for Payroll
                        </button>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                <!-- Present Today -->
                <div class="surface overflow-hidden shadow rounded-lg">
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
                                    <dt class="text-sm font-medium text-secondary truncate">Present Today</dt>
                                    <dd class="text-lg font-semibold text-primary">{{ $presentToday ?? '0' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Absent Today -->
                <div class="surface overflow-hidden shadow rounded-lg">
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
                                    <dt class="text-sm font-medium text-secondary truncate">Absent Today</dt>
                                    <dd class="text-lg font-semibold text-primary">{{ $absentToday ?? '0' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Late Arrivals -->
                <div class="surface overflow-hidden shadow rounded-lg">
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
                                    <dt class="text-sm font-medium text-secondary truncate">Late Arrivals</dt>
                                    <dd class="text-lg font-semibold text-primary">{{ $lateToday ?? '0' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Hours -->
                <div class="surface overflow-hidden shadow rounded-lg">
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
                                    <dt class="text-sm font-medium text-secondary truncate">Total Hours</dt>
                                    <dd class="text-lg font-semibold text-primary">{{ $totalHours ?? '0' }} hrs</dd>
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
            <div class="surface shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <form method="GET" action="{{ route('attendance.dashboard') }}">
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Date Range -->
                            @if (isset($startDateObj) && isset($endDateObj))
                                <div class="sm:col-span-6 mb-4 p-4 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-700">
                                        Showing records from <strong>{{ $startDateObj->format('M j, Y') }}</strong> to
                                        <strong>{{ $endDateObj->format('M j, Y') }}</strong>
                                        @if(request('employee_id'))
                                            for <strong>{{ $employees->where('id', request('employee_id'))->first()['name'] ?? 'Selected Employee' }}</strong>
                                        @endif
                                    </p>
                                </div>
                            @endif
                            
                            <div class="sm:col-span-2">
                                <label for="start_date" class="block text-sm font-medium text-primary">Start Date</label>
                                <div class="mt-1">
                                    <input type="date" id="start_date" name="start_date"
                                        value="{{ request('start_date') ?? $filters['start_date'] ?? '' }}"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-secondary rounded-md">
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="end_date" class="block text-sm font-medium text-primary">End Date</label>
                                <div class="mt-1">
                                    <input type="date" id="end_date" name="end_date"
                                        value="{{ request('end_date') ?? $filters['end_date'] ?? '' }}"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-secondary rounded-md">
                                </div>
                            </div>

                            <!-- Employee Filter -->
                            <div class="sm:col-span-2">
                                <label for="employee_id" class="block text-sm font-medium text-primary">Employee</label>
                                <select id="employee_id" name="employee_id"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-secondary focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">All Employees</option>
                                    @foreach($employees ?? [] as $employee)
                                        <option value="{{ $employee['id'] }}" {{ request('employee_id') == $employee['id'] ? 'selected' : '' }}>
                                            {{ $employee['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Search Filter -->
                            <div class="sm:col-span-3">
                                <label for="search" class="block text-sm font-medium text-primary">Search Employee</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" id="search" name="search" 
                                        value="{{ request('search') }}"
                                        placeholder="Search by employee name..."
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-secondary rounded-md">
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div class="sm:col-span-2">
                                <label for="status" class="block text-sm font-medium text-primary">Status</label>
                                <select id="status" name="status"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-secondary focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">All Status</option>
                                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                    <option value="missed_punch" {{ request('status') == 'missed_punch' ? 'selected' : '' }}>Missed Punch</option>
                                    <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>On Leave</option>
                                </select>
                            </div>

                            <!-- Show Exceptions Checkbox -->
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-primary">&nbsp;</label>
                                <div class="mt-1">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="show_exceptions" value="1" 
                                            {{ request('show_exceptions') ? 'checked' : '' }}
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-secondary rounded">
                                        <span class="ml-2 text-sm text-primary">Show Exceptions Only</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between">
                            <div class="space-x-3">
                                <button type="button" onclick="setQuickDateRange('today')"
                                    class="inline-flex items-center px-3 py-2 border border-secondary rounded-md shadow-sm text-xs font-medium text-primary bg-surface hover:bg-tertiary">
                                    Today
                                </button>
                                <button type="button" onclick="setQuickDateRange('week')"
                                    class="inline-flex items-center px-3 py-2 border border-secondary rounded-md shadow-sm text-xs font-medium text-primary bg-surface hover:bg-tertiary">
                                    This Week
                                </button>
                                <button type="button" onclick="setQuickDateRange('month')"
                                    class="inline-flex items-center px-3 py-2 border border-secondary rounded-md shadow-sm text-xs font-medium text-primary bg-surface hover:bg-tertiary">
                                    This Month
                                </button>
                            </div>
                            <div class="space-x-3">
                                <a href="{{ route('attendance.dashboard') }}"
                                    class="inline-flex items-center px-4 py-2 border border-secondary rounded-md shadow-sm text-sm font-medium text-primary bg-surface hover:bg-tertiary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Clear All
                                </a>
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Attendance Grid -->
            <div class="surface shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-secondary">
                    <h3 class="text-lg leading-6 font-medium text-primary">
                        Attendance Records
                    </h3>
                    <p class="mt-1 text-sm text-secondary">Review and manage attendance exceptions before payroll lock</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-tertiary">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                    Employee</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                    Date</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                    Punch In</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                    Punch Out</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                    Total Hours</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                    Status</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="surface divide-y divide-secondary">
                            @forelse($attendanceRecords ?? [] as $record)
                                <tr
                                    class="@if ($record['status'] === 'late') bg-yellow-50 @elseif($record['status'] === 'absent') bg-red-50 @elseif($record['status'] === 'missed_punch') bg-orange-50 @endif">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-gray-600 font-medium text-sm">
                                                        {{ substr($record['employee_name'] ?? 'Unknown', 0, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-primary">
                                                    {{ $record['employee_name'] ?? 'Unknown Employee' }}</div>
                                                <div class="text-sm text-secondary">
                                                    {{ $record['department'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        {{ \Carbon\Carbon::parse($record['record_date'])->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        {{ $record['punch_in'] ? \Carbon\Carbon::parse($record['punch_in'])->format('h:i A') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        {{ $record['punch_out'] ? \Carbon\Carbon::parse($record['punch_out'])->format('h:i A') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
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
                                            <button onclick="openRegularizeModal({{ $record['id'] }})" class="text-blue-600 hover:text-blue-900">Regularize Time</button>
                                        @elseif($record['status'] === 'absent')
                                            <button onclick="openApplyLeaveModal({{ $record['id'] }})" class="text-blue-600 hover:text-blue-900 mr-3">Apply Leave</button>
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-secondary">
                                        No attendance records found for selected criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Quick date range functionality
        function setQuickDateRange(range) {
            const today = new Date();
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            let startDate, endDate;
            
            switch(range) {
                case 'today':
                    startDate = endDate = today;
                    break;
                case 'week':
                    const startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() - today.getDay());
                    startDate = startOfWeek;
                    endDate = today;
                    break;
                case 'month':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = today;
                    break;
            }
            
            startDateInput.value = formatDate(startDate);
            endDateInput.value = formatDate(endDate);
            
            // Auto-submit form
            startDateInput.form.submit();
        }
        
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Search functionality for employee dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const employeeSelect = document.getElementById('employee_id');
            
            if (searchInput && employeeSelect) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const options = employeeSelect.options;
                    
                    for (let i = 1; i < options.length; i++) {
                        const option = options[i];
                        const text = option.text.toLowerCase();
                        
                        if (text.includes(searchTerm)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    }
                    
                    // If search term matches an employee exactly, select them
                    for (let i = 1; i < options.length; i++) {
                        const option = options[i];
                        if (option.text.toLowerCase() === searchTerm) {
                            employeeSelect.value = option.value;
                            break;
                        }
                    }
                });
            }
        });

        // Handle regularize time modal
        function openRegularizeModal(recordId) {
            // Create modal dynamically
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
            modal.innerHTML = `
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Regularize Attendance Time</h3>
                        <form method="POST" action="{{ route('attendance.regularize', ':id') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="record_id" value="${recordId}">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Punch In Time</label>
                                <input type="datetime-local" name="punch_in" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Punch Out Time</label>
                                <input type="datetime-local" name="punch_out" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Reason</label>
                                <textarea name="reason" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Enter reason for regularization..."></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    Regularize
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            // Replace :id with actual record ID
            modal.querySelector('form').action = modal.querySelector('form').action.replace(':id', recordId);
            
            document.body.appendChild(modal);
        }

        // Handle apply leave modal
        function openApplyLeaveModal(recordId) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
            modal.innerHTML = `
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Apply Leave</h3>
                        <form method="POST" action="{{ route('attendance.apply-leave', ':id') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="record_id" value="${recordId}">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Leave Type</label>
                                <select name="leave_type" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Select Leave Type</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="casual">Casual Leave</option>
                                    <option value="earned">Earned Leave</option>
                                    <option value="unpaid">Unpaid Leave</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">From Date</label>
                                <input type="date" name="from_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">To Date</label>
                                <input type="date" name="to_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Reason</label>
                                <textarea name="reason" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Enter reason for leave..."></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    Apply Leave
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            // Replace :id with actual record ID
            modal.querySelector('form').action = modal.querySelector('form').action.replace(':id', recordId);
            
            document.body.appendChild(modal);
        }

        function closeModal() {
            const modal = document.querySelector('.fixed.inset-0');
            if (modal) {
                modal.remove();
            }
        }
    </script>
</x-app-layout>

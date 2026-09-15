<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="🏠 {{ __('Attendance & Time Tracking') }}"
            description="{{ __('Monitor attendance, track time, and manage exceptions') }}"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-page-header
                title="{{ __('Attendance Dashboard') }}"
                description="{{ __('Manage attendance sync and resolve exceptions before payroll processing') }}"
            >
                <x-slot name="actions">
                    <form method="POST" action="{{ route('attendance.biometric-sync') }}">
                        @csrf
                        <x-button variant="outline" type="submit">
                            <svg class="mr-2 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            {{ __('Sync Biometric Data') }}
                        </x-button>
                    </form>
                    <form method="GET" action="{{ route('attendance.export-payroll') }}">
                        <input type="hidden" name="period"
                            value="{{ isset($startDateObj) ? $startDateObj->format('Y-m') : now()->format('Y-m') }}">
                        @if (request('employee_id'))
                            <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                        @endif
                        <x-button type="submit">
                            <svg class="mr-2 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('Export for Payroll') }}
                        </x-button>
                    </form>
                </x-slot>
            </x-page-header>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat-card label="{{ __('Present Today') }}" :value="($presentToday ?? '0')"
                    tone="success">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Absent Today') }}" :value="($absentToday ?? '0')"
                    tone="error">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Late Arrivals') }}" :value="($lateToday ?? '0')"
                    tone="warning">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Total Hours') }}"
                    :value="($totalHours ?? '0').' hrs'" tone="info">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 6v1m0-1v1" />
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>
            </div>

            @if (request('show_exceptions'))
                <x-alert type="warning" title="{{ __('Attendance Exceptions') }}"
                    description="{{ __('Review and resolve attendance exceptions before payroll processing.') }}">
                </x-alert>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <x-dashboard.stat-card label="{{ __('Late Arrivals') }}"
                        :value="($lateExceptions ?? '0')" tone="warning" />
                    <x-dashboard.stat-card label="{{ __('Missed Punches') }}"
                        :value="($missedPunchExceptions ?? '0')" tone="error" />
                    <x-dashboard.stat-card label="{{ __('Total Minutes Late') }}"
                        :value="($totalLateMinutes ?? '0').' min'" tone="warning" />
                </div>
            @endif

            @if (request('employee_id'))
                <x-card title="{{ __('Attendance Summary') }}">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div class="text-center">
                            <div class="text-sm font-medium text-muted">{{ __('Present Days') }}</div>
                            <div class="mt-1 text-2xl font-semibold text-primary">{{ $presentDays ?? '0' }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm font-medium text-muted">{{ __('Absent Days') }}</div>
                            <div class="mt-1 text-2xl font-semibold text-primary">{{ $absentDays ?? '0' }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm font-medium text-muted">{{ __('Late Days') }}</div>
                            <div class="mt-1 text-2xl font-semibold text-primary">{{ $lateDays ?? '0' }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm font-medium text-muted">{{ __('Total Hours') }}</div>
                            <div class="mt-1 text-2xl font-semibold text-primary">{{ $summaryTotalHours ?? '0' }}</div>
                        </div>
                    </div>
                </x-card>
            @endif

            <x-card title="{{ __('Filters') }}">
                <form method="GET" action="{{ route('attendance.dashboard') }}">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        @if (isset($startDateObj) && isset($endDateObj))
                            <div class="sm:col-span-6 rounded-lg border border-info/25 bg-info/10 p-4">
                                <p class="text-sm text-info">
                                    {{ __('Showing records from') }}
                                    <strong>{{ $startDateObj->format('M j, Y') }}</strong> {{ __('to') }}
                                    <strong>{{ $endDateObj->format('M j, Y') }}</strong>
                                    @if (request('employee_id'))
                                        {{ __('for') }}
                                        <strong>{{ $employees->where('id', request('employee_id'))->first()['name'] ?? 'Selected Employee' }}</strong>
                                    @endif
                                </p>
                            </div>
                        @endif

                        <div class="sm:col-span-2">
                            <label for="start_date" class="block text-sm font-medium text-primary">{{ __('Start Date') }}</label>
                            <div class="mt-1">
                                <input type="date" id="start_date" name="start_date"
                                    value="{{ request('start_date') ?? $filters['start_date'] ?? '' }}"
                                    class="block w-full rounded-md border-secondary text-sm shadow-sm focus:border-primary focus:ring-primary">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="end_date" class="block text-sm font-medium text-primary">{{ __('End Date') }}</label>
                            <div class="mt-1">
                                <input type="date" id="end_date" name="end_date"
                                    value="{{ request('end_date') ?? $filters['end_date'] ?? '' }}"
                                    class="block w-full rounded-md border-secondary text-sm shadow-sm focus:border-primary focus:ring-primary">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="employee_id" class="block text-sm font-medium text-primary">{{ __('Employee') }}</label>
                            <select id="employee_id" name="employee_id"
                                class="mt-1 block w-full rounded-md border-secondary py-2 pl-3 pr-10 text-base focus:border-primary focus:outline-none focus:ring-primary sm:text-sm">
                                <option value="">{{ __('All Employees') }}</option>
                                @foreach ($employees ?? [] as $employee)
                                    <option value="{{ $employee['id'] }}"
                                        {{ request('employee_id') == $employee['id'] ? 'selected' : '' }}>
                                        {{ $employee['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="search" class="block text-sm font-medium text-primary">{{ __('Search Employee') }}</label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="size-5 text-muted" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" id="search" name="search" value="{{ request('search') }}"
                                    placeholder="{{ __('Search by employee name...') }}"
                                    class="block w-full rounded-md border-secondary pl-10 text-sm shadow-sm focus:border-primary focus:ring-primary">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="status" class="block text-sm font-medium text-primary">{{ __('Status') }}</label>
                            <select id="status" name="status"
                                class="mt-1 block w-full rounded-md border-secondary py-2 pl-3 pr-10 text-base focus:border-primary focus:outline-none focus:ring-primary sm:text-sm">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>{{ __('Present') }}</option>
                                <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>{{ __('Absent') }}</option>
                                <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>{{ __('Late') }}</option>
                                <option value="missed_punch" {{ request('status') == 'missed_punch' ? 'selected' : '' }}>{{ __('Missed Punch') }}</option>
                                <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>{{ __('On Leave') }}</option>
                            </select>
                        </div>

                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-primary">&nbsp;</label>
                            <div class="mt-1">
                                <label class="flex items-center">
                                    <input type="checkbox" name="show_exceptions" value="1"
                                        {{ request('show_exceptions') ? 'checked' : '' }}
                                        class="size-4 rounded border-secondary text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-primary">{{ __('Show Exceptions Only') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap gap-3">
                            <x-button variant="outline" size="sm" type="button"
                                onclick="setQuickDateRange('today')">{{ __('Today') }}</x-button>
                            <x-button variant="outline" size="sm" type="button"
                                onclick="setQuickDateRange('week')">{{ __('This Week') }}</x-button>
                            <x-button variant="outline" size="sm" type="button"
                                onclick="setQuickDateRange('month')">{{ __('This Month') }}</x-button>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <x-button.link variant="outline"
                                href="{{ route('attendance.dashboard') }}">{{ __('Clear All') }}</x-button.link>
                            <x-button type="submit">{{ __('Apply Filters') }}</x-button>
                        </div>
                    </div>
                </form>
            </x-card>

            <x-card title="{{ __('Attendance Records') }}">
                <x-slot name="description">
                    {{ __('Review and manage attendance exceptions before payroll lock') }}
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary">
                        <thead class="bg-tertiary">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary">
                                    {{ __('Employee') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary">
                                    {{ __('Date') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary">
                                    {{ __('Punch In') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary">
                                    {{ __('Punch Out') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary">
                                    {{ __('Total Hours') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary">
                                    {{ __('Status') }}</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary">
                                    {{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="surface divide-y divide-secondary">
                            @forelse ($attendanceRecords ?? [] as $record)
                                @php
                                    $statusColors = [
                                        'present' => 'green',
                                        'late' => 'warning',
                                        'absent' => 'error',
                                        'missed_punch' => 'warning',
                                    ];
                                    $statusLabels = [
                                        'present' => 'Present',
                                        'late' => 'LATE (' . ($record['late_minutes'] ?? 0) . 'm)',
                                        'absent' => 'ABSENT (LOP)',
                                        'missed_punch' => 'MISSED PUNCH',
                                    ];
                                    $status = $record['status'] ?? 'present';
                                    $rowTint = match ($status) {
                                        'late' => 'bg-warning/10',
                                        'absent' => 'bg-error/10',
                                        'missed_punch' => 'bg-warning/10',
                                        default => '',
                                    };
                                @endphp
                                <tr class="{{ $rowTint }}">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex size-10 flex-shrink-0">
                                                <div
                                                    class="flex size-10 items-center justify-center rounded-full bg-bg-tertiary">
                                                    <span class="text-sm font-medium text-secondary">
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
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-primary">
                                        {{ \Carbon\Carbon::parse($record['record_date'])->format('M j, Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-primary">
                                        {{ $record['punch_in'] ? \Carbon\Carbon::parse($record['punch_in'])->format('h:i A') : '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-primary">
                                        {{ $record['punch_out'] ? \Carbon\Carbon::parse($record['punch_out'])->format('h:i A') : '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-primary">
                                        {{ $record['total_hours'] ? $record['total_hours'] . ' hrs' : '0.0 hrs' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <x-badge :color="$statusColors[$status] ?? 'gray'">
                                            {{ $statusLabels[$status] ?? ucfirst($status) }}
                                        </x-badge>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                        @if ($status === 'missed_punch' || $status === 'late')
                                            <button onclick="openRegularizeModal({{ $record['id'] }})"
                                                class="text-sm font-medium text-accent hover:text-primary">{{ __('Regularize Time') }}</button>
                                        @elseif ($status === 'absent')
                                            <button onclick="openApplyLeaveModal({{ $record['id'] }})"
                                                class="mr-3 text-sm font-medium text-accent hover:text-primary">{{ __('Apply Leave') }}</button>
                                        @else
                                            <span class="text-sm text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-secondary">
                                        {{ __('No attendance records found for selected criteria.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    <script>
        function setQuickDateRange(range) {
            const today = new Date();
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            let startDate, endDate;

            switch (range) {
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

            startDateInput.form.submit();
        }

        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

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

        function openRegularizeModal(recordId) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 h-full w-full overflow-y-auto bg-text-muted/50';
            modal.innerHTML = `
                <div class="relative top-20 mx-auto w-96 rounded-md border border-secondary bg-surface p-5 shadow-lg">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium leading-6 text-primary">${'{{ __('Regularize Attendance Time') }}'}</h3>
                        <form method="POST" action="{{ route('attendance.regularize', ':id') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="record_id" value="${recordId}">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-secondary">${'{{ __('Punch In Time') }}'}</label>
                                <input type="datetime-local" name="punch_in" class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-secondary">${'{{ __('Punch Out Time') }}'}</label>
                                <input type="datetime-local" name="punch_out" class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-secondary">${'{{ __('Reason') }}'}</label>
                                <textarea name="reason" rows="3" required class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm" placeholder="${'{{ __('Enter reason for regularization...') }}'}"></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeModal()" class="rounded-md border border-secondary px-4 py-2 text-sm font-medium text-primary hover:bg-secondary">
                                    ${'{{ __('Cancel') }}'}
                                </button>
                                <button type="submit" class="rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-inverse shadow-sm hover:bg-primary-dark">
                                    ${'{{ __('Regularize') }}'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;

            modal.querySelector('form').action = modal.querySelector('form').action.replace(':id', recordId);

            document.body.appendChild(modal);
        }

        function openApplyLeaveModal(recordId) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 h-full w-full overflow-y-auto bg-text-muted/50';
            modal.innerHTML = `
                <div class="relative top-20 mx-auto w-96 rounded-md border border-secondary bg-surface p-5 shadow-lg">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium leading-6 text-primary">${'{{ __('Apply Leave') }}'}</h3>
                        <form method="POST" action="{{ route('attendance.apply-leave', ':id') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="record_id" value="${recordId}">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-secondary">${'{{ __('Leave Type') }}'}</label>
                                <select name="leave_type" required class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                    <option value="">${'{{ __('Select Leave Type') }}'}</option>
                                    <option value="sick">${'{{ __('Sick Leave') }}'}</option>
                                    <option value="casual">${'{{ __('Casual Leave') }}'}</option>
                                    <option value="earned">${'{{ __('Earned Leave') }}'}</option>
                                    <option value="unpaid">${'{{ __('Unpaid Leave') }}'}</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-secondary">${'{{ __('From Date') }}'}</label>
                                <input type="date" name="from_date" required class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-secondary">${'{{ __('To Date') }}'}</label>
                                <input type="date" name="to_date" required class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-secondary">${'{{ __('Reason') }}'}</label>
                                <textarea name="reason" rows="3" required class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm" placeholder="${'{{ __('Enter reason for leave...') }}'}"></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeModal()" class="rounded-md border border-secondary px-4 py-2 text-sm font-medium text-primary hover:bg-secondary">
                                    ${'{{ __('Cancel') }}'}
                                </button>
                                <button type="submit" class="rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-inverse shadow-sm hover:bg-primary-dark">
                                    ${'{{ __('Apply Leave') }}'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;

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
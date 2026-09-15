<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏠 {{ __('Payroll > Processing') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Payroll Run Header -->
            <div class="bg-white shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="md:flex md:items-center md:justify-between">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                                Payroll Processing
                            </h2>
                            <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                                <div class="mt-2 flex items-center text-sm text-gray-500">
                                    <x-heroicon-o-calendar class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
                                    Period: {{ $period->format('F Y') }}
                                </div>
                                @if ($employee)
                                    <div class="mt-2 flex items-center text-sm text-gray-500">
                                        <x-heroicon-o-user class="flex-shrink-0 mr-1.5 h-5 w-5 text-blue-500" />
                                        Employee: {{ $employee->user->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-4">
                            <div class="bg-blue-50 px-4 py-2 rounded-lg">
                                <div class="text-sm font-medium text-blue-500">Total Hours</div>
                                <div class="text-2xl font-bold text-blue-700">
                                    {{ number_format($totalHours, 2) }}
                                </div>
                            </div>
                            <div class="bg-green-50 px-4 py-2 rounded-lg">
                                <div class="text-sm font-medium text-green-500">Regular Hours</div>
                                <div class="text-2xl font-bold text-green-700">
                                    {{ number_format($regularHours, 2) }}
                                </div>
                            </div>
                            <div class="bg-orange-50 px-4 py-2 rounded-lg">
                                <div class="text-sm font-medium text-orange-500">Overtime Hours</div>
                                <div class="text-2xl font-bold text-orange-700">
                                    {{ number_format($overtimeHours, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Attendance Statistics -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Attendance Summary
                            </h3>
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-500">Total Records</div>
                                    <div class="mt-1 text-2xl font-semibold text-gray-900">
                                        {{ $attendanceData->count() }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-500">Present Days</div>
                                    <div class="mt-1 text-2xl font-semibold text-green-600">
                                        {{ $attendanceData->where('status', 'present')->count() }}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-500">Late Days</div>
                                    <div class="mt-1 text-2xl font-semibold text-yellow-600">
                                        {{ $attendanceData->where('status', 'late')->count() }}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-medium text-gray-500">Absent Days</div>
                                    <div class="mt-1 text-2xl font-semibold text-red-600">
                                        {{ $attendanceData->where('status', 'absent')->count() }}
                                    </div>
                                </div>
                            </div>

                            <!-- Hours Breakdown -->
                            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-sm font-medium text-gray-500">Average Daily Hours</div>
                                    <div class="mt-1 text-xl font-semibold text-gray-900">
                                        {{ $attendanceData->count() > 0 ? number_format($attendanceData->avg('total_hours'), 2) : '0.00' }}
                                    </div>
                                </div>
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="text-sm font-medium text-blue-500">Total Late Minutes</div>
                                    <div class="mt-1 text-xl font-semibold text-blue-700">
                                        {{ $attendanceData->sum('late_minutes') }}
                                    </div>
                                </div>
                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <div class="text-sm font-medium text-orange-500">Total Overtime Minutes</div>
                                    <div class="mt-1 text-xl font-semibold text-orange-700">
                                        {{ $attendanceData->sum('overtime_minutes') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payroll Actions -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Payroll Actions
                        </h3>
                        <div class="space-y-3">
                            <button type="button"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <x-heroicon-o-check-circle class="mr-2 h-4 w-4" />
                                Calculate Payroll
                            </button>

                            <button type="button"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <x-heroicon-o-document-text class="mr-2 h-4 w-4" />
                                Export for Payroll
                            </button>

                            <a href="{{ route('attendance.export-payroll', ['period' => $period->format('Y-m'), 'employee_id' => request('employee_id')]) }}"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <x-heroicon-o-arrow-up-tray class="mr-2 h-4 w-4" />
                                Download CSV
                            </a>
                        </div>

                        <!-- Quick Stats -->
                        <div class="mt-6 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Working Days:</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $attendanceData->whereIn('status', ['present', 'late'])->count() }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Leave Days:</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $attendanceData->where('status', 'leave')->count() }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Absent Days:</span>
                                <span class="text-sm font-medium text-red-600">
                                    {{ $attendanceData->where('status', 'absent')->count() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Records -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Detailed Attendance Records
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">Attendance data for payroll calculation</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
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
                                    Late Minutes</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Overtime Minutes</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($attendanceData as $record)
                                @php
                                    $statusConfig = [
                                        'present' => ['class' => 'bg-green-100 text-green-800', 'label' => 'Present'],
                                        'late' => ['class' => 'bg-yellow-100 text-yellow-800', 'label' => 'Late'],
                                        'absent' => ['class' => 'bg-red-100 text-red-800', 'label' => 'Absent'],
                                        'leave' => ['class' => 'bg-blue-100 text-blue-800', 'label' => 'Leave'],
                                        'missed_punch' => [
                                            'class' => 'bg-orange-100 text-orange-800',
                                            'label' => 'Missed Punch',
                                        ],
                                    ];
                                    $config = $statusConfig[$record->status] ?? [
                                        'class' => 'bg-gray-100 text-gray-800',
                                        'label' => $record->status,
                                    ];
                                @endphp
                                <tr
                                    class="@if ($record->status === 'late') bg-yellow-50 @elseif($record->status === 'absent') bg-red-50 @elseif($record->status === 'leave') bg-blue-50 @endif">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record->record_date->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record->punch_in ? $record->punch_in->format('h:i A') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record->punch_out ? $record->punch_out->format('h:i A') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record->total_hours ? number_format($record->total_hours, 2) : '0.00' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record->late_minutes ?: '0' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record->overtime_minutes ?: '0' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['class'] }}">
                                            {{ $config['label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No attendance records found for the selected period.
                                    </td>
                                </tr>
                            @endforelse

                            <!-- Summary Row -->
                            @if ($attendanceData->count() > 0)
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                        colspan="3">
                                        Totals
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ number_format($totalHours, 2) }} hrs
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-yellow-600">
                                        {{ $attendanceData->sum('late_minutes') }} min
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-orange-600">
                                        {{ $attendanceData->sum('overtime_minutes') }} min
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500">
                                        {{ $attendanceData->count() }} records
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payroll Calculation Notes -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Payroll Calculation Notes</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Regular hours are calculated based on standard work schedule</li>
                                <li>Overtime is calculated for hours beyond scheduled end time</li>
                                <li>Late minutes are deducted according to company policy</li>
                                <li>Absent days may affect salary calculation (LOP - Loss of Pay)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

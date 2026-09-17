<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">
                    🏢 Employee Portal
                </h2>
                <p class="text-sm text-gray-600 mt-1 dark:text-gray-400">
                    {{ __('Overview of your performance and activities') }}
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('portal.employee.payslips') }}">
                    <x-button.primary>
                        <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                        {{ __('View Payslips') }}
                    </x-button.primary>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Header -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="md:flex md:items-center md:justify-between">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate dark:text-white">
                                {{ __('Welcome back,') }} {{ $employee->first_name }}!
                            </h2>
                            <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                                @if ($employee->position)
                                    <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                        <x-heroicon-o-briefcase class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
                                        {{ $employee->position->name }}
                                    </div>
                                @endif
                                @if ($employee->organizationUnit)
                                    <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                        <x-heroicon-o-building-office class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
                                        {{ $employee->organizationUnit->name }}
                                    </div>
                                @endif
                                <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-check-circle class="flex-shrink-0 mr-1.5 h-5 w-5 text-green-500" />
                                    {{ $employee->is_active ? 'Active' : 'Inactive' }} - {{ $employee->biometric_id ?: 'EMP-'.str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                            <!-- Quick Clock In/Out -->
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-lg">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Current Status') }}</div>
                                @if ($todayAttendance)
                                    <div class="flex items-center space-x-2">
                                        <div class="h-3 w-3 {{ $todayAttendance->punch_out ? 'bg-gray-400' : 'bg-green-500' }} rounded-full {{ $todayAttendance->punch_out ? '' : 'animate-pulse' }}"></div>
                                        <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $todayAttendance->punch_out ? 'Clocked Out' : 'Clocked In' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $todayAttendance->punch_in?->format('g:i A') }}{{ $todayAttendance->punch_out ? ' - '.$todayAttendance->punch_out->format('g:i A') : '' }}
                                    </div>
                                @else
                                    <div class="flex items-center space-x-2">
                                        <div class="h-3 w-3 bg-gray-400 rounded-full"></div>
                                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Not clocked in</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- Attendance Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-clock class="h-6 w-6 text-blue-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">{{ __('Today\'s Status') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $todayAttendance ? ucfirst($todayAttendance->status) : '—' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                        <div class="text-sm">
                            <a href="{{ route('portal.employee.attendance') }}"
                                class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                                {{ __('View details') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Leave Balance -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-play-circle class="h-6 w-6 text-green-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">{{ __('Leave Balance') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $leaveBalance }} {{ __('days') }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                        <div class="text-sm">
                            <a href="{{ route('portal.employee.leave') }}"
                                class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                                {{ __('Apply for leave') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Payslip -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-document-text class="h-6 w-6 text-yellow-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">{{ __('Last Payslip') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $recentPayslips->first()?->period ? \Illuminate\Support\Carbon::parse($recentPayslips->first()->period)->format('M Y') : '—' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                        <div class="text-sm">
                            <a href="{{ route('portal.employee.payslips') }}"
                                class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                                {{ __('View payslips') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Payslips Count -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-calendar class="h-6 w-6 text-purple-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">{{ __('Paid Payslips') }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $recentPayslips->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                        <div class="text-sm">
                            <span class="font-medium text-gray-500 dark:text-gray-400">{{ __('Available in portal') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2">
                    <!-- Recent Payslips -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 dark:text-white">
                                {{ __('Recent Payslips') }}
                            </h3>
                            @forelse ($recentPayslips as $payslip)
                                <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 dark:border-gray-700">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ \Illuminate\Support\Carbon::parse($payslip->period)->format('F Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('Paid') }} {{ $payslip->paid_at?->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                                            ${{ number_format($payslip->net_pay, 2) }}
                                        </p>
                                        <a href="{{ route('portal.employee.payslips') }}" class="text-xs text-blue-600 hover:underline dark:text-blue-400">
                                            {{ __('View') }}
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No paid payslips available yet.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 dark:text-white">
                                {{ __('Quick Actions') }}
                            </h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <a href="{{ route('portal.employee.clock-in') }}"
                                    class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    {{ __('Clock In') }}
                                </a>
                                <a href="{{ route('portal.employee.clock-out') }}"
                                    class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    {{ __('Clock Out') }}
                                </a>
                                <a href="{{ route('portal.employee.leave.create') }}"
                                    class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">
                                    {{ __('Request Leave') }}
                                </a>
                                <a href="{{ route('portal.employee.payslips') }}"
                                    class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">
                                    {{ __('View Payslips') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Attendance Summary -->
                <div class="space-y-6">
                    <!-- Today's Attendance -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 dark:text-white">
                                {{ __('Today\'s Attendance') }}
                            </h3>
                            @if ($todayAttendance)
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($todayAttendance->status) }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Clock In') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $todayAttendance->punch_in?->format('g:i A') ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Clock Out') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $todayAttendance->punch_out?->format('g:i A') ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Hours') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $todayAttendance->total_hours ? number_format($todayAttendance->total_hours, 2).' hrs' : '—' }}</dd>
                                    </div>
                                </dl>
                            @else
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-2 w-2 bg-gray-400 rounded-full mt-2"></div>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('You have not clocked in today. Use the Clock In button to start your shift.') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Attendance Link -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 dark:text-white">
                                {{ __('Attendance History') }}
                            </h3>
                            <a href="{{ route('portal.employee.attendance') }}"
                                class="inline-flex w-full items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">
                                {{ __('View Monthly Attendance') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
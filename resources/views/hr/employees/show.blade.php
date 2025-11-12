<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏠 {{ __('Employee Management > Employee Profile') }}
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Employee Profile: {{ $employee->first_name }} {{ $employee->last_name }}
                    </h2>
                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            @if ($employee->is_active)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active Employee
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inactive Employee
                                </span>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            Employee ID: EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}
                        </div>
                        @if ($employee->created_at)
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                Created: {{ $employee->created_at->format('F j, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <a href="{{ route('hr.employees.edit', $employee) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit Employee Details
                    </a>
                    @if ($employee->biometric_id)
                        {{-- <form action="{{ route('hr.employees.update-biometric', $employee) }}" method="POST"
                            class="inline"> --}}
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="biometric_id" value="">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            onclick="return confirm('Are you sure you want to reset the biometric ID?')">
                            Reset Biometric ID
                        </button>
                        {{-- </form> --}}
                    @endif
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Personal & Contact Information -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Personal & Contact Information
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">First Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $employee->first_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $employee->last_name }}</dd>
                                </div>
                                @if ($employee->middle_name)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Middle Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->middle_name }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $employee->email }}</dd>
                                </div>
                                @if ($employee->phone)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->phone }}</dd>
                                    </div>
                                @endif
                                @if ($employee->date_of_birth)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $employee->date_of_birth->format('F j, Y') }}</dd>
                                    </div>
                                @endif
                                @if ($employee->gender)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($employee->gender) }}</dd>
                                    </div>
                                @endif
                                @if ($employee->address || $employee->city || $employee->state)
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            @if ($employee->address)
                                                {{ $employee->address }}<br>
                                            @endif
                                            @if ($employee->city || $employee->state)
                                                {{ $employee->city }}{{ $employee->city && $employee->state ? ', ' : '' }}{{ $employee->state }}
                                            @endif
                                            @if ($employee->zip_code)
                                                {{ $employee->zip_code }}
                                            @endif
                                            @if ($employee->country)
                                                <br>{{ $employee->country }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Payroll Details -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Payroll Details
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                @if ($employee->salary_per_month)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Monthly Salary</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            ${{ number_format($employee->salary_per_month, 2) }}</dd>
                                    </div>
                                @endif
                                @if ($employee->pay_frequency)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Pay Frequency</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($employee->pay_frequency) }}
                                        </dd>
                                    </div>
                                @endif
                                @if ($employee->required_daily_hours)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Required Daily Hours</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->required_daily_hours }}
                                            hours</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- System Access -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                System Access
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            @if ($employee->user_id && $employee->user && $employee->user->currentOrganizationUser)
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Has System Access
                                        </span>
                                        <p class="mt-1 text-sm text-gray-500">
                                            User can login with roles:
                                            {{ implode(', ', $employee->user->currentOrganizationUser->roles ?? []) }}
                                        </p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Position: {{ $employee->user->currentOrganizationUser->position ?? 'N/A' }}
                                    </div>
                                </div>
                            @else
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No system access</h3>
                                    <p class="mt-1 text-sm text-gray-500">This employee cannot login to the system.</p>
                                    <div class="mt-6">
                                        {{-- <a href="{{ route('hr.employees.grant-access', $employee) }}"
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"> --}}
                                        Grant System Access
                                        {{-- </a> --}}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- HR & Organization -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                HR & Organization
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Employee ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">
                                        EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if ($employee->is_active)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                @if ($employee->organizationUnit)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Department</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->organizationUnit->name }}
                                        </dd>
                                    </div>
                                @endif
                                @if ($employee->organizationUser && $employee->organizationUser->position)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Position</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $employee->organizationUser->position }}</dd>
                                    </div>
                                @endif
                                @if ($employee->is_admin)
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Administrator</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                System Administrator
                                            </span>
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Attendance Setup -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Attendance Setup
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Critical settings linking clock-in device
                                to user</p>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Biometric ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">
                                        @if ($employee->biometric_id)
                                            {{ $employee->biometric_id }}
                                        @else
                                            <span class="text-gray-400">Not set</span>
                                        @endif
                                    </dd>
                                </div>
                                @if ($employee->required_daily_hours)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Daily Hours</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->required_daily_hours }}
                                            hours</dd>
                                    </div>
                                @endif
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Shift Schedule</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Standard Shift
                                            </span>
                                            <span class="text-gray-600">9:00 AM - 6:00 PM</span>
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Quick Stats
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">Attendance Records</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                        {{ $employee->attendanceRecords->count() }}</dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">Leave Requests</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                        {{ $employee->leaveRequests->count() }}</dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">Payroll Entries</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                        {{ $employee->payrollEntries->count() }}</dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">Years of Service</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                        {{ $employee->created_at ? $employee->created_at->diffInYears(now()) : 0 }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

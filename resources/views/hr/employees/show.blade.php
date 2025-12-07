<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    🏠 {{ __('Employee Management > Employee Profile') }}
                </h2>
                <p class="text-sm text-secondary mt-1">
                    View and manage employee information and records
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
                        Employee Profile: {{ $employee->first_name }} {{ $employee->last_name }}
                    </h2>
                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                        <div class="mt-2 flex items-center text-sm text-secondary">
                            @if ($employee->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active Employee
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inactive Employee
                                </span>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center text-sm text-secondary">
                            Employee ID: EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}
                        </div>
                        @if ($employee->created_at)
                            <div class="mt-2 flex items-center text-sm text-secondary">
                                Created: {{ $employee->created_at->format('F j, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <a href="{{ route('hr.employees.edit', $employee) }}"
                        class="inline-flex items-center px-4 py-2 border border-secondary rounded-md shadow-sm text-sm font-medium text-primary bg-surface hover:bg-tertiary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit Employee Details
                    </a>
                    @if ($employee->biometric_id)
                        <form action="{{ route('hr.employees.update-biometric', $employee) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="biometric_id" value="">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                onclick="return confirm('Are you sure you want to reset the biometric ID?')">
                                Reset Biometric ID
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Enhanced Personal & Contact Information -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 shadow-lg overflow-hidden sm:rounded-lg border border-blue-200 dark:border-blue-700">
                        <div class="px-4 py-5 sm:px-6 border-b border-blue-200 dark:border-blue-700 bg-gradient-to-r from-blue-600 to-indigo-600">
                            <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Personal & Contact Information
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        First Name
                                    </dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->first_name }}</dd>
                                </div>
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Last Name
                                    </dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->last_name }}</dd>
                                </div>
                                @if ($employee->middle_name)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-blue-600 dark:text-blue-400">Middle Name</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->middle_name }}</dd>
                                    </div>
                                @endif
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Email
                                    </dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->email }}</dd>
                                </div>
                                @if ($employee->phone)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            Phone
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->phone }}</dd>
                                    </div>
                                @endif
                                @if ($employee->date_of_birth)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            Date of Birth
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $employee->date_of_birth->format('F j, Y') }}
                                        </dd>
                                    </div>
                                @endif
                                @if ($employee->gender)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            Gender
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($employee->gender) }}</dd>
                                    </div>
                                @endif
                                @if ($employee->address || $employee->city || $employee->state)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm sm:col-span-2">
                                        <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            Address
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
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

                    <!-- Enhanced Payroll Details -->
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 shadow-lg overflow-hidden sm:rounded-lg border border-emerald-200 dark:border-emerald-700">
                        <div class="px-4 py-5 sm:px-6 border-b border-emerald-200 dark:border-emerald-700 bg-gradient-to-r from-emerald-600 to-teal-600">
                            <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Payroll Details
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                @if ($employee->salary_per_month || $employee->basic_salary)
                                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-emerald-100 dark:border-emerald-800">
                                        <dt class="text-sm font-medium text-emerald-600 dark:text-emerald-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Monthly Salary
                                        </dt>
                                        <dd class="mt-2 text-xl font-bold text-emerald-700 dark:text-emerald-300">
                                            ${{ number_format($employee->salary_per_month ?? $employee->basic_salary ?? 0, 2) }}
                                        </dd>
                                    </div>
                                @endif
                                
                                @if ($employee->required_daily_hours)
                                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-blue-100 dark:border-blue-800">
                                        <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Daily Hours Required
                                        </dt>
                                        <dd class="mt-2 text-xl font-bold text-blue-700 dark:text-blue-300">
                                            {{ $employee->required_daily_hours }} hours
                                        </dd>
                                    </div>
                                @endif

                                @if ($employee->payrollEntries->isNotEmpty())
                                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-purple-100 dark:border-purple-800">
                                        <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Last Payroll Entry
                                        </dt>
                                        <dd class="mt-2 text-sm font-semibold text-purple-700 dark:text-purple-300">
                                            {{ $employee->payrollEntries->first()?->period ?? 'N/A' }}
                                        </dd>
                                        <dd class="mt-1 text-xs text-purple-600 dark:text-purple-400">
                                            Net: ${{ number_format($employee->payrollEntries->first()?->net_pay ?? 0, 2) }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($employee->increments->isNotEmpty())
                                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-amber-100 dark:border-amber-800">
                                        <dt class="text-sm font-medium text-amber-600 dark:text-amber-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                            </svg>
                                            Latest Increment
                                        </dt>
                                        <dd class="mt-2 text-sm font-semibold text-amber-700 dark:text-amber-300">
                                            @if ($employee->increments->where('status', 'implemented')->isNotEmpty())
                                                {{ $employee->increments->where('status', 'implemented')->first()?->increment_percentage ?? 0 }}%
                                            @else
                                                Pending
                                            @endif
                                        </dd>
                                        <dd class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                            {{ $employee->increments->first()?->effective_date?->format('M j, Y') ?? 'N/A' }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($employee->loans->isNotEmpty())
                                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-red-100 dark:border-red-800">
                                        <dt class="text-sm font-medium text-red-600 dark:text-red-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Active Loans
                                        </dt>
                                        <dd class="mt-2 text-xl font-bold text-red-700 dark:text-red-300">
                                            {{ $employee->loans->count() }}
                                        </dd>
                                        <dd class="mt-1 text-xs text-red-600 dark:text-red-400">
                                            Total: ${{ number_format($employee->loans->sum('amount'), 2) }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($employee->salaryAdvances->isNotEmpty())
                                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-orange-100 dark:border-orange-800">
                                        <dt class="text-sm font-medium text-orange-600 dark:text-orange-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            Salary Advances
                                        </dt>
                                        <dd class="mt-2 text-xl font-bold text-orange-700 dark:text-orange-300">
                                            {{ $employee->salaryAdvances->count() }}
                                        </dd>
                                        <dd class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                                            Total: ${{ number_format($employee->salaryAdvances->sum('amount'), 2) }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Enhanced System Access -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 shadow-lg overflow-hidden sm:rounded-lg border border-green-200 dark:border-green-700">
                        <div class="px-4 py-5 sm:px-6 border-b border-green-200 dark:border-green-700 bg-gradient-to-r from-green-600 to-emerald-600">
                            <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                System Access
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            @if ($employee->user_id && $employee->user && $employee->user->currentOrganizationUser)
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-green-100 dark:border-green-800">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Active System Access
                                            </span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Login Email:</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->user->email }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Position:</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->user->currentOrganizationUser->position ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400 block mb-1">Roles:</span>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($employee->user->currentOrganizationUser->roles ?? [] as $role)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        {{ ucfirst($role) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-red-100 dark:border-red-800">
                                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No System Access</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This employee cannot login to the system.</p>
                                    <div class="mt-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Access Restricted
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Enhanced HR & Organization -->
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 shadow-lg overflow-hidden sm:rounded-lg border border-purple-200 dark:border-purple-700">
                        <div class="px-4 py-5 sm:px-6 border-b border-purple-200 dark:border-purple-700 bg-gradient-to-r from-purple-600 to-pink-600">
                            <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                HR & Organization
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                        </svg>
                                        Employee ID
                                    </dt>
                                    <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100 font-mono bg-purple-50 dark:bg-purple-900/30 px-2 py-1 rounded">
                                        EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}
                                    </dd>
                                </div>
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Status
                                    </dt>
                                    <dd class="mt-1">
                                        @if ($employee->is_active)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                Inactive
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                @if ($employee->organizationUnit)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            Department
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->organizationUnit->name }}</dd>
                                    </div>
                                @endif
                                @if ($employee->organizationUser && $employee->organizationUser->position)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            Position
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->organizationUser->position }}</dd>
                                    </div>
                                @endif
                                @if ($employee->is_admin)
                                    <div class="bg-gradient-to-r from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 p-3 rounded-lg shadow-sm border border-purple-200 dark:border-purple-700 sm:col-span-2">
                                        <dt class="text-sm font-medium text-purple-700 dark:text-purple-300 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                            </svg>
                                            Administrator Access
                                        </dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                                System Administrator
                                            </span>
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Enhanced Attendance Setup -->
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 shadow-lg overflow-hidden sm:rounded-lg border border-orange-200 dark:border-orange-700">
                        <div class="px-4 py-5 sm:px-6 border-b border-orange-200 dark:border-orange-700 bg-gradient-to-r from-orange-600 to-red-600">
                            <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Attendance Setup
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-orange-100">Critical settings linking clock-in device to user</p>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-orange-600 dark:text-orange-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        Biometric ID
                                    </dt>
                                    <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100 font-mono bg-orange-50 dark:bg-orange-900/30 px-2 py-1 rounded">
                                        @if ($employee->biometric_id)
                                            {{ $employee->biometric_id }}
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">Not set</span>
                                        @endif
                                    </dd>
                                </div>
                                @if ($employee->required_daily_hours)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-orange-600 dark:text-orange-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Daily Hours Required
                                        </dt>
                                        <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100">{{ $employee->required_daily_hours }} hours</dd>
                                    </div>
                                @endif
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm sm:col-span-2">
                                    <dt class="text-sm font-medium text-orange-600 dark:text-orange-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Shift Schedule
                                    </dt>
                                    <dd class="mt-1">
                                        <div class="flex items-center space-x-3">
                                            @if($employee->shift)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-sm">
                                                    {{ $employee->shift->name }}
                                                </span>
                                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $employee->shift->start_time }} - {{ $employee->shift->end_time }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                    Standard Shift
                                                </span>
                                                <span class="text-sm text-gray-600 dark:text-gray-400">9:00 AM - 6:00 PM</span>
                                            @endif
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Enhanced Quick Stats -->
                    <div class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 dark:from-indigo-900/20 dark:via-purple-900/20 dark:to-pink-900/20 shadow-lg overflow-hidden sm:rounded-lg border border-indigo-200 dark:border-indigo-700">
                        <div class="px-4 py-5 sm:px-6 border-b border-indigo-200 dark:border-indigo-700 bg-gradient-to-r from-indigo-600 to-purple-600">
                            <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Quick Stats
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-2 gap-4 sm:grid-cols-2">
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-blue-200 dark:border-blue-800 text-center hover:shadow-md transition-shadow">
                                    <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Attendance Records
                                    </dt>
                                    <dd class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300">
                                        {{ $employee->attendanceRecords->count() }}
                                    </dd>
                                </div>
                                
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-green-200 dark:border-green-800 text-center hover:shadow-md transition-shadow">
                                    <dt class="text-sm font-medium text-green-600 dark:text-green-400 flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Leave Requests
                                    </dt>
                                    <dd class="mt-2 text-2xl font-bold text-green-700 dark:text-green-300">
                                        {{ $employee->leaveRequests->count() }}
                                    </dd>
                                </div>
                                
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-purple-200 dark:border-purple-800 text-center hover:shadow-md transition-shadow">
                                    <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Payroll Entries
                                    </dt>
                                    <dd class="mt-2 text-2xl font-bold text-purple-700 dark:text-purple-300">
                                        {{ $employee->payrollEntries->count() }}
                                    </dd>
                                </div>
                                
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-amber-200 dark:border-amber-800 text-center hover:shadow-md transition-shadow">
                                    <dt class="text-sm font-medium text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Years of Service
                                    </dt>
                                    <dd class="mt-2 text-2xl font-bold text-amber-700 dark:text-amber-300">
                                        @php
                                            $yearsOfService = $employee->created_at ? $employee->created_at->diffInYears(now()) : 0;
                                            $monthsOfService = $employee->created_at ? $employee->created_at->diffInMonths(now()) % 12 : 0;
                                        @endphp
                                        @if($yearsOfService > 0)
                                            {{ $yearsOfService }} {{ $yearsOfService == 1 ? 'year' : 'years' }}
                                            @if($monthsOfService > 0)
                                                {{ $monthsOfService }} {{ $monthsOfService == 1 ? 'month' : 'months' }}
                                            @endif
                                        @elseif($monthsOfService > 0)
                                            {{ $monthsOfService }} {{ $monthsOfService == 1 ? 'month' : 'months' }}
                                        @else
                                            < 1 month
                                        @endif
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

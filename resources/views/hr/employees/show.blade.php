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
                                <x-heroicon-o-user class="w-5 h-5 mr-2" />
                                Personal & Contact Information
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                        <x-heroicon-o-user class="w-4 h-4 mr-1" />
                                        First Name
                                    </dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->first_name }}</dd>
                                </div>
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                        <x-heroicon-o-user class="w-4 h-4 mr-1" />
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
                                        <x-heroicon-o-envelope class="w-4 h-4 mr-1" />
                                        Email
                                    </dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->email }}</dd>
                                </div>
                                @if ($employee->phone)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                            <x-heroicon-o-phone class="w-4 h-4 mr-1" />
                                            Phone
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->phone }}</dd>
                                    </div>
                                @endif
                                @if ($employee->date_of_birth)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                            <x-heroicon-o-calendar class="w-4 h-4 mr-1" />
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
                                            <x-heroicon-o-user class="w-4 h-4 mr-1" />
                                            Gender
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($employee->gender) }}</dd>
                                    </div>
                                @endif
                                @if ($employee->address || $employee->city || $employee->state)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm sm:col-span-2">
                                        <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center">
                                            <x-heroicon-o-map-pin class="w-4 h-4 mr-1" />
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
                                <x-heroicon-o-face-smile class="w-5 h-5 mr-2" />
                                Payroll Details
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                @if ($employee->salary_per_month || $employee->basic_salary)
                                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-emerald-100 dark:border-emerald-800">
                                        <dt class="text-sm font-medium text-emerald-600 dark:text-emerald-400 flex items-center">
                                            <x-heroicon-o-face-smile class="w-4 h-4 mr-1" />
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
                                            <x-heroicon-o-clock class="w-4 h-4 mr-1" />
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
                                            <x-heroicon-o-document-text class="w-4 h-4 mr-1" />
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
                                            <x-heroicon-o-arrow-down class="w-4 h-4 mr-1" />
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
                                            <x-heroicon-o-clock class="w-4 h-4 mr-1" />
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
                                            <x-heroicon-o-square-2-stack class="w-4 h-4 mr-1" />
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
                                <x-heroicon-o-lock-closed class="w-5 h-5 mr-2" />
                                System Access
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            @if ($employee->user_id && $employee->user && $employee->user->currentOrganizationUser)
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-green-100 dark:border-green-800">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center">
                                            <x-heroicon-o-check-circle class="w-6 h-6 text-green-500 mr-2" />
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
                                    <x-heroicon-o-lock-closed class="mx-auto h-12 w-12 text-red-400" />
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
                                <x-heroicon-o-document-text class="w-5 h-5 mr-2" />
                                HR & Organization
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center">
                                        <x-heroicon-o-code-bracket class="w-4 h-4 mr-1" />
                                        Employee ID
                                    </dt>
                                    <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100 font-mono bg-purple-50 dark:bg-purple-900/30 px-2 py-1 rounded">
                                        EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}
                                    </dd>
                                </div>
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center">
                                        <x-heroicon-o-check-circle class="w-4 h-4 mr-1" />
                                        Status
                                    </dt>
                                    <dd class="mt-1">
                                        @if ($employee->is_active)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <x-heroicon-m-check-circle class="w-3 h-3 mr-1" />
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                <x-heroicon-m-x-circle class="w-3 h-3 mr-1" />
                                                Inactive
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                @if ($employee->organizationUnit)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center">
                                            <x-heroicon-o-document-text class="w-4 h-4 mr-1" />
                                            Department
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->organizationUnit->name }}</dd>
                                    </div>
                                @endif
                                @if ($employee->organizationUser && $employee->organizationUser->position)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                        <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center">
                                            <x-heroicon-o-paper-airplane class="w-4 h-4 mr-1" />
                                            Position
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->organizationUser->position }}</dd>
                                    </div>
                                @endif
                                @if ($employee->is_admin)
                                    <div class="bg-gradient-to-r from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 p-3 rounded-lg shadow-sm border border-purple-200 dark:border-purple-700 sm:col-span-2">
                                        <dt class="text-sm font-medium text-purple-700 dark:text-purple-300 flex items-center">
                                            <x-heroicon-o-shield-check class="w-4 h-4 mr-1" />
                                            Administrator Access
                                        </dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg">
                                                <x-heroicon-o-star class="w-3 h-3 mr-1" />
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
                                <x-heroicon-o-clock class="w-5 h-5 mr-2" />
                                Attendance Setup
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-orange-100">Critical settings linking clock-in device to user</p>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm">
                                    <dt class="text-sm font-medium text-orange-600 dark:text-orange-400 flex items-center">
                                        <x-heroicon-o-bolt class="w-4 h-4 mr-1" />
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
                                            <x-heroicon-o-clock class="w-4 h-4 mr-1" />
                                            Daily Hours Required
                                        </dt>
                                        <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100">{{ $employee->required_daily_hours }} hours</dd>
                                    </div>
                                @endif
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm sm:col-span-2">
                                    <dt class="text-sm font-medium text-orange-600 dark:text-orange-400 flex items-center">
                                        <x-heroicon-o-calendar class="w-4 h-4 mr-1" />
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
                                <x-heroicon-o-document-duplicate class="w-5 h-5 mr-2" />
                                Quick Stats
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-2 gap-4 sm:grid-cols-2">
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-blue-200 dark:border-blue-800 text-center hover:shadow-md transition-shadow">
                                    <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                        <x-heroicon-o-check-circle class="w-4 h-4 mr-1" />
                                        Attendance Records
                                    </dt>
                                    <dd class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300">
                                        {{ $employee->attendanceRecords->count() }}
                                    </dd>
                                </div>
                                
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-green-200 dark:border-green-800 text-center hover:shadow-md transition-shadow">
                                    <dt class="text-sm font-medium text-green-600 dark:text-green-400 flex items-center justify-center">
                                        <x-heroicon-o-calendar class="w-4 h-4 mr-1" />
                                        Leave Requests
                                    </dt>
                                    <dd class="mt-2 text-2xl font-bold text-green-700 dark:text-green-300">
                                        {{ $employee->leaveRequests->count() }}
                                    </dd>
                                </div>
                                
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-purple-200 dark:border-purple-800 text-center hover:shadow-md transition-shadow">
                                    <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                        <x-heroicon-o-square-2-stack class="w-4 h-4 mr-1" />
                                        Payroll Entries
                                    </dt>
                                    <dd class="mt-2 text-2xl font-bold text-purple-700 dark:text-purple-300">
                                        {{ $employee->payrollEntries->count() }}
                                    </dd>
                                </div>
                                
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-amber-200 dark:border-amber-800 text-center hover:shadow-md transition-shadow">
                                    <dt class="text-sm font-medium text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                        <x-heroicon-o-clock class="w-4 h-4 mr-1" />
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

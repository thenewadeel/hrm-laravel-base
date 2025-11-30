<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            💰 {{ __('Payroll Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Payroll Dashboard
                    </h2>
                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            Period: {{ $period }}
                        </div>
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            Organization: {{ auth()->user()->currentOrganization->name }}
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <form action="{{ route('payroll.processing') }}" method="GET" class="inline">
                        <input type="hidden" name="period" value="{{ $period }}">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Process Payroll
                        </button>
                    </form>
                    <div class="relative">
                        <select onchange="window.location.href='?period=' + this.value" 
                                class="appearance-none h-full rounded-r border-t border-r border-b border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 px-4 py-2">
                            @for($i = 0; $i < 12; $i++)
                                @php
                                    $periodOption = now()->subMonths($i)->format('Y-m');
                                    $displayOption = now()->subMonths($i)->format('F Y');
                                @endphp
                                <option value="{{ $periodOption }}" {{ $period === $periodOption ? 'selected' : '' }}>
                                    {{ $displayOption }}
                                </option>
                            @endfor
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                <!-- Total Employees -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Employees
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $summary['total_employees'] ?? 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Net Pay -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Net Pay
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        ${{ number_format($summary['total_net_pay'] ?? 0, 2) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Pending Approvals
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $pendingIncrements + $pendingLoans + $pendingAdvances }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Loans -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Active Loans
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $summary['employee_breakdown']->count() ?? 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payroll Summary -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Payroll Summary - {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}
                    </h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div class="text-center">
                            <p class="text-sm font-medium text-gray-500 mb-2">Basic Salary</p>
                            <p class="text-3xl font-bold text-gray-900">${{ number_format($summary['total_basic_salary'] ?? 0, 2) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-medium text-gray-500 mb-2">Gross Pay</p>
                            <p class="text-3xl font-bold text-blue-600">${{ number_format($summary['total_gross_pay'] ?? 0, 2) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-medium text-gray-500 mb-2">Net Pay</p>
                            <p class="text-3xl font-bold text-green-600">${{ number_format($summary['total_net_pay'] ?? 0, 2) }}</p>
                        </div>
                    </div>

                    <!-- Detailed Breakdown -->
                    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <h4 class="text-sm font-medium text-gray-600 mb-3">Earnings Breakdown</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Basic Salary</span>
                                    <span class="text-sm font-medium text-gray-900">${{ number_format($summary['total_basic_salary'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Allowances</span>
                                    <span class="text-sm font-medium text-gray-900">${{ number_format($summary['total_allowances'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold text-gray-900 pt-2 border-t border-gray-200">
                                    <span>Gross Pay</span>
                                    <span>${{ number_format($summary['total_gross_pay'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-600 mb-3">Deductions Breakdown</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Tax</span>
                                    <span class="text-sm font-medium text-gray-900">${{ number_format($summary['total_tax'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Other Deductions</span>
                                    <span class="text-sm font-medium text-gray-900">${{ number_format(($summary['total_deductions'] ?? 0) - ($summary['total_tax'] ?? 0), 2) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold text-gray-900 pt-2 border-t border-gray-200">
                                    <span>Total Deductions</span>
                                    <span>${{ number_format($summary['total_deductions'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Items Alert -->
            @if ($pendingIncrements > 0 || $pendingLoans > 0 || $pendingAdvances > 0)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                You have <span class="font-medium">{{ $pendingIncrements }} increment(s)</span>, 
                                <span class="font-medium">{{ $pendingLoans }} loan(s)</span>, and 
                                <span class="font-medium">{{ $pendingAdvances }} advance(s)</span> pending approval.
                            </p>
                            <div class="mt-3">
                                <div class="flex space-x-3">
                                    @if($pendingIncrements > 0)
                                        <a href="{{ route('payroll.increments') }}" class="text-sm font-medium text-yellow-700 underline hover:text-yellow-600">
                                            Review Increments
                                        </a>
                                    @endif
                                    @if($pendingLoans > 0)
                                        <a href="{{ route('payroll.loans') }}" class="text-sm font-medium text-yellow-700 underline hover:text-yellow-600">
                                            Review Loans
                                        </a>
                                    @endif
                                    @if($pendingAdvances > 0)
                                        <a href="{{ route('payroll.advances') }}" class="text-sm font-medium text-yellow-700 underline hover:text-yellow-600">
                                            Review Advances
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('payroll.processing') }}" 
                    class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-900">Process Payroll</h3>
                                <p class="text-sm text-gray-500">Run monthly payroll</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('payroll.increments') }}" 
                    class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-900">Increments</h3>
                                <p class="text-sm text-gray-500">Manage salary increments</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('payroll.loans') }}" 
                    class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-900">Loans</h3>
                                <p class="text-sm text-gray-500">Manage employee loans</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('payroll.advances') }}" 
                    class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-900">Advances</h3>
                                <p class="text-sm text-gray-500">Manage salary advances</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
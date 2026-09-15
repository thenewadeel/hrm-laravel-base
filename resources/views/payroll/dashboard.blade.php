<x-app-layout>
    <x-slot name="header">
        <x-page-header title="💰 {{ __('Payroll Dashboard') }}" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-page-header
                title="{{ __('Payroll Dashboard') }}"
                :description="'Period: '.$period.' • '.auth()->user()->currentOrganization->name"
            >
                <x-slot name="actions">
                    <form action="{{ route('payroll.processing') }}" method="GET">
                        <input type="hidden" name="period" value="{{ $period }}">
                        <x-button variant="success" type="submit">
                            <svg class="mr-2 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            {{ __('Process Payroll') }}
                        </x-button>
                    </form>

                    <div class="relative">
                        <select onchange="window.location.href='?period=' + this.value"
                            class="h-full appearance-none rounded-md border border-secondary surface px-4 py-2 pr-10 text-sm font-medium text-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            @for ($i = 0; $i < 12; $i++)
                                @php
                                    $periodOption = now()->subMonths($i)->format('Y-m');
                                    $displayOption = now()->subMonths($i)->format('F Y');
                                @endphp
                                <option value="{{ $periodOption }}" {{ $period === $periodOption ? 'selected' : '' }}>
                                    {{ $displayOption }}
                                </option>
                            @endfor
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-secondary">
                            <svg class="size-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                            </svg>
                        </div>
                    </div>
                </x-slot>
            </x-page-header>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat-card label="{{ __('Total Employees') }}"
                    :value="($summary['total_employees'] ?? 0)" tone="info">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Total Net Pay') }}"
                    :value="'$'.number_format($summary['total_net_pay'] ?? 0, 2)" tone="success">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Pending Approvals') }}"
                    :value="$pendingIncrements + $pendingLoans + $pendingAdvances" tone="warning">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Active Loans') }}"
                    :value="count($summary['employee_breakdown'] ?? [])" tone="primary">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>
            </div>

            <x-card
                :title="'Payroll Summary - '.\Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y')">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div class="text-center">
                        <p class="mb-2 text-sm font-medium text-muted">{{ __('Basic Salary') }}</p>
                        <p class="text-3xl font-bold text-primary">${{ number_format($summary['total_basic_salary'] ?? 0, 2) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="mb-2 text-sm font-medium text-muted">{{ __('Gross Pay') }}</p>
                        <p class="text-3xl font-bold text-accent">${{ number_format($summary['total_gross_pay'] ?? 0, 2) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="mb-2 text-sm font-medium text-muted">{{ __('Net Pay') }}</p>
                        <p class="text-3xl font-bold text-success">${{ number_format($summary['total_net_pay'] ?? 0, 2) }}</p>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <h4 class="mb-3 text-sm font-medium text-secondary">{{ __('Earnings Breakdown') }}</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">{{ __('Basic Salary') }}</span>
                                <span class="text-sm font-medium text-primary">${{ number_format($summary['total_basic_salary'] ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">{{ __('Allowances') }}</span>
                                <span class="text-sm font-medium text-primary">${{ number_format($summary['total_allowances'] ?? 0, 2) }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between pt-2 text-lg font-semibold text-primary">
                                <span>{{ __('Gross Pay') }}</span>
                                <span>${{ number_format($summary['total_gross_pay'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="mb-3 text-sm font-medium text-secondary">{{ __('Deductions Breakdown') }}</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">{{ __('Tax') }}</span>
                                <span class="text-sm font-medium text-primary">${{ number_format($summary['total_tax'] ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary">{{ __('Other Deductions') }}</span>
                                <span class="text-sm font-medium text-primary">${{ number_format(($summary['total_deductions'] ?? 0) - ($summary['total_tax'] ?? 0), 2) }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between pt-2 text-lg font-semibold text-primary">
                                <span>{{ __('Total Deductions') }}</span>
                                <span>${{ number_format($summary['total_deductions'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            @if ($pendingIncrements > 0 || $pendingLoans > 0 || $pendingAdvances > 0)
                <x-alert type="warning">
                    <p>
                        {{ __('You have') }}
                        <span class="font-medium">{{ $pendingIncrements }} {{ __('increment(s)') }}</span>,
                        <span class="font-medium">{{ $pendingLoans }} {{ __('loan(s)') }}</span>,
                        {{ __('and') }}
                        <span class="font-medium">{{ $pendingAdvances }} {{ __('advance(s)') }}</span>
                        {{ __('pending approval.') }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-3">
                        @if ($pendingIncrements > 0)
                            <a href="{{ route('payroll.increments') }}"
                                class="text-sm font-medium text-warning underline decoration-warning/40 underline-offset-2 hover:text-warning/80">
                                {{ __('Review Increments') }}
                            </a>
                        @endif
                        @if ($pendingLoans > 0)
                            <a href="{{ route('payroll.loans') }}"
                                class="text-sm font-medium text-warning underline decoration-warning/40 underline-offset-2 hover:text-warning/80">
                                {{ __('Review Loans') }}
                            </a>
                        @endif
                        @if ($pendingAdvances > 0)
                            <a href="{{ route('payroll.advances') }}"
                                class="text-sm font-medium text-warning underline decoration-warning/40 underline-offset-2 hover:text-warning/80">
                                {{ __('Review Advances') }}
                            </a>
                        @endif
                    </div>
                </x-alert>
            @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('payroll.processing') }}"
                    class="surface rounded-lg p-5 transition-shadow duration-200 hover:shadow-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-lg bg-success/10 p-3 text-success">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-primary">{{ __('Process Payroll') }}</h3>
                            <p class="text-sm text-muted">{{ __('Run monthly payroll') }}</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('payroll.increments') }}"
                    class="surface rounded-lg p-5 transition-shadow duration-200 hover:shadow-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-lg bg-info/10 p-3 text-info">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-primary">{{ __('Increments') }}</h3>
                            <p class="text-sm text-muted">{{ __('Manage salary increments') }}</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('payroll.loans') }}"
                    class="surface rounded-lg p-5 transition-shadow duration-200 hover:shadow-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-lg bg-primary/10 p-3 text-primary">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-primary">{{ __('Loans') }}</h3>
                            <p class="text-sm text-muted">{{ __('Manage employee loans') }}</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('payroll.advances') }}"
                    class="surface rounded-lg p-5 transition-shadow duration-200 hover:shadow-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-lg bg-warning/10 p-3 text-warning">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-primary">{{ __('Advances') }}</h3>
                            <p class="text-sm text-muted">{{ __('Manage salary advances') }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
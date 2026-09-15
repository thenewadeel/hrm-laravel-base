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
                            <x-heroicon-o-clipboard-document class="mr-2 size-4" />
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
                            <x-heroicon-m-check class="size-4 fill-current" />
                        </div>
                    </div>
                </x-slot>
            </x-page-header>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat-card label="{{ __('Total Employees') }}"
                    :value="($summary['total_employees'] ?? 0)" tone="info">
                    <x-slot name="icon">
                        <x-heroicon-o-user-group class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Total Net Pay') }}"
                    :value="'$'.number_format($summary['total_net_pay'] ?? 0, 2)" tone="success">
                    <x-slot name="icon">
                        <x-heroicon-o-face-smile class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Pending Approvals') }}"
                    :value="$pendingIncrements + $pendingLoans + $pendingAdvances" tone="warning">
                    <x-slot name="icon">
                        <x-heroicon-o-clock class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Active Loans') }}"
                    :value="count($summary['employee_breakdown'] ?? [])" tone="primary">
                    <x-slot name="icon">
                        <x-heroicon-o-square-2-stack class="size-6" />
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
                            <x-heroicon-o-clipboard-document class="size-6" />
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
                            <x-heroicon-o-arrow-down class="size-6" />
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
                            <x-heroicon-o-square-2-stack class="size-6" />
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
                            <x-heroicon-o-face-smile class="size-6" />
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
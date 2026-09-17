<x-app-layout>
    <x-slot name="header">
        <x-page-header title="💰 {{ __('Employee Payroll') }}" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-page-header
                :title="'Payroll Details - '.($employee->first_name.' '.$employee->last_name)"
                :description="'Period: '.Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y').' • '.auth()->user()->currentOrganization->name"
            >
                <x-slot name="actions">
                    <form action="{{ route('payroll.processing') }}" method="GET">
                        <input type="hidden" name="period" value="{{ $period }}">
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        <x-button variant="info" type="submit">
                            <x-heroicon-o-clipboard-document class="mr-2 size-4" />
                            {{ __('Process Payroll') }}
                        </x-button>
                    </form>
                </x-slot>
            </x-page-header>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat-card label="{{ __('Basic Salary') }}"
                    :value="'$'.number_format($payrollData['basic_salary'] ?? 0, 2)" tone="info">
                    <x-slot name="icon">
                        <x-heroicon-o-banknotes class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Gross Pay') }}"
                    :value="'$'.number_format($payrollData['gross_pay'] ?? 0, 2)" tone="primary">
                    <x-slot name="icon">
                        <x-heroicon-o-arrow-trending-up class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Total Deductions') }}"
                    :value="'$'.number_format($payrollData['total_deductions'] ?? 0, 2)" tone="warning">
                    <x-slot name="icon">
                        <x-heroicon-o-arrow-trending-down class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Net Pay') }}"
                    :value="'$'.number_format($payrollData['net_pay'] ?? 0, 2)" tone="success">
                    <x-slot name="icon">
                        <x-heroicon-o-face-smile class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <x-card :title="__('Earnings')" class="lg:col-span-2">
                    <div class="space-y-3">
                        <div class="flex justify-between border-b border-secondary pb-2">
                            <span class="text-sm text-secondary">{{ __('Basic Salary') }}</span>
                            <span class="text-sm font-medium text-primary">${{ number_format($payrollData['basic_salary'] ?? 0, 2) }}</span>
                        </div>

                        @forelse(($payrollData['allowances']['breakdown'] ?? []) as $allowance)
                            <div class="flex justify-between border-b border-secondary pb-2">
                                <span class="text-sm text-secondary">{{ $allowance['name'] }}
                                    @if (! $allowance['is_taxable'])
                                        <span class="ml-1 rounded bg-green-100 px-1.5 py-0.5 text-xs text-green-700 dark:bg-green-900 dark:text-green-300">{{ __('Non-taxable') }}</span>
                                    @endif
                                </span>
                                <span class="text-sm font-medium text-primary">${{ number_format($allowance['amount'], 2) }}</span>
                            </div>
                        @empty
                            <div class="flex justify-between border-b border-secondary pb-2">
                                <span class="text-sm text-secondary">{{ __('Allowances') }}</span>
                                <span class="text-sm font-medium text-primary">${{ number_format($payrollData['allowances']['total'] ?? 0, 2) }}</span>
                            </div>
                        @endforelse

                        <div class="flex items-center justify-between pt-2 text-lg font-semibold text-primary">
                            <span>{{ __('Gross Pay') }}</span>
                            <span>${{ number_format($payrollData['gross_pay'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </x-card>

                <x-card :title="__('Deductions')">
                    <div class="space-y-3">
                        <div class="flex justify-between border-b border-secondary pb-2">
                            <span class="text-sm text-secondary">{{ __('Income Tax') }}</span>
                            <span class="text-sm font-medium text-primary">${{ number_format($payrollData['tax'] ?? 0, 2) }}</span>
                        </div>

                        @forelse(($payrollData['deductions']['breakdown'] ?? []) as $deduction)
                            <div class="flex justify-between border-b border-secondary pb-2">
                                <span class="text-sm text-secondary">{{ $deduction['name'] }}</span>
                                <span class="text-sm font-medium text-primary">${{ number_format($deduction['amount'], 2) }}</span>
                            </div>
                        @empty
                            <div class="flex justify-between border-b border-secondary pb-2">
                                <span class="text-sm text-secondary">{{ __('Other Deductions') }}</span>
                                <span class="text-sm font-medium text-primary">${{ number_format(($payrollData['deductions']['total'] ?? 0), 2) }}</span>
                            </div>
                        @endforelse

                        <div class="flex justify-between border-b border-secondary pb-2">
                            <span class="text-sm text-secondary">{{ __('Loan Deductions') }}</span>
                            <span class="text-sm font-medium text-primary">${{ number_format($payrollData['loan_deductions'] ?? 0, 2) }}</span>
                        </div>

                        <div class="flex justify-between border-b border-secondary pb-2">
                            <span class="text-sm text-secondary">{{ __('Salary Advance Deductions') }}</span>
                            <span class="text-sm font-medium text-primary">${{ number_format($payrollData['advance_deductions'] ?? 0, 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between pt-2 text-lg font-semibold text-primary">
                            <span>{{ __('Total Deductions') }}</span>
                            <span>${{ number_format($payrollData['total_deductions'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </x-card>
            </div>

            <x-card :title="__('Pay Summary')" tone="success">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="mb-1 text-sm font-medium text-muted">{{ __('Taxable Income') }}</p>
                        <p class="text-2xl font-bold text-primary">${{ number_format($payrollData['taxable_income'] ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="mb-1 text-sm font-medium text-muted">{{ __('Net Pay') }}</p>
                        <p class="text-2xl font-bold text-success">${{ number_format($payrollData['net_pay'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
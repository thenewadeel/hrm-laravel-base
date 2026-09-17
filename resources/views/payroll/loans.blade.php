<x-app-layout>
    <x-slot name="header">
        <x-page-header title="💰 {{ __('Employee Loans') }}" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-page-header
                title="Loans Management"
                :description="'Manage employee loan applications and disbursements • '.auth()->user()->currentOrganization->name"
            >
                <x-slot name="actions">
                    <a href="{{ route('payroll.dashboard') }}">
                        <x-button variant="secondary">
                            <x-heroicon-o-arrow-left class="mr-2 size-4" />
                            {{ __('Back to Dashboard') }}
                        </x-button>
                    </a>
                </x-slot>
            </x-page-header>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
                <x-dashboard.stat-card label="{{ __('Total Loans') }}"
                    :value="$loans->total()" tone="info">
                    <x-slot name="icon">
                        <x-heroicon-o-square-2-stack class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Pending') }}"
                    :value="$loans->where('status', 'pending')->count()" tone="warning">
                    <x-slot name="icon">
                        <x-heroicon-o-clock class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Approved') }}"
                    :value="$loans->where('status', 'approved')->count()" tone="primary">
                    <x-slot name="icon">
                        <x-heroicon-o-check-circle class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Active Loans') }}"
                    :value="$loans->where('status', 'active')->count()" tone="success">
                    <x-slot name="icon">
                        <x-heroicon-o-face-smile class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>
            </div>

            <x-card :title="__('Employee Loans')">
                <x-slot name="actions">
                    <a href="{{ route('payroll.loans') }}">
                        <x-button variant="success" type="button">
                            <x-heroicon-o-plus class="mr-2 size-4" />
                            {{ __('New Loan') }}
                        </x-button>
                    </a>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary">
                        <thead class="bg-tertiary">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Reference') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Employee') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Principal') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Balance') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Monthly Installment') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Progress') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary">
                            @forelse($loans as $loan)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                        {{ $loan->loan_reference }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        {{ $loan->employee?->first_name }} {{ $loan->employee?->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">
                                        {{ $loan->loan_type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        ${{ number_format($loan->principal_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        ${{ number_format($loan->balance_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        ${{ number_format($loan->monthly_installment, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        <div class="flex items-center">
                                            <div class="mr-2 h-2 w-16 rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div class="h-2 rounded-full bg-blue-600"
                                                    style="width: {{ $loan->repayment_period_months > 0 ? ($loan->installments_paid / $loan->repayment_period_months) * 100 : 0 }}%"></div>
                                            </div>
                                            <span class="text-xs">{{ $loan->installments_paid }}/{{ $loan->repayment_period_months }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @switch($loan->status)
                                            @case('pending')
                                                <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">{{ __('Pending') }}</span>
                                                @break
                                            @case('approved')
                                                <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">{{ __('Approved') }}</span>
                                                @break
                                            @case('active')
                                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-300">{{ __('Active') }}</span>
                                                @break
                                            @case('completed')
                                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ __('Completed') }}</span>
                                                @break
                                            @case('rejected')
                                                <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800 dark:bg-red-900/30 dark:text-red-300">{{ __('Rejected') }}</span>
                                                @break
                                            @default
                                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ ucfirst($loan->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if ($loan->status === 'pending')
                                            <form action="{{ route('payroll.loans.approve', $loan) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                                    {{ __('Approve') }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($loan->status === 'approved')
                                            <form action="{{ route('payroll.loans.disburse', $loan) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400">
                                                    {{ __('Disburse') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-muted">
                                        {{ __('No employee loans found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($loans->hasPages())
                    <div class="mt-4">
                        {{ $loans->links() }}
                    </div>
                @endif
            </x-card>

            <x-card :title="__('Create Loan Application')">
                <form action="{{ route('payroll.loans.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-secondary">{{ __('Employee') }}</label>
                            <select name="employee_id" id="employee_id" required class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                                <option value="">{{ __('Select employee...') }}</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="loan_type" class="block text-sm font-medium text-secondary">{{ __('Loan Type') }}</label>
                            <input type="text" name="loan_type" id="loan_type" required placeholder="e.g. Home Loan, Vehicle Loan"
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="principal_amount" class="block text-sm font-medium text-secondary">{{ __('Principal Amount ($)') }}</label>
                            <input type="number" name="principal_amount" id="principal_amount" step="0.01" min="0" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="interest_rate" class="block text-sm font-medium text-secondary">{{ __('Annual Interest Rate (%)') }}</label>
                            <input type="number" name="interest_rate" id="interest_rate" step="0.01" min="0" max="100" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="repayment_period_months" class="block text-sm font-medium text-secondary">{{ __('Repayment Period (months)') }}</label>
                            <input type="number" name="repayment_period_months" id="repayment_period_months" min="1" max="360" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="disbursement_date" class="block text-sm font-medium text-secondary">{{ __('Disbursement Date') }}</label>
                            <input type="date" name="disbursement_date" id="disbursement_date" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="purpose" class="block text-sm font-medium text-secondary">{{ __('Purpose') }}</label>
                            <textarea name="purpose" id="purpose" rows="2" maxlength="500"
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary"></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-button variant="success" type="submit">
                            <x-heroicon-o-check class="mr-2 size-4" />
                            {{ __('Submit Loan Application') }}
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
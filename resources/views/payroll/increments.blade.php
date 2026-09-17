<x-app-layout>
    <x-slot name="header">
        <x-page-header title="💰 {{ __('Salary Increments') }}" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-page-header
                title="Increments Management"
                :description="'Manage salary increments and approvals • '.auth()->user()->currentOrganization->name"
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
                <x-dashboard.stat-card label="{{ __('Total Increments') }}"
                    :value="$increments->total()" tone="info">
                    <x-slot name="icon">
                        <x-heroicon-o-arrow-down class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Pending') }}"
                    :value="$increments->where('status', 'pending')->count()" tone="warning">
                    <x-slot name="icon">
                        <x-heroicon-o-clock class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Approved') }}"
                    :value="$increments->where('status', 'approved')->count()" tone="primary">
                    <x-slot name="icon">
                        <x-heroicon-o-check-circle class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Implemented') }}"
                    :value="$increments->where('status', 'implemented')->count()" tone="success">
                    <x-slot name="icon">
                        <x-heroicon-o-check-badge class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>
            </div>

            <x-card :title="__('Salary Increments')">
                <x-slot name="actions">
                    <a href="{{ route('payroll.increments') }}">
                        <x-button variant="success" type="button">
                            <x-heroicon-o-plus class="mr-2 size-4" />
                            {{ __('New Increment') }}
                        </x-button>
                    </a>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary">
                        <thead class="bg-tertiary">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Employee') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Previous Salary') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('New Salary') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Change') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Effective Date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary">
                            @forelse($increments as $increment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        {{ $increment->employee?->first_name }} {{ $increment->employee?->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        ${{ number_format($increment->previous_salary, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                        ${{ number_format($increment->new_salary, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-success">
                                        +${{ number_format($increment->increment_amount, 2) }}
                                        ({{ $increment->increment_percentage }}%)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">
                                        {{ $increment->effective_date?->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @switch($increment->status)
                                            @case('pending')
                                                <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">{{ __('Pending') }}</span>
                                                @break
                                            @case('approved')
                                                <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">{{ __('Approved') }}</span>
                                                @break
                                            @case('implemented')
                                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-300">{{ __('Implemented') }}</span>
                                                @break
                                            @case('rejected')
                                                <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800 dark:bg-red-900/30 dark:text-red-300">{{ __('Rejected') }}</span>
                                                @break
                                            @default
                                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ ucfirst($increment->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if ($increment->status === 'pending')
                                            <form action="{{ route('payroll.increments.approve', $increment) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                                    {{ __('Approve') }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($increment->status === 'approved')
                                            <form action="{{ route('payroll.increments.implement', $increment) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400">
                                                    {{ __('Implement') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-muted">
                                        {{ __('No increments found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($increments->hasPages())
                    <div class="mt-4">
                        {{ $increments->links() }}
                    </div>
                @endif
            </x-card>

            <x-card :title="__('Create Increment')">
                <form action="{{ route('payroll.increments.store') }}" method="POST">
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
                            <label for="increment_type" class="block text-sm font-medium text-secondary">{{ __('Increment Type') }}</label>
                            <select name="increment_type" id="increment_type" required class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                                <option value="percentage">{{ __('Percentage (%)') }}</option>
                                <option value="fixed_amount">{{ __('Fixed Amount ($)') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="increment_value" class="block text-sm font-medium text-secondary">{{ __('Increment Value') }}</label>
                            <input type="number" name="increment_value" id="increment_value" step="0.01" min="0" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="effective_date" class="block text-sm font-medium text-secondary">{{ __('Effective Date') }}</label>
                            <input type="date" name="effective_date" id="effective_date" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="reason" class="block text-sm font-medium text-secondary">{{ __('Reason') }}</label>
                            <textarea name="reason" id="reason" rows="2" maxlength="500"
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary"></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-button variant="success" type="submit">
                            <x-heroicon-o-check class="mr-2 size-4" />
                            {{ __('Submit Increment') }}
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
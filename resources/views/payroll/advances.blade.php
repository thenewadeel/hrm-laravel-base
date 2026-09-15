<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            💰 Salary Advances Management
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="surface shadow rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-primary">Salary Advances</h1>
                        <p class="mt-1 text-sm text-secondary">Manage employee salary advances and repayment schedules</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('payroll.advance-reports') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                            <x-heroicon-o-archive-box class="w-4 h-4 mr-2" />
                            Advance Reports
                        </a>
                        <button wire:click="$dispatch('openModal', { component: 'payroll.create-advance' })" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring focus:ring-green-300 disabled:opacity-25 transition">
                            <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                            New Advance
                        </button>
                    </div>
                </div>
            </div>

        <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="surface overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <x-heroicon-o-face-smile class="w-5 h-5 text-white" />
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted truncate">Total Advances</dt>
                                    <dd class="text-lg font-medium text-primary">{{ $advances->total() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="surface overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <x-heroicon-o-clock class="w-5 h-5 text-white" />
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted truncate">Pending Approval</dt>
                                    <dd class="text-lg font-medium text-primary">
                                        {{ $advances->where('status', 'pending')->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="surface overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <x-heroicon-o-check-circle class="w-5 h-5 text-white" />
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted truncate">Active Advances</dt>
                                    <dd class="text-lg font-medium text-primary">
                                        {{ $advances->where('status', 'active')->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="surface overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                    <x-heroicon-o-face-smile class="w-5 h-5 text-white" />
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted truncate">Total Outstanding</dt>
                                    <dd class="text-lg font-medium text-primary">
                                        ${{ number_format($advances->sum('balance_amount'), 0) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Advances Table -->
            <div class="surface shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-secondary">
                            <thead class="bg-tertiary">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">
                                        Reference
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">
                                        Employee
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">
                                        Balance
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">
                                        Monthly Deduction
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">
                                        Progress
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">
                                        Request Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-surface divide-y divide-secondary">
                                @forelse($advances as $advance)
                                    <tr id="{{ $advance->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                            {{ $advance->advance_reference }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                            {{ $advance->employee->first_name }} {{ $advance->employee->last_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                            ${{ number_format($advance->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                            ${{ number_format($advance->balance_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                            ${{ number_format($advance->monthly_deduction, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                            <div class="flex items-center">
                                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($advance->months_repaid / $advance->repayment_months) * 100 }}%"></div>
                                                </div>
                                                <span class="text-xs">{{ $advance->months_repaid }}/{{ $advance->repayment_months }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($advance->status)
                                                @case('pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                    @break
                                                @case('approved')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Approved
                                                    </span>
                                                    @break
                                                @case('active')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                    @break
                                                @case('completed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Completed
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        {{ ucfirst($advance->status) }}
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                            {{ $advance->request_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($advance->status === 'pending')
                                                <form wire:submit="approveAdvance({{ $advance->id }})" class="inline">
                                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                                </form>
                                            @endif
                                            <a href="#" class="text-blue-600 hover:text-blue-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-muted">
                                            No salary advances found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($advances->hasPages())
                        <div class="mt-4">
                            {{ $advances->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
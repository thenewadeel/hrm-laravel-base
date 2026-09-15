<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🏢 My Payslips
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Record of your payslips
                </p>
            </div>
            <div class="flex space-x-2">
                <x-button.outline>
                    <x-heroicon-s-cog-6-tooth class="w-4 h-4 mr-2" />
                    Settings
                </x-button.outline>
                <x-button.primary>
                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                    Generate Report
                </x-button.primary>
            </div>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        My Payslips
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Access and download your salary payslips</p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
                <!-- Average Monthly Salary -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-face-frown class="h-6 w-6 text-green-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Avg. Monthly Salary</dt>
                                    <dd class="text-lg font-semibold text-gray-900">
                                        ${{ number_format($payslips->avg('net_pay') ?? 0, 2) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last Payslip -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-document-text class="h-6 w-6 text-blue-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Last Payslip</dt>
                                    <dd class="text-lg font-semibold text-gray-900">
                                        {{ $payslips->first()?->period ? \Carbon\Carbon::parse($payslips->first()->period)->format('M Y') : 'N/A' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Records -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-document-duplicate class="h-6 w-6 text-purple-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Records</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $payslips->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payslips Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Salary Payslips
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pay Period</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Basic Salary</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Allowances</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Deductions</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Net Pay</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Paid Date</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($payslips as $payslip)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($payslip->period)->format('F Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500">Payroll #{{ $payslip->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($payslip->basic_salary, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            <span>Housing: ${{ number_format($payslip->housing_allowance, 2) }}</span>
                                            <span>Transport:
                                                ${{ number_format($payslip->transport_allowance, 2) }}</span>
                                            <span>Overtime: ${{ number_format($payslip->overtime_pay, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            <span>Tax: ${{ number_format($payslip->tax_deduction, 2) }}</span>
                                            <span>Insurance:
                                                ${{ number_format($payslip->insurance_deduction, 2) }}</span>
                                            <span>Other: ${{ number_format($payslip->other_deductions, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-lg font-semibold text-green-600">
                                            ${{ number_format($payslip->net_pay, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'paid' => 'bg-green-100 text-green-800',
                                                'processed' => 'bg-blue-100 text-blue-800',
                                                'draft' => 'bg-gray-100 text-gray-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$payslip->status] }}">
                                            {{ ucfirst($payslip->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $payslip->paid_at ? $payslip->paid_at->format('M d, Y') : '--' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('portal.employee.payslips.show', $payslip) }}"
                                            class="text-blue-600 hover:text-blue-900">View</a>
                                        <span class="mx-2">|</span>
                                        <a href="{{ route('portal.employee.payslips.download', $payslip) }}"
                                            class="text-green-600 hover:text-green-900">Download</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No payslips found for your account.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                <!-- Yearly Summary -->
                @if ($payslips->isNotEmpty())
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Yearly Summary - {{ now()->year }}</h4>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Total Earnings</div>
                                <div class="text-lg font-semibold text-gray-900">
                                    ${{ number_format($payslips->sum('gross_pay'), 2) }}
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Total Deductions</div>
                                <div class="text-lg font-semibold text-gray-900">
                                    ${{ number_format($payslips->sum('total_deductions'), 2) }}
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Net Income</div>
                                <div class="text-lg font-semibold text-green-600">
                                    ${{ number_format($payslips->sum('net_pay'), 2) }}
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Avg. Monthly</div>
                                <div class="text-lg font-semibold text-blue-600">
                                    ${{ number_format($payslips->avg('net_pay'), 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

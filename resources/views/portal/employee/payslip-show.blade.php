<x-app-layout><x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">📜 Payslip:
                    {{ \Carbon\Carbon::parse($payslip->period)->format('F Y') }}</h2>
                <p class="text-sm text-gray-600 mt-1">Payslip Details #{{ $payslip->id }}</p>
            </div>
            <div class="flex space-x-2"><!-- Back Button --><a
                    href="{{ route('portal.employee.payslips') }}"><x-button.outline><x-heroicon-s-arrow-left
                            class="w-4 h-4 mr-2" />Back to Payslips</x-button.outline></a><!-- Download Button --><a
                    href="{{ route('portal.employee.payslips.download', $payslip) }}"><x-button.primary><x-heroicon-s-document-arrow-down
                            class="w-4 h-4 mr-2" />Download PDF</x-button.primary></a></div>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Payslip Document Container -->
            <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200">

                <!-- Payslip Header: Company and Pay Period -->
                <div class="p-8 bg-gray-50 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <!-- Company Details -->
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">Issued By</p>
                            <h3 class="text-2xl font-bold text-gray-900">ACME Corp Payroll</h3>
                            <p class="text-sm text-gray-500">123 Business Blvd, Suite 400</p>
                            <p class="text-sm text-gray-500">City, State 90210</p>
                        </div>
                        <!-- Pay Period Details -->
                        <div class="text-right">
                            <p class="text-xs font-semibold uppercase text-gray-500">Pay Period</p>
                            <p class="text-xl font-bold text-gray-800">
                                {{ \Carbon\Carbon::parse($payslip->period)->format('F d, Y') }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Paid Date: {{ $payslip->paid_at ? $payslip->paid_at->format('M d, Y') : 'N/A' }}
                            </p>
                            <span
                                class="inline-flex mt-2 items-center px-3 py-1 rounded-full text-sm font-medium {{ ['paid' => 'bg-green-100 text-green-800', 'processed' => 'bg-blue-100 text-blue-800'][$payslip->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($payslip->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Employee Details -->
                <div class="px-8 py-6 border-b border-gray-200 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="font-semibold text-gray-700">Employee Name:</p>
                        <p class="text-gray-900">Jane Doe (Mock Data)</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700">Position:</p>
                        <p class="text-gray-900">Software Engineer (Mock Data)</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700">Employee ID:</p>
                        <p class="text-gray-900">EMP-4567</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700">Payment Method:</p>
                        <p class="text-gray-900">Direct Deposit (****1234)</p>
                    </div>
                </div>

                <!-- Earnings and Deductions Sections -->
                <div class="p-8 grid md:grid-cols-2 gap-8">
                    <!-- Column 1: Earnings -->
                    <div>
                        <h4 class="text-lg font-bold text-green-700 border-b pb-2 mb-4">Earnings</h4>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-gray-700">Basic Salary</dt>
                                <dd class="font-medium text-gray-900">${{ number_format($payslip->basic_salary, 2) }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-700">Housing Allowance</dt>
                                <dd class="font-medium text-gray-900">
                                    ${{ number_format($payslip->housing_allowance, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-700">Transport Allowance</dt>
                                <dd class="font-medium text-gray-900">
                                    ${{ number_format($payslip->transport_allowance, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-700">Overtime Pay</dt>
                                <dd class="font-medium text-gray-900">${{ number_format($payslip->overtime_pay, 2) }}
                                </dd>
                            </div>

                            <!-- Gross Pay Total -->
                            <div class="pt-3 border-t border-dashed border-gray-300 flex justify-between">
                                <dt class="text-base font-bold text-green-700">GROSS PAY</dt>
                                <dd class="text-lg font-bold text-green-700">
                                    ${{ number_format($payslip->gross_pay, 2) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Column 2: Deductions -->
                    <div>
                        <h4 class="text-lg font-bold text-red-700 border-b pb-2 mb-4">Deductions</h4>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-gray-700">Income Tax (PAYE)</dt>
                                <dd class="font-medium text-red-600">(${{ number_format($payslip->tax_deduction, 2) }})
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-700">Health Insurance</dt>
                                <dd class="font-medium text-red-600">
                                    (${{ number_format($payslip->insurance_deduction, 2) }})</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-700">Social Security / Pension</dt>
                                <dd class="font-medium text-red-600">
                                    (${{ number_format($payslip->social_security_deduction ?? 0, 2) }})</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-700">Other Deductions</dt>
                                <dd class="font-medium text-red-600">
                                    (${{ number_format($payslip->other_deductions, 2) }})</dd>
                            </div>

                            <!-- Total Deductions -->
                            <div class="pt-3 border-t border-dashed border-gray-300 flex justify-between">
                                <dt class="text-base font-bold text-red-700">TOTAL DEDUCTIONS</dt>
                                <dd class="text-lg font-bold text-red-700">
                                    (${{ number_format($payslip->total_deductions, 2) }})</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Final Net Pay Summary -->
                <div class="bg-blue-600 p-6 text-white">
                    <div class="flex justify-between items-center">
                        <h4 class="text-2xl font-extrabold tracking-tight">NET PAY</h4>
                        <div class="text-4xl font-extrabold">
                            ${{ number_format($payslip->net_pay, 2) }}
                        </div>
                    </div>
                    <p class="text-sm text-blue-200 mt-2">
                        This is the amount deposited into your bank account.
                    </p>
                </div>
            </div>

            <div class="mt-4 text-center text-sm text-gray-500">
                Payslip generated on {{ now()->format('M d, Y H:i A') }}
            </div>

        </div>
    </div>
</x-app-layout>

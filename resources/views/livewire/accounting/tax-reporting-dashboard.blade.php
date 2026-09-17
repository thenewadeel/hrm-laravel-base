<div>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Tax Reporting Dashboard</h2>
            <button wire:click="exportReport"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <x-heroicon-o-arrow-down-tray class="w-5 h-5 mr-2" />
                Export Report
            </button>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" wire:model.live="startDate"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                    <input type="date" wire:model.live="endDate"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax Type</label>
                    <select wire:model.live="taxType"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @foreach($taxTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Report Type</label>
                    <select wire:model.live="reportType"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="summary">Summary</option>
                        <option value="liability">Liability</option>
                        <option value="filing_schedule">Filing Schedule</option>
                    </select>
                </div>
            </div>
        </div>

        @if($taxReport)
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Tax Collected</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($taxReport['summary']['total_tax_collected'], 2) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Base Amount</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($taxReport['summary']['total_base_amount'], 2) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Transactions</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $taxReport['summary']['total_transactions'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Average Tax Rate</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($taxReport['summary']['average_tax_rate'] ?? 0, 2) }}%</div>
                </div>
            </div>

            @if($reportType === 'summary')
                {{-- By Tax Type --}}
                @if($taxReport['by_tax_type']->count())
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">By Tax Type</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tax Type</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Base Amount</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tax Amount</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Avg Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($taxReport['by_tax_type'] as $type)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $type['tax_type'] }}</td>
                                            <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-white">${{ number_format($type['total_base_amount'], 2) }}</td>
                                            <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-white">${{ number_format($type['total_tax_amount'], 2) }}</td>
                                            <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-white">{{ number_format($type['average_rate'], 2) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Monthly Breakdown --}}
                @if($taxReport['monthly_breakdown']->count())
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Monthly Breakdown</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Month</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tax Amount</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Transactions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($taxReport['monthly_breakdown'] as $month)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $month['month'] }}</td>
                                            <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-white">${{ number_format($month['total_tax_amount'], 2) }}</td>
                                            <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-white">{{ $month['transaction_count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif

            @if($reportType === 'liability')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tax Liability (as of {{ \Carbon\Carbon::parse($liabilityReport['as_of_date'])->format('M d, Y') }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tax Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Collected</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Paid</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Outstanding</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($liabilityReport['liabilities'] as $liability)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $liability['tax_rate_name'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $liability['tax_type'] }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-white">${{ number_format($liability['total_collected'], 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-white">${{ number_format($liability['total_paid'], 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-semibold {{ $liability['outstanding_liability'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                            ${{ number_format($liability['outstanding_liability'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No liability data found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white text-right">Total Outstanding:</td>
                                    <td class="px-6 py-4 text-sm font-bold text-right text-red-600 dark:text-red-400">${{ number_format($liabilityReport['total_liability'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            @if($reportType === 'filing_schedule')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filing Schedule ({{ $filingSchedule['months_ahead'] }} months)</h3>
                        @if($filingSchedule['overdue_filings'])
                            <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                {{ $filingSchedule['overdue_filings'] }} overdue
                            </span>
                        @endif
                        @if($filingSchedule['upcoming_filings'])
                            <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                {{ $filingSchedule['upcoming_filings'] }} due soon
                            </span>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tax Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($filingSchedule['schedule'] as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $item['tax_rate_name'] }} ({{ $item['tax_type'] }})</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $item['period_start'] }} to {{ $item['period_end'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                            {{ $item['due_date'] }}
                                            @if($item['days_until_due'] < 0)
                                                <span class="ml-1 text-xs text-red-600 dark:text-red-400">({{ abs($item['days_until_due']) }}d overdue)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $item['status'] === 'Paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($item['status'] === 'Accepted' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                                {{ $item['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No upcoming filings.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

        {{-- Compliance Overview --}}
        @if($complianceDashboard)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Active Tax Rates</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $complianceDashboard['summary']['total_tax_rates'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Active Exemptions</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $complianceDashboard['summary']['active_exemptions'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Pending Filings</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $complianceDashboard['summary']['pending_filings'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Overdue Filings</div>
                    <div class="text-2xl font-bold {{ $complianceDashboard['summary']['overdue_filings'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $complianceDashboard['summary']['overdue_filings'] }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-message', ([message, type]) => {
                const toast = document.createElement('div');
                const colors = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
                toast.className = `fixed top-4 right-4 z-50 px-4 py-3 text-white rounded-lg shadow-lg ${colors} transition-opacity`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
            });
        });
    </script>
</div>

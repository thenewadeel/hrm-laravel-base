<div>
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Accounts Payable Outstanding</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Vendor outstanding balances with aging analysis</p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Vendor Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vendor</label>
                <select wire:model.live="vendorId" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All Vendors</option>
                    @foreach($this->vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                <input type="date" wire:model.live="startDate" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                <input type="date" wire:model.live="endDate" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- As Of Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">As Of Date</label>
                <input type="date" wire:model.live="asOfDate" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button wire:click="refreshStatement" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
                <button wire:click="resetFilters" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    <i class="fas fa-undo mr-2"></i>Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @if($statement['summary']['total_outstanding'] > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Outstanding</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($statement['summary']['total_outstanding'], 2) }}
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Current</div>
                <div class="text-2xl font-bold text-green-600">
                    {{ number_format($statement['summary']['aging']['current'], 2) }}
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">30 Days</div>
                <div class="text-2xl font-bold text-yellow-600">
                    {{ number_format($statement['summary']['aging']['30_days'], 2) }}
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">60 Days</div>
                <div class="text-2xl font-bold text-orange-600">
                    {{ number_format($statement['summary']['aging']['60_days'], 2) }}
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">90+ Days</div>
                <div class="text-2xl font-bold text-red-600">
                    {{ number_format($statement['summary']['aging']['90_days'], 2) }}
                </div>
            </div>
        </div>
    @endif

    <!-- Export Buttons -->
    <div class="flex justify-end gap-2 mb-4">
        <button wire:click="exportPdf" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </button>
        <button wire:click="exportExcel" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </button>
    </div>

    <!-- Vendor Statements Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Outstanding</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">30 Days</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">60 Days</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">90+ Days</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($statement['vendor_statements'] as $vendorStatement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $vendorStatement['vendor']['name'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-gray-300">
                                    @if($vendorStatement['vendor']['email'])
                                        <div>{{ $vendorStatement['vendor']['email'] }}</div>
                                    @endif
                                    @if($vendorStatement['vendor']['phone'])
                                        <div class="text-gray-500">{{ $vendorStatement['vendor']['phone'] }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($vendorStatement['total_outstanding'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600">
                                {{ number_format($vendorStatement['aging']['current'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-yellow-600">
                                {{ number_format($vendorStatement['aging']['30_days'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-orange-600">
                                {{ number_format($vendorStatement['aging']['60_days'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600">
                                {{ number_format($vendorStatement['aging']['90_days'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                <button wire:click="showVendorDetails({{ $vendorStatement }})" 
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    <i class="fas fa-eye mr-1"></i>View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No outstanding payables found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Vendor Details Modal -->
    @if($showDetails)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDetails">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Vendor Details: {{ $selectedVendor['vendor']['name'] }}
                        </h3>
                        <button wire:click="closeDetails" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Vendor Info -->
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Email</div>
                                <div class="font-medium">{{ $selectedVendor['vendor']['email'] ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Phone</div>
                                <div class="font-medium">{{ $selectedVendor['vendor']['phone'] ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Payment Terms</div>
                                <div class="font-medium">{{ $selectedVendor['vendor']['payment_terms'] ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Address</div>
                                <div class="font-medium">{{ $selectedVendor['vendor']['address'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Outstanding Summary -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Outstanding</div>
                            <div class="text-lg font-bold">{{ number_format($selectedVendor['total_outstanding'], 2) }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Current</div>
                            <div class="text-lg font-bold text-green-600">{{ number_format($selectedVendor['aging']['current'], 2) }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600 dark:text-gray-400">30 Days</div>
                            <div class="text-lg font-bold text-yellow-600">{{ number_format($selectedVendor['aging']['30_days'], 2) }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600 dark:text-gray-400">60 Days</div>
                            <div class="text-lg font-bold text-orange-600">{{ number_format($selectedVendor['aging']['60_days'], 2) }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600 dark:text-gray-400">90+ Days</div>
                            <div class="text-lg font-bold text-red-600">{{ number_format($selectedVendor['aging']['90_days'], 2) }}</div>
                        </div>
                    </div>

                    <!-- Bill Details -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bill #</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Due Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Days Overdue</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($selectedVendor['entries'] as $entry)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">{{ $entry['invoice_number'] ?? $entry['reference_number'] }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $entry['entry_date'] }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $entry['due_date'] }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $entry['days_overdue'] }}</td>
                                        <td class="px-4 py-2 text-sm text-right font-medium">{{ number_format($entry['total_amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
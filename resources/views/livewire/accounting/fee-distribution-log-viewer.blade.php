<div>
    <!-- Header with title and actions -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Fee Distribution Logs
                </h2>
                <button wire:click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    Reset Filters
                </button>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="px-6 py-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input 
                        wire:model.live="search" 
                        type="text" 
                        placeholder="Search logs..."
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select wire:model.live="statusFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">All Statuses</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Fee Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fee Type</label>
                    <select wire:model.live="feeTypeFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">All Types</option>
                        @foreach($feeTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                    <input 
                        wire:model.live="dateFrom" 
                        type="date" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                </div>
                
                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                    <input 
                        wire:model.live="dateTo" 
                        type="date" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary Cards -->
    @if(isset($summary))
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Amount</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($summary['total_amount'], 2) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Distributed</div>
            <div class="text-xl font-semibold text-green-600 dark:text-green-400">{{ number_format($summary['total_distributed'], 2) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Success</div>
            <div class="text-xl font-semibold text-green-600 dark:text-green-400">{{ $summary['success_count'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Failed</div>
            <div class="text-xl font-semibold text-red-600 dark:text-red-400">{{ $summary['failed_count'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Partial</div>
            <div class="text-xl font-semibold text-yellow-600 dark:text-yellow-400">{{ $summary['partial_count'] }}</div>
        </div>
    </div>
    @endif
    
    <!-- Logs Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Member
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $log->distributed_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $log->memberFee->member->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $log->memberFee->description ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ number_format($log->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($log->status === 'success') 
                                        bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($log->status === 'failed')
                                        bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @else
                                        bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @endif">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <button wire:click="showDetails({{ $log->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No fee distribution logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
    
    <!-- Details Modal -->
    @if($showDetailsModal && $selectedLog)
        <div wire:click="showDetailsModal = false" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Distribution Details</h3>
                    <button wire:click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <strong>Total Amount:</strong> {{ number_format($selectedLog->total_amount, 2) }}
                    </div>
                    <div>
                        <strong>Distributed Amount:</strong> {{ number_format($selectedLog->actual_distributed_amount, 2) }}
                    </div>
                    <div>
                        <strong>Status:</strong> 
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($selectedLog->status === 'success') 
                                bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($selectedLog->status === 'failed')
                                bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @else
                                bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @endif">
                            {{ ucfirst($selectedLog->status) }}
                        </span>
                    </div>
                    
                    @if($selectedLog->error_message)
                    <div>
                        <strong>Error Message:</strong> {{ $selectedLog->error_message }}
                    </div>
                    @endif
                    
                    @if($selectedLog->distribution_breakdown)
                    <div>
                        <strong>Distribution Breakdown:</strong>
                        <div class="mt-2 space-y-2">
                            @foreach($selectedLog->distribution_breakdown as $item)
                            <div class="border rounded p-2 dark:border-gray-600">
                                <div>Account ID: {{ $item['account_id'] ?? 'N/A' }}</div>
                                <div>Type: {{ $item['type'] ?? 'N/A' }}</div>
                                <div>Amount: {{ number_format($item['amount'] ?? 0, 2) }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
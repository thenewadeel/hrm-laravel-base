<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Advanced Member Management</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage and search members with advanced filtering options
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <button wire:click="$toggle('showFilters')" 
                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filters
                </button>
                <button wire:click="$toggle('showExportModal')" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        @if(isset($statistics))
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $statistics['total_members'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Members</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $statistics['active_members'] ?? 0 }}</div>
                    <div class="text-sm text-green-500 dark:text-green-400">Active Members</div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $statistics['inactive_members'] ?? 0 }}</div>
                    <div class="text-sm text-yellow-500 dark:text-yellow-400">Inactive Members</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $statistics['expired_members'] ?? 0 }}</div>
                    <div class="text-sm text-red-500 dark:text-red-400">Expired Members</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Filters Panel -->
    @if($showFilters)
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Advanced Filters</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Members</label>
                    <input wire:model.live.debounce.300ms="search" 
                           type="text" 
                           placeholder="Search by name, email, phone, membership number..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select wire:model.live="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Subscription Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subscription Status</label>
                    <select wire:model.live="subscriptionStatus" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        @foreach($subscriptionStatuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                    <select wire:model.live="dateRange" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        @foreach($dateRanges as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
                    <select wire:model.live="sortBy" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        @foreach($sortOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Per Page</label>
                    <select wire:model.live="perPage" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <!-- Family Filter -->
                <div class="flex items-center">
                    <input wire:model.live="hasFamily" type="checkbox" id="hasFamily" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="hasFamily" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        Has Family Members
                    </label>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex items-center justify-between mt-6">
                <div class="flex items-center space-x-3">
                    <button wire:click="resetFilters" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reset Filters
                    </button>
                    
                    <!-- Filter Presets -->
                    <div class="flex items-center space-x-2">
                        <input wire:model="presetName" type="text" placeholder="Preset name..." 
                               class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <button wire:click="saveFilterPreset" 
                                class="px-3 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900 border border-indigo-200 dark:border-indigo-700 rounded-md hover:bg-indigo-100 dark:hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Preset
                        </button>
                    </div>
                </div>

                <!-- Load Preset -->
                @if(!empty($filterPresets))
                    <select wire:change="loadFilterPreset($event.target.value)" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Load Preset...</option>
                        @foreach(array_keys($filterPresets) as $presetName)
                            <option value="{{ $presetName }}">{{ $presetName }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>
    @endif

    <!-- Bulk Actions -->
    @if(!empty($selectedMembers))
        <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-blue-700 dark:text-blue-300">
                        {{ count($selectedMembers) }} member(s) selected
                    </span>
                </div>
                <div class="flex items-center space-x-3">
                    <select wire:model="bulkAction" class="px-3 py-2 text-sm border border-blue-300 dark:border-blue-600 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-blue-800 dark:text-white">
                        <option value="">Select Action...</option>
                        @foreach($bulkActions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <button wire:click="performBulkAction" 
                            wire:confirm="Are you sure you want to perform this action on selected members?"
                            class="px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-800 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-200 dark:hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Execute Action
                    </button>
                    <button wire:click="$set('selectedMembers', [])" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Clear Selection
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Members Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sort('membership_number')">
                            Membership #
                            @if($sortBy === 'membership_number')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sort('first_name')">
                            Name
                            @if($sortBy === 'first_name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Family
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sort('join_date')">
                            Join Date
                            @if($sortBy === 'join_date')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer" wire:click="sort('expiry_date')">
                            Expiry Date
                            @if($sortBy === 'expiry_date')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↓' : '↑' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($members as $member)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedMembers" value="{{ $member->id }}" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $member->membership_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $member->full_name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $member->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $member->phone }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($member->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($member->status === 'inactive') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                    @elseif($member->status === 'suspended') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($member->status === 'expired') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                    @endif">
                                    {{ ucfirst($member->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $member->familyMembers->count() }} family member(s)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $member->join_date?->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($member->expiry_date)
                                    <span class="{{ $member->isExpiringSoon() ? 'text-yellow-600 dark:text-yellow-400 font-medium' : '' }}">
                                        {{ $member->expiry_date->format('M d, Y') }}
                                    </span>
                                    @if($member->isExpiringSoon())
                                        <span class="text-xs text-yellow-600 dark:text-yellow-400 block">Expiring Soon</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">No expiry</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button wire:click="viewMember({{ $member->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">
                                    View
                                </button>
                                <button wire:click="editMember({{ $member->id }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-3">
                                    Edit
                                </button>
                                <button wire:click="deleteMember({{ $member->id }})" 
                                        wire:confirm="Are you sure you want to delete this member?"
                                        class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="mt-2">No members found</p>
                                    <p class="text-sm">Try adjusting your search criteria or filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($members->hasPages())
            <div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button wire:click="previousPage" wire:disabled="{{ $members->onFirstPage() }}" 
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50">
                        Previous
                    </button>
                    <button wire:click="nextPage" wire:disabled="{{ $members->onLastPage() }}"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ $members->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $members->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $members->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            {{ $members->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Export Modal -->
    @if($showExportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" wire:click="$toggle('showExportModal')">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                                    Export Members
                                </h3>
                                
                                <!-- Export Format -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Export Format</label>
                                    <div class="flex space-x-4">
                                        <label class="flex items-center">
                                            <input wire:model="exportFormat" type="radio" value="csv" class="mr-2">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">CSV</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input wire:model="exportFormat" type="radio" value="pdf" class="mr-2">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">PDF</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Export Columns -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Include Columns</label>
                                    <div class="space-y-2">
                                        @foreach($exportColumns as $column => $enabled)
                                            <label class="flex items-center">
                                                <input wire:model="exportColumns.{{ $column }}" type="checkbox" class="mr-2">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ ucwords(str_replace('_', ' ', $column)) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="exportMembers('{{ $exportFormat }}')" type="button" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Export
                        </button>
                        <button wire:click="$toggle('showExportModal')" type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
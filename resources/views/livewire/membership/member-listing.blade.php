<div>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Member Management</h1>
            
            @if ($canExportMembers)
                <button wire:click="exportMembers" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Export Members
                </button>
            @endif
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Members</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $memberStats['total'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active</h3>
                <p class="text-2xl font-bold text-green-600">{{ $memberStats['active'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Inactive</h3>
                <p class="text-2xl font-bold text-gray-600">{{ $memberStats['inactive'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Expired</h3>
                <p class="text-2xl font-bold text-red-600">{{ $memberStats['expired'] }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="Search members..." 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <select wire:model.live="statusFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="perPage" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="10">10 per page</option>
                        <option value="15">15 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Members Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            @if ($canManageMembers)
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" wire:model.live="selectAll">
                                </th>
                            @endif
                            <th wire:click="sortBy('first_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                                Name
                            </th>
                            <th wire:click="sortBy('email')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                                Email
                            </th>
                            <th wire:click="sortBy('membership_number')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                                Membership #
                            </th>
                            <th wire:click="sortBy('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                                Status
                            </th>
                            <th wire:click="sortBy('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                                Joined
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($members as $member)
                            <tr>
                                @if ($canManageMembers)
                                    <td class="px-6 py-4">
                                        <input type="checkbox" wire:model.live="selectedMembers" value="{{ $member->id }}">
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $member->full_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $member->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $member->membership_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $this->getMemberStatusClass($member->status) }}">
                                        {{ $this->getMemberStatusText($member->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $member->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManageMembers ? 6 : 5 }}" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No members found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                {{ $members->links() }}
            </div>
        </div>

        <!-- Bulk Actions -->
        @if ($canManageMembers && count($selectedMembers) > 0)
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ count($selectedMembers) }} member(s) selected
                    </span>
                    <select wire:model="bulkAction" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select action</option>
                        <option value="active">Set Active</option>
                        <option value="inactive">Set Inactive</option>
                        <option value="expired">Set Expired</option>
                    </select>
                    <button wire:click="bulkUpdateStatus($bulkAction)" 
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Apply Action
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
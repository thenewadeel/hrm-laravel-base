<div>
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Fixed Assets</h2>
            <div class="flex gap-2">
                <button wire:click="$dispatch('open-asset-form')" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Asset
                </button>
                <button wire:click="$dispatch('open-depreciation-modal')" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-calculator mr-2"></i>Post Depreciation
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input wire:model.live="search" 
                   type="text" 
                   placeholder="Search assets..." 
                   class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            
            <select wire:model.live="category" 
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All Categories</option>
                @foreach($this->categories as $category)
                    <option value="{{ $category->code }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="status" 
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All Status</option>
                @foreach($this->statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <input wire:model.live="location" 
                   type="text" 
                   placeholder="Filter by location..." 
                   class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        </div>

        @if($search || $category || $status || $location)
            <div class="mt-4 flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Active filters:</span>
                <button wire:click="resetFilters" 
                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    Clear all
                </button>
            </div>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <i class="fas fa-cube text-blue-600 dark:text-blue-300"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Assets</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $this->totalAssets }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                    <i class="fas fa-dollar-sign text-green-600 dark:text-green-300"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Book Value</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($this->totalBookValue, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <i class="fas fa-check-square text-purple-600 dark:text-purple-300"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Selected</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ count($this->selectedAssets) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(count($selectedAssets) > 0)
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <span class="text-blue-800 dark:text-blue-200">
                    {{ count($selectedAssets) }} asset(s) selected
                </span>
                <div class="flex gap-2">
                    <button wire:click="bulkDepreciation" 
                            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition-colors text-sm">
                        <i class="fas fa-calculator mr-1"></i>Depreciate
                    </button>
                    <button wire:click="bulkDispose" 
                            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-sm">
                        <i class="fas fa-trash mr-1"></i>Dispose
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Assets Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" 
                                   wire:model.live="selectAll" 
                                   class="rounded border-gray-300">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Asset Tag
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Location
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Purchase Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Book Value
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
                    @forelse($this->assets as $asset)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4">
                                <input type="checkbox" 
                                       wire:model.live="selectedAssets" 
                                       value="{{ $asset->id }}"
                                       class="rounded border-gray-300">
                            </td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $asset->asset_tag }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $asset->name }}
                                </div>
                                @if($asset->description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ Str::limit($asset->description, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $asset->category?->name }}</td>
                            <td class="px-6 py-4 text-sm">{{ $asset->location }}</td>
                            <td class="px-6 py-4 text-sm">{{ $asset->purchase_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm font-medium">
                                {{ number_format($asset->current_book_value, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($asset->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($asset->status === 'inactive') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                    @elseif($asset->status === 'disposed') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @endif">
                                    {{ ucfirst($asset->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex space-x-2">
                                    <button wire:click="$dispatch('edit-asset', { id: {{ $asset->id }} })" 
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="$dispatch('transfer-asset', { id: {{ $asset->id }} })" 
                                            class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                            title="Transfer">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <button wire:click="$dispatch('maintain-asset', { id: {{ $asset->id }} })" 
                                            class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                            title="Maintenance">
                                        <i class="fas fa-wrench"></i>
                                    </button>
                                    <button wire:click="$dispatch('dispose-asset', { id: {{ $asset->id }} })" 
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="Dispose">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-cube text-4xl mb-4"></i>
                                <p class="text-lg font-medium">No assets found</p>
                                <p class="text-sm">Get started by adding your first fixed asset.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $this->assets->links() }}
        </div>
    </div>
</div>

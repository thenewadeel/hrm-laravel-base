<div>
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Post Depreciation</h3>

        <!-- Date Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Depreciation Date <span class="text-red-500">*</span>
            </label>
            <input wire:model="selectedDate" 
                   type="date" 
                   class="w-full md:w-auto rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            @error('selectedDate')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Assets Ready for Depreciation -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">
                Assets Ready for Depreciation ({{ $assets->total() }})
            </h4>
            
            @if($assets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
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
                                    Method
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Current Book Value
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Est. Annual Depreciation
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($assets as $asset)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 font-mono text-sm">{{ $asset->asset_tag }}</td>
                                    <td class="px-6 py-4 text-sm font-medium">{{ $asset->name }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $asset->category?->name }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $asset->depreciation_method === 'straight_line' ? 'Straight Line' : 
                                               ($asset->depreciation_method === 'declining_balance' ? 'Declining Balance' : 'Sum of Years') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        {{ number_format($asset->current_book_value, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        {{ number_format($asset->calculateAnnualDepreciation(), 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $assets->links() }}
                </div>
            @else
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-calculator text-4xl mb-4"></i>
                    <p class="text-lg font-medium">No assets ready for depreciation</p>
                    <p class="text-sm">All assets are up to date with depreciation.</p>
                </div>
            @endif
        </div>

        <!-- Results Section -->
        @if($showResults)
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Depreciation Results</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400 mr-3"></i>
                            <div>
                                <p class="text-sm text-green-600 dark:text-green-400">Successfully Processed</p>
                                <p class="text-2xl font-bold text-green-800 dark:text-green-200">{{ $results['processed'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 mr-3"></i>
                            <div>
                                <p class="text-sm text-red-600 dark:text-red-400">Errors</p>
                                <p class="text-2xl font-bold text-red-800 dark:text-red-200">{{ $results['errors'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mr-3"></i>
                            <div>
                                <p class="text-sm text-blue-600 dark:text-blue-400">Total Assets</p>
                                <p class="text-2xl font-bold text-blue-800 dark:text-blue-200">
                                    {{ $results['processed'] + $results['errors'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($results['errors'] > 0)
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <h5 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">Error Details:</h5>
                        <ul class="text-sm text-red-700 dark:text-red-300 space-y-1">
                            @foreach($results['error_details'] as $error)
                                <li>
                                    <strong>{{ $error['asset'] }}:</strong> {{ $error['error'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <!-- Actions -->
        <div class="flex justify-end space-x-3 mt-6">
            <button wire:click="$dispatch('close-modal')" 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Close
            </button>
            <button wire:click="postDepreciation" 
                    wire:loading.attr="disabled"
                    wire:target="postDepreciation"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center disabled:opacity-50">
                <span wire:loading wire:target="postDepreciation">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Processing...
                </span>
                <span wire:loading.remove wire:target="postDepreciation">
                    <i class="fas fa-calculator mr-2"></i>
                    Post Depreciation
                </span>
            </button>
        </div>
    </div>
</div>

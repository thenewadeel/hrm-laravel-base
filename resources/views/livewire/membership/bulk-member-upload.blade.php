<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-8">
    <div class="max-w-6xl mx-auto px-6">
        <div class="space-y-10">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Bulk Member Upload</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">Import multiple members from a CSV file</p>
                </div>
                <button wire:click="downloadTemplate" 
                        class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-xl transition-all duration-200 flex items-center shadow-lg hover:shadow-xl">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
                    Download Template
                </button>
            </div>
        </div>

        <!-- Upload Type Selection -->
        @if(!$showPreview)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Select Upload Type</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <label class="relative">
                        <input type="radio" wire:model="uploadType" value="individual" class="sr-only peer">
                        <div class="p-6 border-2 rounded-xl cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:bg-gradient-to-r peer-checked:from-blue-50 peer-checked:to-indigo-50 dark:peer-checked:from-blue-900/20 dark:peer-checked:to-indigo-900/20 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800 rounded-full flex items-center justify-center shadow-lg">
                                    <x-heroicon-o-user class="w-7 h-7 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Individual Members</h4>
                                    <p class="text-base text-gray-600 dark:text-gray-400">Import individual members</p>
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="relative">
                        <input type="radio" wire:model="uploadType" value="family" class="sr-only peer">
                        <div class="p-6 border-2 rounded-xl cursor-pointer transition-all peer-checked:border-purple-500 peer-checked:bg-gradient-to-r peer-checked:from-purple-50 peer-checked:to-pink-50 dark:peer-checked:from-purple-900/20 dark:peer-checked:to-pink-900/20 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900 dark:to-purple-800 rounded-full flex items-center justify-center shadow-lg">
                                    <x-heroicon-o-user-group class="w-7 h-7 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Family Members</h4>
                                    <p class="text-base text-gray-600 dark:text-gray-400">Import family members</p>
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="relative">
                        <input type="radio" wire:model="uploadType" value="corporate" class="sr-only peer">
                        <div class="p-6 border-2 rounded-xl cursor-pointer transition-all peer-checked:border-green-500 peer-checked:bg-gradient-to-r peer-checked:from-green-50 peer-checked:to-emerald-50 dark:peer-checked:from-green-900/20 dark:peer-checked:to-emerald-900/20 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 rounded-full flex items-center justify-center shadow-lg">
                                    <x-heroicon-o-document-text class="w-7 h-7 text-green-600 dark:text-green-400" />
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Corporate Members</h4>
                                    <p class="text-base text-gray-600 dark:text-gray-400">Import corporate employees</p>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        @endif

        <!-- File Upload -->
        @if(!$showPreview)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Upload CSV File</h3>
                
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-12 text-center hover:border-blue-500 dark:hover:border-blue-400 transition-all duration-200 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700">
                    <x-heroicon-o-cloud-arrow-up class="mx-auto h-16 w-16 text-gray-400" />
                    
                    <div class="mt-6">
                        <label for="csvFile" class="cursor-pointer">
                            <span class="mt-3 block text-lg font-semibold text-gray-900 dark:text-white">
                                Click to upload or drag and drop
                            </span>
                            <span class="mt-2 block text-sm text-gray-500 dark:text-gray-400">
                                CSV files only (MAX. 10MB)
                            </span>
                        </label>
                        <input id="csvFile" type="file" wire:model="csvFile" accept=".csv,.txt" class="sr-only">
                    </div>
                    
                    @if($csvFile)
                        <div class="mt-4 flex items-center justify-center">
                            <div class="flex items-center space-x-2 text-sm text-green-600 dark:text-green-400">
                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                <span>{{ $csvFile->getClientOriginalName() }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                @error('csvFile')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div class="mt-8 flex justify-end">
                    <button wire:click="uploadCsv" 
                            wire:loading.attr="disabled"
                            class="px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:from-blue-400 disabled:to-blue-500 text-white font-semibold rounded-xl transition-all duration-200 flex items-center shadow-lg hover:shadow-xl">
                        <span wire:loading.remove>Process CSV</span>
                        <span wire:loading>Processing...</span>
                        <x-heroicon-o-chevron-right class="w-4 h-4 ml-2" />
                        <x-heroicon-o-arrow-path class="animate-spin w-4 h-4 ml-2" />
                    </button>
                </div>
            </div>
        @endif

        <!-- Column Mapping -->
        @if($showPreview && count($columnMapping) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Column Mapping</h3>
                <p class="text-base text-gray-600 dark:text-gray-400 mb-6">
                    Map your CSV columns to database fields. Auto-detected mappings are shown below.
                </p>
                
                <div class="space-y-4">
                    @foreach($columnMapping as $csvColumn => $dbColumn)
                        <div class="flex items-center space-x-6">
                            <div class="flex-1">
                                <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    CSV Column: {{ $csvColumn }}
                                </label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <x-heroicon-o-arrow-right class="w-6 h-6 text-gray-400" />
                            </div>
                            <div class="flex-1">
                                <select wire:model="columnMapping.{{ $csvColumn }}" 
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-base">
                                    <option value="">Ignore this column</option>
                                    @foreach($availableColumns as $key => $label)
                                        <option value="{{ $key }}" {{ $dbColumn === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Preview Data -->
        @if($showPreview)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Preview Data</h3>
                    <div class="text-base text-gray-600 dark:text-gray-400">
                        Showing first {{ count($previewData) }} rows
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Row
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Phone
                                </th>
                                @if($uploadType === 'family')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Relationship
                                    </th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($previewData as $row)
                                <tr class="{{ count($row['errors']) > 0 ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $row['row_number'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $row['mapped_data']['first_name'] ?? '' }} {{ $row['mapped_data']['last_name'] ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $row['mapped_data']['email'] ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $row['mapped_data']['phone'] ?? '' }}
                                    </td>
                                    @if($uploadType === 'family')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $row['mapped_data']['relationship'] ?? '' }}
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(count($row['errors']) > 0)
                                            <div class="text-red-600 dark:text-red-400">
                                                <x-heroicon-o-clock class="w-4 h-4 inline" />
                                                {{ count($row['errors']) }} errors
                                            </div>
                                            <div class="text-xs text-red-600 dark:text-red-400 mt-1">
                                                {{ implode(', ', array_slice($row['errors'], 0, 2)) }}
                                                @if(count($row['errors']) > 2)
                                                    ... and {{ count($row['errors']) - 2 }} more
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-green-600 dark:text-green-400">
                                                <x-heroicon-o-check-circle class="w-4 h-4 inline" />
                                                Valid
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Import Actions -->
        @if($showPreview)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Ready to Import</h3>
                        <p class="text-base text-gray-600 dark:text-gray-400 mt-2">
                            Review preview above, then confirm to import data.
                        </p>
                    </div>
                    <div class="flex space-x-6">
                        <button wire:click="cancelImport" 
                                class="px-8 py-4 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            Cancel
                        </button>
                        <button wire:click="confirmImport" 
                                wire:loading.attr="disabled"
                                class="px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:from-blue-400 disabled:to-blue-500 text-white font-semibold rounded-xl transition-all duration-200 flex items-center shadow-lg hover:shadow-xl">
                            <span wire:loading.remove>Import Members</span>
                            <span wire:loading>Importing...</span>
                            <x-heroicon-o-cloud-arrow-up class="w-4 h-4 ml-2" />
                            <x-heroicon-o-arrow-path class="animate-spin w-4 h-4 ml-2" />
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Import Results -->
        @if($importResults['success'] > 0 || $importResults['failed'] > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Import Results</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-6 shadow-inner">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-check-circle class="h-10 w-10 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-6">
                                <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                                    {{ $importResults['success'] }}
                                </div>
                                <div class="text-base text-green-800 dark:text-green-200 font-semibold">
                                    Successfully Imported
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 rounded-xl p-6 shadow-inner">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-x-circle class="h-10 w-10 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="ml-6">
                                <div class="text-3xl font-bold text-red-600 dark:text-red-400">
                                    {{ $importResults['failed'] }}
                                </div>
                                <div class="text-base text-red-800 dark:text-red-200 font-semibold">
                                    Failed to Import
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(count($importResults['errors']) > 0)
                    <div class="bg-gradient-to-br from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 rounded-xl p-6 shadow-inner">
                        <h4 class="text-lg font-bold text-red-800 dark:text-red-200 mb-4">Import Errors</h4>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($importResults['errors'] as $error)
                                <div class="text-base text-red-700 dark:text-red-300">{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
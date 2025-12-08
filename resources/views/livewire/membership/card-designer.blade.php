<div>
    <!-- Mode Toggle -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button
                        wire:click="toggleBatchMode"
                        class="px-4 py-2 rounded-md font-medium transition-colors
                            @if(!$batchMode)
                                bg-blue-600 text-white hover:bg-blue-700
                            @else
                                bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300
                            @endif">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Single Card
                    </button>
                    
                    <button
                        wire:click="toggleBatchMode"
                        class="px-4 py-2 rounded-md font-medium transition-colors
                            @if($batchMode)
                                bg-blue-600 text-white hover:bg-blue-700
                            @else
                                bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300
                            @endif">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Batch Printing
                    </button>
                </div>
                
                @if($memberCard)
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-{{ $memberCard->status_color }}-100 text-{{ $memberCard->status_color }}-800 dark:bg-{{ $memberCard->status_color }}-900 dark:text-{{ $memberCard->status_color }}-200">
                            {{ $memberCard->status_label }}
                        </span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Printed: {{ $memberCard->print_count }} times
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($batchMode)
        <!-- Batch Printing Mode -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Batch Card Generation
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Filter Options -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Member Status
                        </label>
                        <select wire:model="batchFilters.status" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="active">Active Members</option>
                            <option value="all">All Members</option>
                            <option value="expired">Expired Members</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Card Template
                        </label>
                        <select wire:model="batchFilters.template" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            @foreach($availableTemplates as $template => $info)
                                <option value="{{ $template }}">{{ $info['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Paper Size
                        </label>
                        <select wire:model="batchFilters.paper_size" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            @foreach($printingSettings['paper_sizes'] as $size => $label)
                                <option value="{{ $size }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Include Family Members
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="batchFilters.include_family" class="rounded border-gray-300 dark:border-gray-600">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include family members</span>
                        </label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Orientation
                        </label>
                        <select wire:model="batchFilters.orientation" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            @foreach($printingSettings['orientations'] as $orientation => $label)
                                <option value="{{ $orientation }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button
                        wire:click="generateBatchCards"
                        class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 font-medium">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Generate Batch Cards
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Recent Cards -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recently Generated Cards</h3>
                
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Member
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Card Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Printed
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Last Printed
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($memberCards as $card)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $card->member->full_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $card->card_type_label }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $card->status_color }}-100 text-{{ $card->status_color }}-800">
                                            {{ $card->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $card->print_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $card->last_printed_at?->diffForHumans() ?? 'Never' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{ $memberCards->links() }}
            </div>
        </div>
    @elseif(!$this->person)
        <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        No Person Selected
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        Please select a member or family member to design a card.
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Card Designer Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Card Designer
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400">
                            Design and generate membership cards for {{ $this->personName }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $this->personType === 'member' ? 'Member' : 'Family Member' }}
                        </span>
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ $this->barcodeNumber }}
                        </span>
                    </div>
                </div>

                <!-- Card Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Card Type
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($availableCardTypes as $type => $label)
                            <div class="relative">
                                <input
                                    type="radio"
                                    id="card-type-{{ $type }}"
                                    wire:model="cardType"
                                    value="{{ $type }}"
                                    class="sr-only"
                                />
                                <label for="card-type-{{ $type }}" 
                                       class="block p-3 border-2 rounded-lg cursor-pointer transition-colors text-center
                                            @if($cardType === $type)
                                                border-blue-500 bg-blue-50 dark:bg-blue-900/20
                                            @else
                                                border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600
                                            @endif">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $label }}
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Template Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Card Template
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($availableTemplates as $template => $info)
                            <div class="relative">
                                <input
                                    type="radio"
                                    id="template-{{ $template }}"
                                    wire:model="cardTemplate"
                                    value="{{ $template }}"
                                    class="sr-only"
                                />
                                <label for="template-{{ $template }}" 
                                       class="block p-4 border-2 rounded-lg cursor-pointer transition-colors
                                            @if($cardTemplate === $template)
                                                border-blue-500 bg-blue-50 dark:bg-blue-900/20
                                            @else
                                                border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600
                                            @endif">
                                    <div class="text-center">
                                        <div class="w-full h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded mb-2 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                            </svg>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $info['name'] }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $info['description'] }}
                                        </div>
                                        @if(isset($info['features']))
                                            <div class="flex flex-wrap gap-1 mt-2 justify-center">
                                                @foreach(array_slice($info['features'], 0, 3) as $feature)
                                                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1 py-0.5 rounded">
                                                        {{ $feature }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Design Settings -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Design Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Colors -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Primary Color
                            </label>
                            <div class="flex items-center space-x-2">
                                <input
                                    type="color"
                                    wire:model.live="design_settings.primary_color"
                                    class="h-10 w-20 rounded border-gray-300 dark:border-gray-600"
                                />
                                <input
                                    type="text"
                                    wire:model.live="design_settings.primary_color"
                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                                />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Secondary Color
                            </label>
                            <div class="flex items-center space-x-2">
                                <input
                                    type="color"
                                    wire:model.live="design_settings.secondary_color"
                                    class="h-10 w-20 rounded border-gray-300 dark:border-gray-600"
                                />
                                <input
                                    type="text"
                                    wire:model.live="design_settings.secondary_color"
                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                                />
                            </div>
                        </div>
                        
                        <!-- Font -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Font Family
                            </label>
                            <select wire:model.live="design_settings.font_family" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <option value="Arial">Arial</option>
                                <option value="Helvetica">Helvetica</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Inter">Inter</option>
                            </select>
                        </div>
                        
                        <!-- Layout -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Layout
                            </label>
                            <select wire:model.live="design_settings.layout" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <option value="horizontal">Horizontal</option>
                                <option value="vertical">Vertical</option>
                            </select>
                        </div>
                        
                        <!-- Toggle Options -->
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="design_settings.show_photo" class="rounded border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show Photo</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="design_settings.show_qr_code" class="rounded border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show QR Code</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="design_settings.show_barcode" class="rounded border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show Barcode</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="design_settings.show_expiry" class="rounded border-gray-300 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show Expiry Date</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6">
                <div class="flex flex-wrap gap-3">
                    <button
                        wire:click="generatePreview"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Preview Card
                    </button>

                    @if(!$memberCard)
                        <button
                            wire:click="saveCard"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"></path>
                            </svg>
                            Save Card Design
                        </button>
                    @endif

                    @if($previewMode)
                        <button
                            wire:click="printCard"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Card
                        </button>

                        <button
                            wire:click="downloadCard"
                            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download PDF
                        </button>

                        @if($memberCard)
                            <button
                                wire:click="reprintCard"
                                class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500"
                            >
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reprint Card
                            </button>
                        @endif

                        <button
                            wire:click="resetPreview"
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset Preview
                        </button>
                    @endif
                </div>

                <!-- Card Status Information -->
                @if($memberCard)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Card Number:</span>
                                <span class="ml-2 text-gray-900 dark:text-white">{{ $memberCard->card_number }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Issue Date:</span>
                                <span class="ml-2 text-gray-900 dark:text-white">{{ $memberCard->issue_date?->format('M d, Y') ?? 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Expiry Date:</span>
                                <span class="ml-2 text-gray-900 dark:text-white">{{ $memberCard->expiry_date?->format('M d, Y') ?? 'Never' }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Preview Area -->
        @if($previewMode)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Card Preview</h3>
                    
                    <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                        <div class="transform scale-75 origin-top-left">
                            {!! $previewHtml !!}
                        </div>
                    </div>

                    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        <p>This is a preview of the card. The actual printed card will be full size and high quality.</p>
                    </div>
                </div>
            </div>
        @else
            <!-- Instructions -->
            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            How to Design Your Card
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Select a card template from the options above</li>
                                <li>Click "Preview Card" to see how it will look</li>
                                <li>Use "Print Card" to generate a printable version</li>
                                <li>Or use "Download PDF" to save the card to your device</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Card Statistics -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Cards Generated</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $cardStatistics['total_cards_generated'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Members with Photos</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $cardStatistics['members_with_photos'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Cards Today</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $cardStatistics['cards_today'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

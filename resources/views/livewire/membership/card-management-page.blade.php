<div>
    <!-- Card Preview Modal -->
    @if($showPreview && !empty($cardPreview))
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold mb-1">Member Card Preview</h2>
                            <p class="text-blue-100">Manage card status and generate PDF</p>
                        </div>
                        <button wire:click="closePreview" class="text-white hover:text-blue-200">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Card Preview -->
                <div class="p-6">
                    <div class="max-w-md mx-auto">
                        <!-- Credit Card Style Preview -->
                        <div class="relative bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl p-6 shadow-2xl text-white">
                            <!-- Card Header -->
                            <div class="flex justify-between items-start mb-8">
                                <div>
                                    <p class="text-xs uppercase tracking-wider opacity-80">Country Club</p>
                                    <p class="text-lg font-bold">{{ $getCardTypeLabel() }}</p>
                                </div>
                                @if($cardPreview['qr_code_path'])
                                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                        <x-heroicon-o-squares-2x2 class="w-8 h-8" />
                                    </div>
                                @endif
                            </div>

                            <!-- Card Number -->
                            <div class="mb-6">
                                <p class="text-xl font-mono tracking-wider">{{ $cardPreview['card_number'] }}</p>
                            </div>

                            <!-- Card Details -->
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-xs uppercase tracking-wider opacity-80 mb-1">Member Name</p>
                                    <p class="text-lg font-semibold">{{ $cardPreview['member_name'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs uppercase tracking-wider opacity-80 mb-1">Valid Thru</p>
                                    <p class="text-sm font-mono">{{ $cardPreview['expiry_date'] ?? '12/99' }}</p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="absolute top-4 right-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    @if($cardPreview['status'] === 'active') bg-green-500 text-white
                                    @elseif($cardPreview['status'] === 'inactive') bg-gray-500 text-white
                                    @elseif($cardPreview['status'] === 'lost') bg-red-500 text-white
                                    @elseif($cardPreview['status'] === 'damaged') bg-yellow-500 text-white
                                    @else bg-gray-500 text-white
                                    @endif">
                                    {{ ucfirst($cardPreview['status']) }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Information -->
                        <div class="mt-6 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400">Member ID:</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $cardPreview['member_id'] }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400">Card Type:</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $getCardTypeLabel() }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400">Issue Date:</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $cardPreview['issue_date'] ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400">Print Count:</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $cardPreview['print_count'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Management Actions -->
                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Card Management</h3>
                    
                    <!-- Status Actions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                        @if($cardPreview['status'] !== 'active')
                            <button wire:click="activateCard" class="flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <x-heroicon-o-check-circle class="w-5 h-5 mr-2" />
                                Activate Card
                            </button>
                        @endif

                        @if($cardPreview['status'] !== 'inactive')
                            <button wire:click="deactivateCard" class="flex items-center justify-center px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <x-heroicon-o-x-mark class="w-5 h-5 mr-2" />
                                Deactivate
                            </button>
                        @endif

                        <button wire:click="markAsLost" class="flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-2" />
                            Mark as Lost
                        </button>

                        <button wire:click="markAsDamaged" class="flex items-center justify-center px-4 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-2" />
                            Mark Damaged
                        </button>
                    </div>

                    <!-- Status Note -->
                    <div class="mb-6">
                        <label for="statusNote" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Change Note</label>
                        <textarea
                            wire:model="statusNote"
                            id="statusNote"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Enter reason for status change..."
                        ></textarea>
                    </div>

                    <!-- Export Actions -->
                    <div class="flex flex-wrap gap-3">
                        <button wire:click="generatePDF" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                            <x-heroicon-o-arrow-down-tray class="w-5 h-5 inline mr-2" />
                            Generate PDF
                        </button>
                        
                        <button class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 font-medium">
                            <x-heroicon-o-arrow-up-tray class="w-5 h-5 inline mr-2" />
                            Download Card
                        </button>
                        
                        <button class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 font-medium">
                            <x-heroicon-o-arrows-right-left class="w-5 h-5 inline mr-2" />
                            Email Card
                        </button>
                    </div>

                    <!-- Card History -->
                    @if($cardPreview['last_printed'])
                        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Last printed:</strong> {{ $cardPreview['last_printed'] }} 
                                <span class="ml-2">• Printed {{ $cardPreview['print_count'] }} times</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Demo Button to Show Card Preview -->
    @if(!$showPreview)
        <div class="p-6 text-center">
            <button wire:click="loadCardPreview(1)" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                <x-heroicon-o-credit-card class="w-5 h-5 inline mr-2" />
                View Sample Card Management
            </button>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Click to see card preview with activation/deactivation options and PDF export</p>
        </div>
    @endif

    <!-- Notification Script -->
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('show-notification', function (event) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                    event.type === 'success' ? 'bg-green-500 text-white' : 
                    event.type === 'warning' ? 'bg-yellow-500 text-white' : 
                    event.type === 'error' ? 'bg-red-500 text-white' : 'bg-gray-500 text-white'
                }`;
                notification.textContent = event.message;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 3000);
            });
        });
    </script>
</div>
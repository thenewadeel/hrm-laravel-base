<div class="p-6">
    <!-- Scanner Header -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Member Card Scanner</h2>
        <p class="text-gray-600 dark:text-gray-400">Scan member cards or enter member ID to check access</p>
    </div>

    <!-- Scanner Input -->
    <div class="mb-8">
        <div class="max-w-md mx-auto">
            <label for="scan-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Scan Card or Enter Member ID
            </label>
            <div class="relative">
                <input
                    wire:model.live="scanInput"
                    wire:keydown.enter="scan"
                    type="text"
                    id="scan-input"
                    placeholder="Enter card number or member ID..."
                    class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white text-lg"
                    wire:loading.attr="disabled"
                >
                <button
                    wire:click="scan"
                    wire:loading.attr="disabled"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                    <span wire:loading.remove>Scan</span>
                    <span wire:loading>Scanning...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Scanner Status -->
    <div class="mb-8">
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-blue-800 dark:text-blue-200 text-sm">
                    Ready to scan. Enter a card number or member ID and press Enter or click Scan.
                </span>
            </div>
        </div>
    </div>

    <!-- Recent Scans -->
    @if(!empty($recentScans))
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Scans</h3>
            <div class="space-y-3">
                @foreach($recentScans as $scan)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            @if($scan['success'] ?? false)
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $scan['name'] }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $scan['member_id'] }} • {{ $scan['time'] }}</p>
                            </div>
                        </div>
                        <div>
                            @if($scan['success'] ?? false)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Access Granted
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Not Found
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Quick Stats -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
            <div class="flex items-center">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg mr-3">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Members</p>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">1,234</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg mr-3">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Active Today</p>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">89</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg mr-3">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Avg. Check-in</p>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">2:34 PM</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Listeners -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('scan-success', (e) => {
            // Show success notification
            console.log('Member scanned:', e.member);
        });
        
        Livewire.on('scan-error', () => {
            // Show error notification
            console.log('Scan failed - member not found');
        });
    });
</script>

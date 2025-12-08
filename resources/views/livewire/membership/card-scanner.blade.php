<div>
    <!-- Sound Effects -->
    <audio id="success-sound" preload="auto">
        <source src="{{ asset('sounds/success.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="warning-sound" preload="auto">
        <source src="{{ asset('sounds/warning.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="error-sound" preload="auto">
        <source src="{{ asset('sounds/error.mp3') }}" type="audio/mpeg">
    </audio>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:to-gray-800">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-xl border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10 py-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-blue-600 rounded-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Membership Access Scanner</h1>
                            <p class="text-gray-600 dark:text-gray-400">Scan member cards for facility access</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Scan Type Toggle -->
                        <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                            <button wire:click="$set('scanType', 'member')" 
                                    class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $scanType === 'member' ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow' : 'text-gray-600 dark:text-gray-400' }}">
                                Member
                            </button>
                            <button wire:click="$set('scanType', 'family')" 
                                    class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $scanType === 'family' ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow' : 'text-gray-600 dark:text-gray-400' }}">
                                Family
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10 py-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Scanner Section -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Scanner Interface -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-10">
                        <div class="text-center mb-10">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800 rounded-full mb-6 shadow-lg">
                                <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">Scan Member Card</h2>
                            <p class="text-lg text-gray-600 dark:text-gray-400">Enter barcode number or scan with barcode reader</p>
                        </div>

                        <!-- Barcode Input -->
                        <div class="space-y-6">
                            <div>
                                <label for="barcode" class="block text-lg font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                    Barcode Number
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           id="barcode"
                                           wire:model.live="scannedBarcode"
                                           class="w-full px-6 py-4 text-xl border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white shadow-sm"
                                           placeholder="Enter or scan barcode..."
                                           autofocus>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('scannedBarcode')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-6">
                                <button wire:click="scanBarcode" 
                                        class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-8 rounded-xl transition-all duration-200 flex items-center justify-center space-x-3 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Scan Card</span>
                                </button>
                                <button wire:click="clearScan" 
                                        class="px-8 py-4 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 shadow-md hover:shadow-lg">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- Demo Barcodes -->
                        <div class="mt-8 p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl shadow-inner">
                            <p class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-4">Demo Barcodes:</p>
                            <div class="flex flex-wrap gap-3">
                                @foreach($this->demoBarcodes as $demo)
                                    <button wire:click="simulateScan('{{ $demo['barcode'] }}')" 
                                            class="px-4 py-2 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 text-sm font-medium rounded-lg hover:bg-blue-100 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="{{ $demo['type'] }}">
                                        {{ $demo['barcode'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Scan Result -->
                    @if($showResult)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-10">
                            @if($accessGranted)
                                <!-- Success Result -->
                                <div class="text-center">
                                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 rounded-full mb-6 shadow-lg">
                                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-3xl font-bold text-green-600 dark:text-green-400 mb-3">Access Granted</h3>
                                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">{{ $accessStatus }}</p>
                                </div>
                            @else
                                <!-- Denied Result -->
                                <div class="text-center">
                                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900 dark:to-red-800 rounded-full mb-6 shadow-lg">
                                        <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-3xl font-bold text-red-600 dark:text-red-400 mb-3">Access Denied</h3>
                                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">{{ $accessStatus }}</p>
                                </div>
                            @endif

                            @if($errorMessage)
                                <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border border-red-200 dark:border-red-800 rounded-xl p-6 mb-8 shadow-inner">
                                    <p class="text-red-600 dark:text-red-400 font-medium">{{ $errorMessage }}</p>
                                </div>
                            @endif

                            <!-- Member Information -->
                            @if($scannedMember)
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Member Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <!-- Member Details -->
                                        <div class="space-y-3">
                                            <div class="flex items-center space-x-3">
                                                @if($scannedMember->photo_path)
                                                    <img src="{{ Storage::url($scannedMember->photo_path) }}" 
                                                         alt="{{ $scannedMember->full_name }}" 
                                                         class="w-12 h-12 rounded-full object-cover">
                                                @else
                                                    <div class="w-12 h-12 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">{{ $scannedMember->full_name }}</p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $scannedMember->membership_number }}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                                    <span class="font-medium {{ $scannedMember->status === 'active' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                        {{ ucfirst($scannedMember->status) }}
                                                    </span>
                                                </div>
                                                @if($scannedMember->currentSubscription)
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600 dark:text-gray-400">Subscription:</span>
                                                        <span class="font-medium text-gray-900 dark:text-white">
                                                            {{ $scannedMember->currentSubscription->subscriptionPlan->name ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600 dark:text-gray-400">Expires:</span>
                                                        <span class="font-medium text-gray-900 dark:text-white">
                                                            {{ $scannedMember->currentSubscription->end_date->format('M d, Y') }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Family Member Info (if applicable) -->
                                        @if($scannedFamilyMember)
                                            <div class="space-y-3">
                                                <h5 class="font-medium text-gray-900 dark:text-white">Family Member</h5>
                                                <div class="flex items-center space-x-3">
                                                    @if($scannedFamilyMember->photo_path)
                                                        <img src="{{ Storage::url($scannedFamilyMember->photo_path) }}" 
                                                             alt="{{ $scannedFamilyMember->full_name }}" 
                                                             class="w-12 h-12 rounded-full object-cover">
                                                    @else
                                                        <div class="w-12 h-12 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">{{ $scannedFamilyMember->full_name }}</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $scannedFamilyMember->relationship }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Recent Scans -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Recent Scans</h3>
                        @if($recentScans->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentScans as $scan)
                                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-2 h-2 rounded-full {{ $scan['access_granted'] ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $scan['barcode_number'] }}</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($scan['created_at'] ?? 'now')->format('H:i:s') }}</p>
                                            </div>
                                        </div>
                                        @if($scan['access_granted'])
                                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No recent scans</p>
                        @endif
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Today's Stats</h3>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total Scans</span>
                                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $recentScans->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Access Granted</span>
                                <span class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ $recentScans->where('access_granted', true)->count() }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Access Denied</span>
                                <span class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    {{ $recentScans->where('access_granted', false)->count() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-8 shadow-inner">
                        <h3 class="text-xl font-bold text-blue-900 dark:text-blue-100 mb-6">Instructions</h3>
                        <ul class="space-y-4 text-base text-blue-800 dark:text-blue-200">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Enter barcode manually or scan with reader
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                System validates membership status
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                        Green = Access Granted
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Red = Access Denied
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for sound effects and auto-focus -->
    <script>
        document.addEventListener('livewire:init', function () {
            // Handle sound events
            Livewire.on('play-sound', function (data) {
                const audio = document.getElementById(data.sound + '-sound');
                if (audio) {
                    audio.play().catch(e => console.log('Audio play failed:', e));
                }
            });

            // Auto-focus barcode input
            const barcodeInput = document.getElementById('barcode');
            if (barcodeInput) {
                barcodeInput.focus();
                
                // Re-focus on click outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('button') && !e.target.closest('input')) {
                        barcodeInput.focus();
                    }
                });
            }

            // Handle Enter key for scanning
            barcodeInput?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    @this.scanBarcode();
                }
            });
        });
    </script>
</div>
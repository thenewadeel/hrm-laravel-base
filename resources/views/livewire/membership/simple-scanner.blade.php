<div class="p-6">
    <!-- Scanner Header -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Member Card Scanner</h2>
        <p class="text-gray-600 dark:text-gray-400">Scan member cards or enter member ID to check access</p>
    </div>

    <!-- Enhanced Scanner Input -->
    <div class="mb-8">
        <div class="max-w-2xl mx-auto">
            <!-- Scanner Header with Animation -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Member Access Scanner</h2>
                <p class="text-gray-600 dark:text-gray-400">Scan member cards or enter member ID to verify access</p>
            </div>

            <!-- Enhanced Input Field -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <input
                    wire:model.live="scanInput"
                    wire:keydown.enter="scan"
                    type="text"
                    id="scan-input"
                    placeholder="Enter card number or member ID..."
                    class="block w-full pl-12 pr-32 py-4 text-lg border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                    wire:loading.attr="disabled"
                >
                <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                    <button
                        wire:click="scan"
                        wire:loading.attr="disabled"
                        class="relative px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 disabled:opacity-50 transition-all duration-200 font-medium"
                    >
                        <span wire:loading.remove class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Scan
                        </span>
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Scanning...
                        </span>
                    </button>
                </div>
            </div>
            
            <!-- Enhanced Demo Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                <button
                    wire:click="scanDemo('valid')"
                    class="group relative px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-4 focus:ring-green-500/20 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Test Valid Member
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </span>
                </button>
                <button
                    wire:click="scanDemo('invalid')"
                    class="group relative px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl hover:from-red-600 hover:to-pink-700 focus:outline-none focus:ring-4 focus:ring-red-500/20 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Test Invalid Entry
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Enhanced Member Profile Modal -->
    @if($showProfile && $currentMember)
        <div class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4" wire:click.self="closeProfile">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-6xl w-full max-h-[95vh] overflow-hidden transform transition-all duration-300 ease-out" wire:loading.delay.100ms.class="scale-95 opacity-0">
                
                <!-- Enhanced Profile Header with Gradient -->
                <div class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 p-8 text-white">
                    <!-- Decorative pattern overlay -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full transform translate-x-32 -translate-y-32"></div>
                        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full transform -translate-x-24 translate-y-24"></div>
                    </div>
                    
                    <div class="relative flex justify-between items-start">
                        <div class="flex items-center space-x-6">
                            <!-- Large Profile Photo -->
                            <div class="relative">
                                @if($currentMember['photo_path'])
                                    <img src="{{ Storage::url($currentMember['photo_path']) }}" 
                                         alt="{{ $currentMember['name'] }}" 
                                         class="w-32 h-32 rounded-2xl border-4 border-white shadow-2xl object-cover">
                                @else
                                    <div class="w-32 h-32 rounded-2xl border-4 border-white shadow-2xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <!-- Status indicator -->
                                <div class="absolute -bottom-2 -right-2 w-8 h-8 {{ $currentMember['is_active'] ? 'bg-green-500' : 'bg-red-500' }} rounded-full border-4 border-white flex items-center justify-center">
                                    @if($currentMember['is_active'])
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Member Info -->
                            <div>
                                <h2 class="text-3xl font-bold mb-2">{{ $currentMember['name'] }}</h2>
                                <div class="flex items-center space-x-4 text-indigo-100 mb-3">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                        </svg>
                                        {{ $currentMember['member_id'] }}
                                    </span>
                                    @if($currentMember['age'])
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $currentMember['age'] }} years
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium {{ $currentMember['is_active'] ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                        {{ $currentMember['status'] }}
                                    </span>
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-white/20 backdrop-blur-sm text-white">
                                        {{ $currentMember['membership_type'] ?? 'Premium' }}
                                    </span>
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-white/20 backdrop-blur-sm text-white">
                                        {{ $currentMember['access_level'] ?? 'Full Access' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <button wire:click="closeProfile" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Enhanced Profile Content -->
                <div class="p-8 bg-gradient-to-b from-gray-50 to-white dark:from-gray-800 dark:to-gray-900">
                    <!-- Quick Actions Bar -->
                    <div class="mb-8 flex flex-wrap gap-3">
                        <button wire:click="checkInMember" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Check In Member
                        </button>
                        <button wire:click="addNote" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Add Note
                        </button>
                        <button wire:click="viewFullProfile" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Full Profile
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Member Details (2 columns) -->
                        <div class="lg:col-span-2 space-y-8">
                            <!-- Contact Information -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Contact Information
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Address</label>
                                            <p class="text-gray-900 dark:text-white font-medium">{{ $currentMember['email'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</label>
                                            <p class="text-gray-900 dark:text-white font-medium">{{ $currentMember['phone'] ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Member Since</label>
                                            <p class="text-gray-900 dark:text-white font-medium">{{ $currentMember['join_date'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Membership Expires</label>
                                            <p class="text-gray-900 dark:text-white font-medium {{ $currentMember['is_expiring_soon'] ? 'text-yellow-600 dark:text-yellow-400' : '' }}">
                                                {{ $currentMember['expiry_date'] ?? 'N/A' }}
                                                @if($currentMember['is_expiring_soon'])
                                                    <span class="text-xs ml-2">⚠️ Expires Soon</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Family Members Section -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Family Members ({{ count($currentMember['family_members'] ?? []) }})
                                </h3>
                                @if(!empty($currentMember['family_members']))
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($currentMember['family_members'] as $familyMember)
                                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-lg transition-shadow bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800">
                                                <div class="flex items-center space-x-4">
                                                    <!-- Family Member Photo -->
                                                    <div class="relative">
                                                        @if($familyMember['photo_path'])
                                                            <img src="{{ Storage::url($familyMember['photo_path']) }}" 
                                                                 alt="{{ $familyMember['name'] }}" 
                                                                 class="w-16 h-16 rounded-xl object-cover">
                                                        @else
                                                            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-400 flex items-center justify-center">
                                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                        <!-- Family member status indicator -->
                                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 {{ $familyMember['is_active'] ? 'bg-green-500' : 'bg-red-500' }} rounded-full border-2 border-white"></div>
                                                    </div>
                                                    
                                                    <div class="flex-1">
                                                        <h4 class="font-semibold text-gray-900 dark:text-white text-lg">{{ $familyMember['name'] }}</h4>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $familyMember['relationship_color'] ?? 'bg-gray-100 text-gray-800' }}">
                                                                {{ $familyMember['relationship'] }}
                                                            </span>
                                                            @if($familyMember['age'])
                                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $familyMember['age'] }} yrs</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400">No family members registered</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Recent Activity -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Recent Activity
                                </h3>
                                @if(!empty($currentMember['recent_activity'] ?? []))
                                    <div class="space-y-4">
                                        @foreach($currentMember['recent_activity'] as $activity)
                                            <div class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                <div class="w-10 h-10 {{ $activity['type'] === 'check_in' ? 'bg-green-100' : ($activity['type'] === 'payment' ? 'bg-blue-100' : 'bg-purple-100') }} rounded-full flex items-center justify-center">
                                                    @if($activity['type'] === 'check_in')
                                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    @elseif($activity['type'] === 'payment')
                                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-gray-900 dark:text-white font-medium">{{ $activity['description'] }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $activity['date'] }} at {{ $activity['time'] }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 dark:text-gray-400">No recent activity</p>
                                @endif
                            </div>
                        </div>

                        <!-- Sidebar Stats -->
                        <div class="space-y-6">
                            <!-- Access Status Card -->
                            <div class="{{ $currentMember['is_active'] ? 'bg-gradient-to-br from-green-500 to-green-600' : 'bg-gradient-to-br from-red-500 to-red-600' }} rounded-xl shadow-lg p-6 text-white">
                                <div class="flex items-center mb-4">
                                    @if($currentMember['is_active'])
                                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                    <h3 class="text-lg font-bold">Access Status</h3>
                                </div>
                                <p class="text-2xl font-bold mb-2">
                                    {{ $currentMember['is_active'] ? 'GRANTED' : 'DENIED' }}
                                </p>
                                <p class="text-green-100">
                                    {{ $currentMember['is_active'] ? 'Member has full access to all facilities' : 'Member access is restricted' }}
                                </p>
                            </div>

                            <!-- Quick Stats -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quick Stats</h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">Total Check-ins</span>
                                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $currentMember['check_in_count'] ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">Last Visit</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $currentMember['last_visit'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">Family Size</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ 1 + count($currentMember['family_members'] ?? []) }} members</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Membership Benefits -->
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200 dark:border-indigo-800">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Benefits</h3>
                                <ul class="space-y-2">
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Full facility access
                                    </li>
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Guest privileges ({{ rand(2, 5) }} per month)
                                    </li>
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Priority bookings
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Enhanced Scanner Status -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100">Scanner Ready</h3>
                    <p class="text-blue-700 dark:text-blue-300 text-sm">
                        Enter a card number or member ID and press Enter or click Scan. Use demo buttons for testing functionality.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Recent Scans -->
    @if(!empty($recentScans))
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Recent Scans</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">Last {{ count($recentScans) }} activities</span>
            </div>
            <div class="space-y-3">
                @foreach($recentScans as $index => $scan)
                    <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden border border-gray-200 dark:border-gray-700">
                        <!-- Success indicator bar -->
                        <div class="absolute top-0 left-0 w-1 h-full {{ $scan['success'] ?? false ? 'bg-green-500' : 'bg-red-500' }}"></div>
                        
                        <div class="flex items-center justify-between p-4">
                            <div class="flex items-center flex-1">
                                <div class="w-12 h-12 {{ $scan['success'] ?? false ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-full flex items-center justify-center mr-4">
                                    @if($scan['success'] ?? false)
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 dark:text-white text-lg">{{ $scan['name'] }}</p>
                                    <div class="flex items-center space-x-3 mt-1">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $scan['member_id'] }}</span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $scan['time'] }}</span>
                                        @if($index === 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                                Most Recent
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="ml-4">
                                @if($scan['success'] ?? false)
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Access Granted
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Not Found
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Enhanced Quick Stats Dashboard -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="group bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 p-6 rounded-xl border border-indigo-200 dark:border-indigo-800 hover:shadow-lg transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 mb-1">Total Members</p>
                    <p class="text-3xl font-bold text-indigo-900 dark:text-indigo-100">1,234</p>
                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-2">+12% from last month</p>
                </div>
                <div class="p-3 bg-indigo-500 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="group bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-6 rounded-xl border border-green-200 dark:border-green-800 hover:shadow-lg transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600 dark:text-green-400 mb-1">Active Today</p>
                    <p class="text-3xl font-bold text-green-900 dark:text-green-100">89</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-2">Peak time: 2-4 PM</p>
                </div>
                <div class="p-3 bg-green-500 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="group bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-6 rounded-xl border border-purple-200 dark:border-purple-800 hover:shadow-lg transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-600 dark:text-purple-400 mb-1">Avg. Check-in</p>
                    <p class="text-3xl font-bold text-purple-900 dark:text-purple-100">2:34 PM</p>
                    <p class="text-xs text-purple-600 dark:text-purple-400 mt-2">Most busy hour</p>
                </div>
                <div class="p-3 bg-purple-500 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Event Listeners with Animations -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('scan-success', (e) => {
            // Show success notification with animation
            showNotification('success', `✅ ${e.member} checked in successfully!`);
            console.log('Member scanned:', e.member);
        });
        
        Livewire.on('scan-error', () => {
            // Show error notification with animation
            showNotification('error', '❌ Member not found or access denied');
            console.log('Scan failed - member not found');
        });

        Livewire.on('member-checked-in', (e) => {
            showNotification('success', `✅ ${e.member} has been checked in`);
        });

        Livewire.on('add-note-modal', (e) => {
            showNotification('info', `📝 Opening note editor for member ID: ${e.memberId}`);
        });

        Livewire.on('view-full-profile', (e) => {
            showNotification('info', `👤 Opening full profile for member ID: ${e.memberId}`);
        });
    });

    function showNotification(type, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform translate-x-full transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-blue-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
            notification.classList.add('translate-x-0');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + K to focus scan input
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('scan-input')?.focus();
        }
        
        // Escape to close profile modal
        if (e.key === 'Escape') {
            // Trigger Livewire closeProfile if modal is open
            const profileModal = document.querySelector('[wire\\:click\\.self="closeProfile"]');
            if (profileModal && profileModal.style.display !== 'none') {
                @this.call('closeProfile');
            }
        }
    });

    // Add scan input focus animation
    const scanInput = document.getElementById('scan-input');
    if (scanInput) {
        scanInput.addEventListener('focus', () => {
            scanInput.parentElement.classList.add('ring-4', 'ring-indigo-500/20');
        });
        
        scanInput.addEventListener('blur', () => {
            scanInput.parentElement.classList.remove('ring-4', 'ring-indigo-500/20');
        });
    }
</script>

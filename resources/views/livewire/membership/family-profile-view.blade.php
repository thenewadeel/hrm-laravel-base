<div>
    <!-- Family Profile Modal -->
    @if($showProfile && $member)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-6 text-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold mb-1">Family Profile</h2>
                            <p class="text-purple-100">Complete family membership overview</p>
                        </div>
                        <button wire:click="closeProfile" class="text-white hover:text-purple-200">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Master Member Section -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-heroicon-o-user-circle class="w-5 h-5 mr-2 text-indigo-600" />
                        Master Member
                    </h3>
                    
                    <div class="flex items-start space-x-6">
                        <!-- Master Member Photo -->
                        <div class="flex-shrink-0">
                            @if($member->photo_path)
                                <img src="{{ Storage::url($member->photo_path) }}" 
                                     alt="{{ $member->full_name }}" 
                                     class="w-32 h-32 rounded-2xl border-4 border-indigo-100 shadow-lg object-cover">
                            @else
                                <div class="w-32 h-32 rounded-2xl border-4 border-indigo-100 shadow-lg bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center">
                                    <span class="text-white text-3xl font-bold">{{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Master Member Details -->
                        <div class="flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $member->full_name }}</h4>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $member->title }} • {{ $this->getMemberAgeText() }}</p>
                                    <div class="flex items-center mt-2 space-x-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $this->getMembershipStatus()['color'] }}-100 text-{{ $this->getMembershipStatus()['color'] }}-800 dark:bg-{{ $this->getMembershipStatus()['color'] }}-900 dark:text-{{ $this->getMembershipStatus()['color'] }}-200">
                                            {{ ucfirst($this->getMembershipStatus()['status']) }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $member->membership_number }}</span>
                                    </div>
                                </div>
                                
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Email:</span>
                                        <span class="text-gray-900 dark:text-white">{{ $member->email ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Phone:</span>
                                        <span class="text-gray-900 dark:text-white">{{ $member->phone ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Join Date:</span>
                                        <span class="text-gray-900 dark:text-white">{{ $member->join_date?->format('M d, Y') ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Expiry:</span>
                                        <span class="text-gray-900 dark:text-white">{{ $member->expiry_date?->format('M d, Y') ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Family Members Section -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <x-heroicon-o-user-group class="w-5 h-5 mr-2 text-purple-600" />
                            Family Members ({{ count($familyMembers) }})
                        </h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $member->familyMembers->count() }} members included in membership
                        </span>
                    </div>

                    @if(!empty($familyMembers))
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($familyMembers as $familyMember)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                    <div class="flex items-start space-x-3">
                                        <!-- Family Member Photo -->
                                        <div class="flex-shrink-0">
                                            @if($familyMember['photo_path'])
                                                <img src="{{ Storage::url($familyMember['photo_path']) }}" 
                                                     alt="{{ $familyMember['name'] }}" 
                                                     class="w-16 h-16 rounded-full border-2 border-purple-200 object-cover">
                                            @else
                                                <div class="w-16 h-16 rounded-full border-2 border-purple-200 bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                                    <span class="text-white text-lg font-bold">{{ substr($familyMember['name'], 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Family Member Details -->
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-gray-900 dark:text-white truncate">{{ $familyMember['name'] }}</h4>
                                            <p class="text-sm text-purple-600 dark:text-purple-400 font-medium">{{ $familyMember['relationship'] }}</p>
                                            
                                            <div class="mt-2 space-y-1 text-xs">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-500 dark:text-gray-400">Age:</span>
                                                    <span class="text-gray-900 dark:text-white">{{ $familyMember['age'] ?? 'N/A' }} years</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-500 dark:text-gray-400">Gender:</span>
                                                    <span class="text-gray-900 dark:text-white">{{ ucfirst($familyMember['gender'] ?? 'N/A') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        {{ ucfirst($familyMember['status']) }}
                                                    </span>
                                                </div>
                                                @if($familyMember['barcode_number'])
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-500 dark:text-gray-400">Card:</span>
                                                        <span class="text-gray-900 dark:text-white font-mono text-xs">{{ $familyMember['barcode_number'] }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-user-group class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500 dark:text-gray-400">No family members registered</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Add family members to this membership</p>
                        </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="p-6 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap gap-3">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                            <x-heroicon-o-plus class="w-4 h-4 inline mr-2" />
                            Add Family Member
                        </button>
                        <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                            <x-heroicon-o-bell class="w-4 h-4 inline mr-2" />
                            Send Notifications
                        </button>
                        <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                            <x-heroicon-o-document-text class="w-4 h-4 inline mr-2" />
                            View Details
                        </button>
                        <button class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm">
                            <x-heroicon-o-arrow-up-tray class="w-4 h-4 inline mr-2" />
                            Export Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Demo Button to Show Family Profile -->
    @if(!$showProfile)
        <div class="p-6 text-center">
            <button wire:click="loadFamilyProfile(1)" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-purple-500 font-medium">
                <x-heroicon-o-user-group class="w-5 h-5 inline mr-2" />
                View Sample Family Profile
            </button>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Click to see a sample family profile with master member and dependents</p>
        </div>
    @endif
</div>
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-8">
    <div class="max-w-6xl mx-auto px-6">
        <!-- Progress Indicator -->
        <div class="mb-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $editMode ? 'Edit Member' : 'New Member Registration' }}
                </h2>
            <div class="flex items-center space-x-2">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">1</div>
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Personal</span>
                </div>
                <div class="w-8 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded-full flex items-center justify-center text-sm font-medium">2</div>
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Family</span>
                </div>
                <div class="w-8 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded-full flex items-center justify-center text-sm font-medium">3</div>
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Review</span>
                </div>
            </div>
        </div>
    </div>

        <form wire:submit="save" class="space-y-10">
            <!-- Personal Information Section -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 px-8 py-6">
                <h4 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Personal Information
                </h4>
            </div>
            
            <div class="p-8">
                <!-- Photo Upload Section -->
                <div class="mb-8 flex items-center space-x-8">
                    <div class="flex-shrink-0">
                        @if($editMode && $member && $member->photo_path)
                            <img src="{{ Storage::url($member->photo_path) }}" 
                                 alt="{{ $member->full_name }}" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-blue-100 dark:border-blue-900 shadow-lg">
                        @else
                            <div class="w-32 h-32 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 rounded-full flex items-center justify-center border-4 border-gray-300 dark:border-gray-600 shadow-lg">
                                <svg class="w-16 h-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block text-lg font-semibold text-gray-700 dark:text-gray-300 mb-3">
                            Member Photo
                        </label>
                        <input type="file" wire:model="photo" accept="image/*" class="block w-full text-base text-gray-500 dark:text-gray-400
                            file:mr-6 file:py-3 file:px-6
                            file:rounded-full file:border-0
                            file:text-base file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                            dark:file:bg-blue-900 dark:file:text-blue-300">
                        @error('photo')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Recommended: Square image, at least 300x300px</p>
                    </div>
                </div>

                <!-- Basic Information Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Title
                        </label>
                        <select
                            id="title"
                            wire:model="title"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                        >
                            <option value="">Select</option>
                            <option value="Mr">Mr</option>
                            <option value="Mrs">Mrs</option>
                            <option value="Ms">Ms</option>
                            <option value="Miss">Miss</option>
                            <option value="Dr">Dr</option>
                            <option value="Prof">Prof</option>
                        </select>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="first_name"
                            wire:model="first_name"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                            placeholder="John"
                            required
                        />
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="last_name"
                            wire:model="last_name"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                            placeholder="Doe"
                            required
                        />
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Gender <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="gender"
                            wire:model="gender"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                            required
                        >
                            @foreach($genders as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('gender')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <input
                                type="email"
                                id="email"
                                wire:model="email"
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                                placeholder="john.doe@example.com"
                                required
                            />
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Phone Number
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <input
                                type="tel"
                                id="phone"
                                wire:model="phone"
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                                placeholder="+1 (555) 123-4567"
                            />
                        </div>
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Date of Birth
                        </label>
                        <input
                            type="date"
                            id="date_of_birth"
                            wire:model="date_of_birth"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                        />
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Join Date -->
                    <div>
                        <label for="join_date" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Join Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            id="join_date"
                            wire:model="join_date"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                            required
                        />
                        @error('join_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Expiry Date -->
                    <div>
                        <label for="expiry_date" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Expiry Date
                        </label>
                        <input
                            type="date"
                            id="expiry_date"
                            wire:model="expiry_date"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                        />
                        @error('expiry_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Address Information -->
                <div class="mt-8 space-y-6">
                    <div>
                        <label for="address" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Address
                        </label>
                        <input
                            type="text"
                            id="address"
                            wire:model="address"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                            placeholder="123 Main Street"
                        />
                        @error('address')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="city" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                City
                            </label>
                            <input
                                type="text"
                                id="city"
                                wire:model="city"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                                placeholder="Springfield"
                            />
                            @error('city')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="state" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                State
                            </label>
                            <input
                                type="text"
                                id="state"
                                wire:model="state"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                                placeholder="IL"
                            />
                            @error('state')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="postal_code" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Postal Code
                            </label>
                            <input
                                type="text"
                                id="postal_code"
                                wire:model="postal_code"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                                placeholder="62701"
                            />
                            @error('postal_code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="country" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Country
                            </label>
                            <input
                                type="text"
                                id="country"
                                wire:model="country"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                                placeholder="USA"
                            />
                            @error('country')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-8">
                    <label for="notes" class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Notes
                    </label>
                    <textarea
                        id="notes"
                        wire:model="notes"
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                        placeholder="Additional notes about the member..."
                    ></textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Family Members Section -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 px-8 py-6 flex items-center justify-between">
                <h4 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Family Members
                </h4>
                <button type="button" wire:click="addFamilyMember" 
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-3 rounded-xl text-base font-semibold transition-colors flex items-center shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Family Member
                </button>
            </div>
            
            <div class="p-8">
                @if(count($family_members) > 0)
                    <div class="space-y-4">
                        @foreach($family_members as $index => $familyMember)
                            @if(!$familyMember['remove'])
                                <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                                    <div class="flex items-center justify-between mb-6">
                                        <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Family Member #{{ $index + 1 }}</h5>
                                        <button type="button" wire:click="removeFamilyMember({{ $index }})" 
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors p-2 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                        <div>
                                            <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Relationship <span class="text-red-500">*</span>
                                            </label>
                                            <select
                                                wire:model="family_members.{{ $index }}.relationship"
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                                                required
                                            >
                                                <option value="">Select Relationship</option>
                                                @foreach($relationships as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                First Name <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                wire:model="family_members.{{ $index }}.first_name"
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                                                placeholder="First name"
                                                required
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Last Name <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                wire:model="family_members.{{ $index }}.last_name"
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                                                placeholder="Last name"
                                                required
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Gender
                                            </label>
                                            <select
                                                wire:model="family_members.{{ $index }}.gender"
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors text-base"
                                            >
                                                @foreach($genders as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No family members</h3>
                        <p class="mt-2 text-base text-gray-500 dark:text-gray-400">Get started by adding a family member.</p>
                        <div class="mt-8">
                            <button type="button" wire:click="addFamilyMember" 
                                    class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-base font-semibold rounded-xl text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Family Member
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-6">
            <a href="{{ route('members.index') }}" 
               class="px-8 py-4 border border-gray-300 dark:border-gray-600 rounded-xl shadow-lg text-base font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all hover:shadow-xl">
                Cancel
            </a>
            <button type="submit" 
                    class="px-8 py-4 border border-transparent rounded-xl shadow-lg text-base font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ $editMode ? 'Update Member' : 'Create Member' }}
            </button>
        </div>
        </form>
    </div>
</div>
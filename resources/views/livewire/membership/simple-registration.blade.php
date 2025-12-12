<div class="p-6">
    <!-- Registration Header -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Member Registration</h2>
        <p class="text-gray-600 dark:text-gray-400">Register new members quickly and efficiently</p>
    </div>

    <!-- Success Message -->
    @if($showSuccess)
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-green-800 dark:text-green-200 font-medium">Registration Successful!</h3>
                    <p class="text-green-700 dark:text-green-300 text-sm mt-1">Member has been registered successfully.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Registration Form -->
        <div class="lg:col-span-2">
            <form wire:submit="register" class="space-y-6">
                <!-- Personal Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Personal Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Photo Upload -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Member Photo</label>
                            <div class="flex items-center space-x-4">
                                <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                                    @if($photoPreview)
                                        <img src="{{ $photoPreview }}" alt="Photo preview" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <input type="file" wire:model="photo" class="hidden" id="photo-upload" accept="image/*">
                                    <label for="photo-upload" class="cursor-pointer px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                                        Choose Photo
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">JPG, PNG up to 2MB</p>
                                    @error('photo')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                            <select wire:model="formData.title" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Title</option>
                                <option value="Mr">Mr</option>
                                <option value="Mrs">Mrs</option>
                                <option value="Ms">Ms</option>
                                <option value="Dr">Dr</option>
                            </select>
                            @error('formData.title')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.title') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                            <input
                                wire:model="formData.first_name"
                                type="text"
                                id="first_name"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.first_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.first_name') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                            <input
                                wire:model="formData.last_name"
                                type="text"
                                id="last_name"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.last_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.last_name') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date of Birth</label>
                            <input
                                wire:model="formData.date_of_birth"
                                type="date"
                                id="date_of_birth"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.date_of_birth')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.date_of_birth') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
                            <select wire:model="formData.gender" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            @error('formData.gender')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.gender') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input
                                wire:model="formData.email"
                                type="email"
                                id="email"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.email') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                            <input
                                wire:model="formData.phone"
                                type="tel"
                                id="phone"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.phone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.phone') }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                            <input
                                wire:model="formData.address"
                                type="text"
                                id="address"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.address')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.address') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                            <input
                                wire:model="formData.city"
                                type="text"
                                id="city"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.city')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.city') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State</label>
                            <input
                                wire:model="formData.state"
                                type="text"
                                id="state"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.state')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.state') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Postal Code</label>
                            <input
                                wire:model="formData.postal_code"
                                type="text"
                                id="postal_code"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.postal_code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.postal_code') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                            <input
                                wire:model="formData.country"
                                type="text"
                                id="country"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.country')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.country') }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Country Club Membership Selection -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Country Club Membership</h3>
                    
                    <div class="space-y-4">
                        @foreach($countryClubPlans as $key => $plan)
                            <label class="flex items-start p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $formData['subscription_plan'] === $key ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-500' : '' }}">
                                <input
                                    wire:model="formData.subscription_plan"
                                    type="radio"
                                    value="{{ $key }}"
                                    class="mr-3 mt-1 text-indigo-600 focus:ring-indigo-500"
                                >
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $plan['name'] }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $plan['period'] }} billing</p>
                                        </div>
                                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                            ${{ number_format($plan['price'], 0) }}/{{ $plan['period'] === 'yearly' ? 'yr' : 'mo' }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <ul class="space-y-1">
                                            @foreach($plan['features'] as $feature)
                                                <li class="flex items-start">
                                                    <svg class="w-3 h-3 text-green-500 mr-1 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    {{ $feature }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @if($plan['family_included'] > 1)
                                        <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-2">
                                            Includes {{ $plan['family_included'] }} family members
                                        </p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('formData.subscription_plan')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.subscription_plan') }}</p>
                    @enderror
                </div>

                <!-- Emergency Contact -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Emergency Contact</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Name</label>
                            <input
                                wire:model="formData.emergency_contact_name"
                                type="text"
                                id="emergency_contact_name"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.emergency_contact_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.emergency_contact_name') }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Phone</label>
                            <input
                                wire:model="formData.emergency_contact_phone"
                                type="tel"
                                id="emergency_contact_phone"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                            @error('formData.emergency_contact_phone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.emergency_contact_phone') }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Payment Method</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $formData['payment_method'] === 'card' ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-500' : '' }}">
                            <input
                                wire:model="formData.payment_method"
                                type="radio"
                                value="card"
                                class="mr-2 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-gray-900 dark:text-white">Credit Card</span>
                        </label>

                        <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $formData['payment_method'] === 'cash' ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-500' : '' }}">
                            <input
                                wire:model="formData.payment_method"
                                type="radio"
                                value="cash"
                                class="mr-2 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-gray-900 dark:text-white">Cash</span>
                        </label>

                        <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $formData['payment_method'] === 'bank' ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-500' : '' }}">
                            <input
                                wire:model="formData.payment_method"
                                type="radio"
                                value="bank"
                                class="mr-2 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-gray-900 dark:text-white">Bank Transfer</span>
                        </label>

                        <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $formData['payment_method'] === 'cheque' ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-500' : '' }}">
                            <input
                                wire:model="formData.payment_method"
                                type="radio"
                                value="cheque"
                                class="mr-2 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-gray-900 dark:text-white">Cheque</span>
                        </label>
                    </div>
                    @error('formData.payment_method')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.payment_method') }}</p>
                    @enderror
                </div>

                <!-- Additional Notes -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Additional Information</h3>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (Optional)</label>
                        <textarea
                            wire:model="formData.notes"
                            id="notes"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Any additional information or special requirements..."
                        ></textarea>
                        @error('formData.notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.notes') }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        wire:click="resetForm"
                        class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        Clear Form
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        <span wire:loading.remove>Register Member</span>
                        <span wire:loading>Registering...</span>
                    </button>
                </div>
            </form>
        </div>

<!-- Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Registration Summary</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Membership Plan:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $countryClubPlans[$formData['subscription_plan']]['name'] }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Billing Period:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ ucfirst($countryClubPlans[$formData['subscription_plan']]['period']) }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Membership Fee:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${{ number_format($countryClubPlans[$formData['subscription_plan']]['price'], 0) }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Payment Method:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ ucfirst($formData['payment_method']) }}</span>
                    </div>
                    
                    @if($countryClubPlans[$formData['subscription_plan']]['family_included'] > 1)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Family Members:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $countryClubPlans[$formData['subscription_plan']]['family_included'] }} included</span>
                        </div>
                    @endif
                    
                    <hr class="border-gray-200 dark:border-gray-600">
                    
                    <div class="flex justify-between text-lg">
                        <span class="font-semibold text-gray-900 dark:text-white">Total Due:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">${{ number_format($countryClubPlans[$formData['subscription_plan']]['price'], 0) }}</span>
                    </div>
                </div>
                
                <!-- Features List -->
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Included Features:</h4>
                    <ul class="space-y-1">
                        @foreach($countryClubPlans[$formData['subscription_plan']]['features'] as $feature)
                            <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Registration Benefits -->
                <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                    <h4 class="text-sm font-medium text-indigo-900 dark:text-indigo-200 mb-2">Country Club Benefits</h4>
                    <ul class="space-y-1 text-xs text-indigo-800 dark:text-indigo-300">
                        <li>• Premium facilities access</li>
                        <li>• Exclusive member events</li>
                        <li>• Professional staff support</li>
                        <li>• Flexible payment options</li>
                        <li>• Family-friendly environment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Listeners -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('reset-form', () => {
            setTimeout(() => {
                // Additional client-side reset if needed
            }, 3000);
        });
    });
</script>

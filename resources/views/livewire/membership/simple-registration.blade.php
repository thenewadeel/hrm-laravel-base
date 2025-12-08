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
                    </div>
                </div>

                <!-- Membership Selection -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Membership Type</h3>
                    
                    <div class="space-y-3">
                        @foreach($membershipTypes as $key => $type)
                            <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $formData['membership_type'] === $key ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-500' : '' }}">
                                <input
                                    wire:model="formData.membership_type"
                                    type="radio"
                                    value="{{ $key }}"
                                    class="mr-3 text-indigo-600 focus:ring-indigo-500"
                                >
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $type['name'] }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ implode(', ', $type['features']) }}</p>
                                        </div>
                                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">${{ $type['price'] }}/mo</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('formData.membership_type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.membership_type') }}</p>
                    @enderror
                </div>

                <!-- Payment Method -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Payment Method</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
                    </div>
                    @error('formData.payment_method')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $errors->first('formData.payment_method') }}</p>
                    @enderror
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
                        <span class="text-gray-600 dark:text-gray-400">Membership Type:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $membershipTypes[$formData['membership_type']]['name'] }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Monthly Fee:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${{ $membershipTypes[$formData['membership_type']]['price'] }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Payment Method:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ ucfirst($formData['payment_method']) }}</span>
                    </div>
                    
                    <hr class="border-gray-200 dark:border-gray-600">
                    
                    <div class="flex justify-between text-lg">
                        <span class="font-semibold text-gray-900 dark:text-white">Total Due:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">${{ $membershipTypes[$formData['membership_type']]['price'] }}</span>
                    </div>
                </div>

                <!-- Features List -->
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Included Features:</h4>
                    <ul class="space-y-1">
                        @foreach($membershipTypes[$formData['membership_type']]['features'] as $feature)
                            <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ $feature }}
                            </li>
                        @endforeach
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

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            👤 {{ __('Complete Your Employee Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Setup Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Welcome Message -->
                    <div class="text-center mb-8">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100">
                            <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="mt-4 text-2xl font-bold text-gray-900">Welcome to Your Employee Portal!</h2>
                        <p class="mt-2 text-gray-600">
                            Before you can access your dashboard, we need to complete your employee profile.
                        </p>
                    </div>

                    <!-- Setup Form -->
                    <form method="POST" action="{{ route('portal.employee.complete-setup') }}">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Personal Information Section -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                            </div>

                            <!-- First Name -->
                            <div>
                                <x-input-label for="first_name" :value="__('First Name')" />
                                <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name"
                                    :value="old('first_name', $user->name ? explode(' ', $user->name)[0] : '')" required autofocus />
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                            </div>

                            <!-- Last Name -->
                            <div>
                                <x-input-label for="last_name" :value="__('Last Name')" />
                                <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name"
                                    :value="old(
                                        'last_name',
                                        $user->name && count(explode(' ', $user->name)) > 1
                                            ? explode(' ', $user->name)[1]
                                            : '',
                                    )" required />
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                            </div>

                            <!-- Email (Read-only) -->
                            <div class="md:col-span-2">
                                <x-input-label for="email" :value="__('Email Address')" />
                                <x-text-input id="email" class="block mt-1 w-full bg-gray-50" type="email"
                                    value="{{ $user->email }}" readonly disabled />
                                <p class="mt-1 text-sm text-gray-500">Your email address is taken from your user
                                    account.</p>
                            </div>

                            <!-- Contact Information Section -->
                            <div class="md:col-span-2 mt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                            </div>

                            <!-- Phone -->
                            <div class="md:col-span-2">
                                <x-input-label for="phone" :value="__('Phone Number')" />
                                <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone"
                                    :value="old('phone')" placeholder="+1 (555) 123-4567" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <!-- Address -->
                            <div class="md:col-span-2">
                                <x-input-label for="address" :value="__('Address')" />
                                <textarea id="address" name="address" rows="3"
                                    class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                    placeholder="Enter your full address">{{ old('address') }}</textarea>
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>

                            <!-- Additional Information Section -->
                            <div class="md:col-span-2 mt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                            </div>

                            <!-- Date of Birth -->
                            <div>
                                <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                                <x-text-input id="date_of_birth" class="block mt-1 w-full" type="date"
                                    name="date_of_birth" :value="old('date_of_birth')" />
                                <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                            </div>

                            <!-- Gender -->
                            <div>
                                <x-input-label for="gender" :value="__('Gender')" />
                                <select id="gender" name="gender"
                                    class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female
                                    </option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Current Organization Info -->
                        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-blue-400 mr-2" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="text-sm font-medium text-blue-800">Organization Information</span>
                            </div>
                            <p class="mt-1 text-sm text-blue-600">
                                You are setting up your profile for:
                                <strong>{{ $user->currentOrganization->name ?? 'Your Organization' }}</strong>
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end mt-8">
                            <x-primary-button class="ml-4">
                                {{ __('Complete Setup & Enter Portal') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Help Text -->
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <div class="flex">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Why do we need this information?</h4>
                                <p class="mt-1 text-sm text-gray-600">
                                    This information helps us create your employee record for attendance tracking,
                                    payroll processing, and other HR functions. Your data is secure and will only
                                    be used for official business purposes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Information -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Need help? Contact your HR department at
                    <a href="mailto:hr@yourcompany.com"
                        class="text-blue-600 hover:text-blue-500">hr@yourcompany.com</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto-fill name fields from user's existing name
        document.addEventListener('DOMContentLoaded', function() {
            const userName = '{{ $user->name }}';
            if (userName) {
                const nameParts = userName.split(' ');
                if (nameParts.length > 0 && !document.getElementById('first_name').value) {
                    document.getElementById('first_name').value = nameParts[0];
                }
                if (nameParts.length > 1 && !document.getElementById('last_name').value) {
                    document.getElementById('last_name').value = nameParts.slice(1).join(' ');
                }
            }
        });
    </script>
</x-app-layout>

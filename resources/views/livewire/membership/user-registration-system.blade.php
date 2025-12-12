<div>
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">User Registration System</h2>
        <p class="text-gray-600 dark:text-gray-400">Register members individually or upload in bulk using CSV</p>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex">
            <button
                wire:click="switchTab('individual')"
                class="{{ $activeTab === 'individual' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} 
                       flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors duration-200"
            >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Individual Registration
            </button>
            <button
                wire:click="switchTab('bulk')"
                class="{{ $activeTab === 'bulk' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} 
                       flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors duration-200"
            >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Bulk CSV Upload
            </button>
        </nav>
    </div>

    <!-- Individual Registration Tab -->
    @if($activeTab === 'individual')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Registration Form -->
            <div class="lg:col-span-2">
                <form wire:submit="registerIndividual" class="space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Personal Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Photo</label>
                                <div class="flex items-center space-x-4">
                                    <div class="w-20 h-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                                        @if($individualPhotoPreview)
                                            <img src="{{ $individualPhotoPreview }}" alt="Photo preview" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <input type="file" wire:model="individualPhoto" class="hidden" id="individual-photo-upload" accept="image/*">
                                        <label for="individual-photo-upload" class="cursor-pointer px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                                            Choose Photo
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                                <select wire:model="individualForm.title" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                    <option value="">Select Title</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Ms">Ms</option>
                                    <option value="Dr">Dr</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                                <input wire:model="individualForm.first_name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                                <input wire:model="individualForm.last_name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <input wire:model="individualForm.email" type="email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                                <input wire:model="individualForm.phone" type="tel" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                                <input wire:model="individualForm.address" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                                <input wire:model="individualForm.city" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State</label>
                                <input wire:model="individualForm.state" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Postal Code</label>
                                <input wire:model="individualForm.postal_code" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                                <input wire:model="individualForm.country" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Membership & Emergency Contact -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Membership & Emergency Contact</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscription Plan</label>
                                <select wire:model="individualForm.subscription_plan" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                    <option value="individual">Individual</option>
                                    <option value="family">Family</option>
                                    <option value="corporate">Corporate</option>
                                    <option value="sports">Sports</option>
                                    <option value="social">Social</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency Contact Name</label>
                                <input wire:model="individualForm.emergency_contact_name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency Contact Phone</label>
                                <input wire:model="individualForm.emergency_contact_phone" type="tel" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                            <span wire:loading.remove>Register Member</span>
                            <span wire:loading>Registering...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Stats Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Registration Stats</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Today's Registrations</span>
                            <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">12</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">This Week</span>
                            <span class="text-xl font-bold text-green-600 dark:text-green-400">48</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">This Month</span>
                            <span class="text-xl font-bold text-blue-600 dark:text-blue-400">156</span>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <h4 class="text-sm font-medium text-indigo-900 dark:text-indigo-200 mb-2">Quick Tips</h4>
                        <ul class="text-xs text-indigo-800 dark:text-indigo-300 space-y-1">
                            <li>• Ensure all required fields are filled</li>
                            <li>• Upload a clear photo for member cards</li>
                            <li>• Verify emergency contact information</li>
                            <li>• Choose appropriate subscription plan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk CSV Upload Tab -->
    @if($activeTab === 'bulk')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Upload Section -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Bulk CSV Upload</h3>
                    
                    @if(!$showPreview)
                        <!-- File Upload -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select CSV File</label>
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <input type="file" wire:model="csvFile" class="hidden" id="csv-upload" accept=".csv,.txt">
                                <label for="csv-upload" class="cursor-pointer px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    Choose CSV File
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">CSV files up to 10MB</p>
                            </div>
                        </div>

                        <!-- Template Download -->
                        <div class="mb-6">
                            <button wire:click="downloadTemplate" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download Template
                            </button>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Download the CSV template with required fields</p>
                        </div>

                        <!-- Upload Button -->
                        @if($csvFile)
                            <div class="flex justify-end">
                                <button wire:click="uploadCSV" class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    Preview CSV Data
                                </button>
                            </div>
                        @endif
                    @else
                        <!-- Preview Section -->
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">CSV Preview (First 5 rows)</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            @foreach(array_keys($csvPreview[0] ?? []) as $header)
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($csvPreview as $row)
                                            <tr>
                                                @foreach($row as $cell)
                                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">{{ $cell }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                Total records to process: {{ $uploadProgress['total'] }}
                            </p>
                        </div>

                        <!-- Process Button -->
                        <div class="flex justify-between items-center">
                            <button wire:click="resetBulkUpload" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                Choose Different File
                            </button>
                            <button wire:click="processBulkUpload" class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                Process All Records
                            </button>
                        </div>

                        <!-- Progress Bar -->
                        @if($uploadProgress['processed'] > 0)
                            <div class="mt-6">
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    <span>Processing: {{ $uploadProgress['processed'] }} / {{ $uploadProgress['total'] }}</span>
                                    <span>{{ round(($uploadProgress['processed'] / $uploadProgress['total']) * 100) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ round(($uploadProgress['processed'] / $uploadProgress['total']) * 100) }}%"></div>
                                </div>
                                
                                <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
                                    <div class="text-center">
                                        <p class="text-green-600 dark:text-green-400 font-bold">{{ $uploadProgress['successful'] }}</p>
                                        <p class="text-gray-500 dark:text-gray-400">Successful</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-red-600 dark:text-red-400 font-bold">{{ $uploadProgress['failed'] }}</p>
                                        <p class="text-gray-500 dark:text-gray-400">Failed</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-blue-600 dark:text-blue-400 font-bold">{{ $uploadProgress['processed'] }}</p>
                                        <p class="text-gray-500 dark:text-gray-400">Processed</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Instructions Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">CSV Instructions</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Required Fields:</h4>
                            <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• title (Mr, Mrs, Ms, Dr)</li>
                                <li>• first_name</li>
                                <li>• last_name</li>
                                <li>• email</li>
                                <li>• phone</li>
                                <li>• address</li>
                                <li>• city</li>
                                <li>• state</li>
                                <li>• postal_code</li>
                                <li>• country</li>
                                <li>• subscription_plan</li>
                                <li>• emergency_contact_name</li>
                                <li>• emergency_contact_phone</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Format Guidelines:</h4>
                            <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• Use UTF-8 encoding</li>
                                <li>• First row should contain headers</li>
                                <li>• Use comma as delimiter</li>
                                <li>• Remove empty rows</li>
                                <li>• Validate email format</li>
                            </ul>
                        </div>

                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <h4 class="text-sm font-medium text-yellow-900 dark:text-yellow-200 mb-1">Important:</h4>
                            <p class="text-xs text-yellow-800 dark:text-yellow-300">
                                Always validate your data before uploading. Invalid records will be skipped and logged in the error report.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Event Listeners -->
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

            Livewire.on('download-file', function (event) {
                const blob = new Blob([event.content], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = event.filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            });
        });
    </script>
</div>
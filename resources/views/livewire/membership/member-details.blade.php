<div>
    <!-- Member Details Container -->
    <div class="space-y-6">
        
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Member Profile</h1>
        </div>
        
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Member Photo -->
                    <div class="relative">
                        @if($member->photo_path)
                            <img src="{{ Storage::url($member->photo_path) }}" 
                                 alt="{{ $member->full_name }}" 
                                 class="w-20 h-20 rounded-full object-cover">
                        @else
                            <div class="w-20 h-20 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                <span class="text-gray-600 dark:text-gray-300 text-xl font-semibold">
                                    {{ substr($member->full_name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        
                        <!-- Photo Upload Button -->
                        <button wire:click="$set('showAddFamilyMember', true)" 
                                class="absolute bottom-0 right-0 bg-blue-500 hover:bg-blue-600 text-white p-1 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Member Basic Info -->
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $member->full_name }}</h2>
                        <p class="text-gray-600 dark:text-gray-300">{{ $member->membership_number }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $member->status }}</p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex space-x-2">
                <button wire:click="generateQRCode" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    QR Code
                </button>
                <button wire:click="generateBarcode" 
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    Barcode
                </button>
                <button wire:click="$set('showEmailModal', true)" 
                        class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                    Email
                </button>
                <button wire:click="$set('showSMSModal', true)" 
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                    SMS
                </button>
                </div>
            </div>
            

        </div>
        
        <!-- Contact Information -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <p class="text-gray-900 dark:text-white">{{ $member->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                    <p class="text-gray-900 dark:text-white">{{ $member->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                    <p class="text-gray-900 dark:text-white">{{ $member->address ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                    <p class="text-gray-900 dark:text-white">{{ $member->date_of_birth?->format('M d, Y') ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                    <p class="text-gray-900 dark:text-white">{{ $member->city ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                    <p class="text-gray-900 dark:text-white">{{ $member->state ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postal Code</label>
                    <p class="text-gray-900 dark:text-white">{{ $member->postal_code ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Family Members Section -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Family Members</h3>
                <button wire:click="$set('showAddFamilyMember', true)" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Add Family Member
                </button>
            </div>
            
            <!-- Family Members List -->
            @if($member->familyMembers && $member->familyMembers->count() > 0)
                <div class="space-y-3">
                    @foreach($member->familyMembers as $familyMember)
                        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold">{{ $familyMember->full_name }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ $familyMember->relationship }} • {{ $familyMember->date_of_birth?->format('M d, Y') }}
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <button wire:click="editFamilyMember({{ $familyMember->id }})" 
                                            class="text-blue-500 hover:text-blue-600">
                                        Edit
                                    </button>
                                    <button wire:click="removeFamilyMember({{ $familyMember->id }})" 
                                            class="text-red-500 hover:text-red-600">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">No family members added yet.</p>
            @endif
            
            <!-- Add/Edit Family Member Form -->
            @if($showAddFamilyMember || $showEditFamilyMember)
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded">
                    <h3 class="text-lg font-semibold mb-4">
                        {{ $showEditFamilyMember ? 'Edit' : 'Add' }} Family Member
                    </h3>
                    
                    <form wire:submit="{{ $showEditFamilyMember ? 'updateFamilyMember' : 'addFamilyMember' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                                <input type="text" wire:model="familyMemberForm.first_name" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                @error('familyMemberForm.first_name')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                                <input type="text" wire:model="familyMemberForm.last_name" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                @error('familyMemberForm.last_name')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Relationship</label>
                                <select wire:model="familyMemberForm.relationship" 
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                    <option value="">Select Relationship</option>
                                    <option value="spouse">Spouse</option>
                                    <option value="child">Child</option>
                                    <option value="parent">Parent</option>
                                    <option value="sibling">Sibling</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('familyMemberForm.relationship')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                                <input type="date" wire:model="familyMemberForm.date_of_birth" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                @error('familyMemberForm.date_of_birth')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                                <select wire:model="familyMemberForm.gender" 
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('familyMemberForm.gender')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-4 flex space-x-2">
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                {{ $showEditFamilyMember ? 'Update' : 'Add' }} Family Member
                            </button>
                            <button type="button" wire:click="$set('showAddFamilyMember', false); $set('showEditFamilyMember', false)" 
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
        
        <!-- Membership History -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Membership History</h3>
            
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Join Date</label>
                        <p class="text-gray-900 dark:text-white">{{ $member->join_date?->format('M j, Y') ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date</label>
                        <p class="text-gray-900 dark:text-white">{{ $member->expiry_date?->format('M j, Y') ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            
            @if($member->subscriptions && $member->subscriptions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Plan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Join Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    End Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($member->subscriptions as $subscription)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $subscription->plan?->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $subscription->start_date?->format('M d, Y') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $subscription->end_date?->format('M d, Y') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                               {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $subscription->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">No membership history found.</p>
            @endif
        </div>
        
        <!-- Fee & Payment History -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Fee & Payment History</h3>
            
            @if($member->fees && $member->fees->count() > 0)
                <div class="space-y-4">
                    @foreach($member->fees as $fee)
                        <div class="border border-gray-200 dark:border-gray-700 rounded p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold">{{ $fee->fee_type }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        Due: {{ $fee->due_date?->format('M d, Y') ?? 'N/A' }}
                                    </p>
                                    <p class="text-sm font-medium">
                                        Amount: {{ number_format($fee->amount, 2) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                           {{ $fee->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                              ($fee->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $fee->status }}
                                    </span>

                                </div>
                            </div>
                            
                            @if($fee->payments && $fee->payments->count() > 0)
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <h5 class="text-sm font-medium mb-2">Payments:</h5>
                                    @foreach($fee->payments as $payment)
                                        <div class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ $payment->payment_date?->format('M d, Y') }} - 
                                            {{ number_format($payment->amount, 2) }} 
                                            ({{ $payment->payment_method }})
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">No fee records found.</p>
            @endif
        </div>
        
        <!-- QR Code Modal -->
        @if($showQRCode)
            <div wire:click="closeQRCode" 
                 class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div wire:click.stop class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full mx-4">
                    <h3 class="text-lg font-semibold mb-4">Member QR Code</h3>
                    <div class="flex justify-center mb-4">
                        {!! $qrCodeData !!}
                    </div>
                    <button wire:click="closeQRCode" 
                            class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        Close
                    </button>
                </div>
            </div>
        @endif
        
        <!-- Barcode Modal -->
        @if($showBarcode)
            <div wire:click="closeBarcode" 
                 class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div wire:click.stop class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full mx-4">
                    <h3 class="text-lg font-semibold mb-4">Member Barcode</h3>
                    <div class="flex justify-center mb-4">
                        {!! $barcodeData !!}
                    </div>
                    <button wire:click="closeBarcode" 
                            class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        Close
                    </button>
                </div>
            </div>
        @endif
        
    </div>
</div>
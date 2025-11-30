<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Attach User to Organization
        </h2>
    </x-slot>
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="bg-gray-800 text-white p-6">
                <h1 class="text-2xl font-bold">Attach User to Organization</h1>
                <p class="text-gray-300 mt-2">Fix user organization attachment issues</p>
            </div>
            
            <form action="{{ route('admin.attach-user') }}" method="POST" class="p-6">
                @csrf
                
                <!-- User Selection -->
                <div class="mb-6">
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select User
                    </label>
                    <select id="user_id" name="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Choose a user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->email }})
                                @if($user->organizations->count() > 0)
                                    - Already attached to {{ $user->organizations->count() }} org(s)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Organization Selection -->
                <div class="mb-6">
                    <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Organization
                    </label>
                    <select id="organization_id" name="organization_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Choose an organization...</option>
                        @foreach($organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                        @endforeach
                    </select>
                    @error('organization_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Organization Unit Selection -->
                <div class="mb-6">
                    <label for="organization_unit_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Organization Unit (Optional)
                    </label>
                    <select id="organization_unit_id" name="organization_unit_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select unit (will use default if not specified)</option>
                    </select>
                    @error('organization_unit_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Position -->
                <div class="mb-6">
                    <label for="position" class="block text-sm font-medium text-gray-700 mb-2">
                        Position
                    </label>
                    <input type="text" id="position" name="position" value="Staff" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('position')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Roles -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Roles
                    </label>
                    <div class="space-y-2">
                        @foreach(['inventory_user', 'inventory_manager', 'inventory_admin'] as $role)
                            <label class="flex items-center">
                                <input type="checkbox" name="roles[]" value="{{ $role }}" 
                                       @if($role === 'inventory_user') checked @endif
                                       class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Set as Current Organization -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="set_as_current" value="1" checked
                               class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Set as user's current organization</span>
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between">
                    <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Attach User to Organization
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const organizationSelect = document.getElementById('organization_id');
    const unitSelect = document.getElementById('organization_unit_id');
    
    // Store organizations data
    const organizations = @json($organizations);
    
    organizationSelect.addEventListener('change', function() {
        const selectedOrgId = this.value;
        
        // Clear current units
        unitSelect.innerHTML = '<option value="">Select unit (will use default if not specified)</option>';
        
        if (selectedOrgId) {
            const selectedOrg = organizations.find(org => org.id == selectedOrgId);
            if (selectedOrg && selectedOrg.units) {
                selectedOrg.units.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.name + ' (' + unit.type + ')';
                    unitSelect.appendChild(option);
                });
            }
        }
    });
});
</script>
@endpush
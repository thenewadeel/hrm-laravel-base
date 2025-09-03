<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-4">Organization Members</h2>

    <div class="mb-4">
        <label for="organization-filter" class="block text-gray-700 font-bold mb-2">Select Organization:</label>
        <select id="organization-filter" wire:model.live="organizationId"
            class="block w-full border border-gray-300 rounded-md p-2">
            <option value="">Select an Organization</option>
            @foreach ($organizations as $org)
                <option value="{{ $org->id }}">
                    {{ $org->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <input wire:model.live="search" type="search" placeholder="Search members by name or email..."
            class="w-full p-2 border border-gray-300 rounded-md">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($members as $member)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $member->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $member->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $member->pivot->position ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($member->pivot->organization_unit_id)
                                {{ \App\Models\OrganizationUnit::find($member->pivot->organization_unit_id)->name ?? 'N/A' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            <a href="#" class="ml-2 text-red-600 hover:text-red-900">Remove</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No members found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

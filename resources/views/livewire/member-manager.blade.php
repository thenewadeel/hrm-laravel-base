<div class="surface shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-4 text-primary">Organization Members</h2>

    <div class="mb-4">
        <label for="organization-filter" class="block text-primary font-bold mb-2">Select Organization:</label>
        <select id="organization-filter" wire:model.live="organizationId"
            class="block w-full border border-primary rounded-md p-2">
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
            class="w-full p-2 border border-primary rounded-md">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y border-secondary">
            <thead class="bg-primary">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Position
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="surface divide-y border-secondary">
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
                        <td colspan="5" class="px-6 py-4 text-center text-muted">No members found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

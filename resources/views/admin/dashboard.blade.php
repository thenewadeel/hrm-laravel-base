<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Portal
        </h2>
    </x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="bg-gray-800 text-white p-6">
                <h1 class="text-3xl font-bold">Admin Portal</h1>
                <p class="text-gray-300 mt-2">Manage user-organization attachments and system issues</p>
            </div>
            
            <!-- Statistics -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold mb-4">System Statistics</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['total_users'] }}</div>
                        <div class="text-sm text-gray-600">Total Users</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ $stats['users_with_orgs'] }}</div>
                        <div class="text-sm text-gray-600">Users with Organizations</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ $stats['users_without_orgs'] }}</div>
                        <div class="text-sm text-gray-600">Users without Organizations</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ $stats['total_orgs'] }}</div>
                        <div class="text-sm text-gray-600">Total Organizations</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">{{ $stats['orphaned_org_users'] }}</div>
                        <div class="text-sm text-gray-600">Orphaned Org Users</div>
                    </div>
                </div>
            </div>

            <!-- Users with Issues -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Users with Organization Issues</h2>
                    <a href="{{ route('admin.attach-user.form') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Attach User to Organization
                    </a>
                </div>
                
                @if($usersWithIssues->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Org</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attached Orgs</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issues</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($usersWithIssues as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($user->current_organization_id)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $user->currentOrganization?->name ?? 'Unknown' }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Not Set
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                {{ $user->organizations->count() }} organization(s)
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($user->organizations->count() === 0)
                                                <span class="text-red-600 text-xs">No organizations</span>
                                            @elseif(!$user->current_organization_id)
                                                <span class="text-yellow-600 text-xs">No current org set</span>
                                            @else
                                                <span class="text-green-600 text-xs">OK</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($user->organizations->count() > 0 && !$user->current_organization_id)
                                                <form action="{{ route('admin.fix-user-organization') }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <input type="hidden" name="organization_id" value="{{ $user->organizations->first()->id }}">
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900 mr-3">Fix Current Org</button>
                                                </form>
                                            @endif
                                            @if($user->organizations->count() > 0)
                                                <form action="{{ route('admin.detach-user') }}" method="POST" class="inline" onsubmit="return confirm('Detach user from all organizations?')">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <input type="hidden" name="organization_id" value="{{ $user->organizations->first()->id }}">
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Detach</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500">
                        <p>No users with organization issues found.</p>
                    </div>
                @endif
            </div>

            <!-- Organizations -->
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">Organizations</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($organizations as $organization)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="font-semibold text-lg">{{ $organization->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $organization->users->count() }} user(s) • 
                                {{ $organization->units->count() }} unit(s)
                            </p>
                            <div class="mt-3">
                                <div class="text-xs text-gray-500">
                                    Users: {{ $organization->users->pluck('name')->implode(', ') ?: 'None' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
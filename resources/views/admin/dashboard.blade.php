<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="{{ __('Admin Portal') }}"
            description="{{ __('Manage user-organization attachments and system issues.') }}"
        />
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-card title="{{ __('System Statistics') }}">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <x-dashboard.stat-card label="{{ __('Total Users') }}" :value="$stats['total_users']" tone="primary" />
                    <x-dashboard.stat-card label="{{ __('Users with Organizations') }}"
                        :value="$stats['users_with_orgs']" tone="success" />
                    <x-dashboard.stat-card label="{{ __('Users without Organizations') }}"
                        :value="$stats['users_without_orgs']" tone="error" />
                    <x-dashboard.stat-card label="{{ __('Total Organizations') }}" :value="$stats['total_orgs']"
                        tone="info" />
                    <x-dashboard.stat-card label="{{ __('Orphaned Org Users') }}"
                        :value="$stats['orphaned_org_users']" tone="warning" />
                </div>
            </x-card>

            <x-card title="{{ __('Users with Organization Issues') }}">
                <x-slot name="actions">
                    <x-button.link href="{{ route('admin.attach-user.form') }}">
                        {{ __('Attach User to Organization') }}
                    </x-button.link>
                </x-slot>

                @if ($usersWithIssues->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-secondary">
                            <thead>
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Current Org') }}</th>
                                    <th>{{ __('Attached Orgs') }}</th>
                                    <th>{{ __('Issues') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="surface divide-y divide-secondary">
                                @foreach ($usersWithIssues as $user)
                                    <tr>
                                        <td>
                                            <div class="text-sm font-medium text-primary">{{ $user->name }}</div>
                                        </td>
                                        <td>
                                            <div class="text-sm text-secondary">{{ $user->email }}</div>
                                        </td>
                                        <td>
                                            @if ($user->current_organization_id)
                                                <x-badge color="green">{{ $user->currentOrganization?->name ?? 'Unknown' }}</x-badge>
                                            @else
                                                <x-badge color="red">{{ __('Not Set') }}</x-badge>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-sm text-secondary">
                                                {{ $user->organizations->count() }} {{ __('organization(s)') }}
                                            </div>
                                        </td>
                                        <td>
                                            @if ($user->organizations->count() === 0)
                                                <span class="text-xs text-error">{{ __('No organizations') }}</span>
                                            @elseif (! $user->current_organization_id)
                                                <span class="text-xs text-warning">{{ __('No current org set') }}</span>
                                            @else
                                                <span class="text-xs text-success">{{ __('OK') }}</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap text-sm font-medium">
                                            @if ($user->organizations->count() > 0 && ! $user->current_organization_id)
                                                <form action="{{ route('admin.fix-user-organization') }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <input type="hidden" name="organization_id"
                                                        value="{{ $user->organizations->first()->id }}">
                                                    <button type="submit"
                                                        class="mr-3 font-medium text-accent hover:text-primary">
                                                        {{ __('Fix Current Org') }}
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($user->organizations->count() > 0)
                                                <form action="{{ route('admin.detach-user') }}" method="POST" class="inline"
                                                    onsubmit="return confirm('Detach user from all organizations?')">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <input type="hidden" name="organization_id"
                                                        value="{{ $user->organizations->first()->id }}">
                                                    <button type="submit" class="font-medium text-error hover:text-error/80">
                                                        {{ __('Detach') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="py-4 text-center text-secondary">
                        {{ __('No users with organization issues found.') }}
                    </p>
                @endif
            </x-card>

            <x-card title="{{ __('Organizations') }}">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($organizations as $organization)
                        <div class="surface rounded-lg p-5">
                            <h3 class="font-semibold text-lg text-primary">{{ $organization->name }}</h3>
                            <p class="mt-1 text-sm text-secondary">
                                {{ $organization->users->count() }} {{ __('user(s)') }} •
                                {{ $organization->units->count() }} {{ __('unit(s)') }}
                            </p>
                            <div class="mt-3">
                                <div class="text-xs text-muted">
                                    {{ __('Users') }}: {{ $organization->users->pluck('name')->implode(', ') ?: __('None') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
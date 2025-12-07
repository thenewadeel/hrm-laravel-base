<div>
    <!-- Dashboard Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Membership Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400">Overview of your membership system</p>
    </div>

    <!-- Period Selector -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Period:</label>
                    <select
                        wire:model.live="period"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    >
                        @foreach($periods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Last updated: {{ now()->format('M j, Y g:i A') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(count($this->alerts) > 0)
        <div class="space-y-4 mb-6">
            @foreach($this->alerts as $alert)
                <div class="rounded-md p-4
                    @if($alert['type'] === 'error') bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800
                    @elseif($alert['type'] === 'warning') bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800
                    @elseif($alert['type'] === 'info') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800
                    @else bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800
                    @endif">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            @if($alert['type'] === 'error')
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            @elseif($alert['type'] === 'warning')
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium
                                @if($alert['type'] === 'error') text-red-800 dark:text-red-200
                                @elseif($alert['type'] === 'warning') text-yellow-800 dark:text-yellow-200
                                @else text-blue-800 dark:text-blue-200
                                @endif">
                                {{ $alert['title'] }}
                            </h3>
                            <div class="mt-1 text-sm
                                @if($alert['type'] === 'error') text-red-700 dark:text-red-300
                                @elseif($alert['type'] === 'warning') text-yellow-700 dark:text-yellow-300
                                @else text-blue-700 dark:text-blue-300
                                @endif">
                                {{ $alert['message'] }}
                            </div>
                            @if(isset($alert['action']))
                                <div class="mt-2">
                                    <a href="{{ $alert['action'] }}" class="text-sm font-medium underline
                                        @if($alert['type'] === 'error') text-red-800 dark:text-red-200 hover:text-red-900
                                        @elseif($alert['type'] === 'warning') text-yellow-800 dark:text-yellow-200 hover:text-yellow-900
                                        @else text-blue-800 dark:text-blue-200 hover:text-blue-900
                                        @endif">
                                        View Details →
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach($this->quickActions as $action)
            <a href="{{ $action['route'] }}" 
               class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-{{ $action['color'] }}-100 dark:bg-{{ $action['color'] }}-900 rounded-full flex items-center justify-center">
                            @switch($action['icon'])
                                @case('user-plus')
                                    <svg class="w-6 h-6 text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    @break
                                @case('credit-card')
                                    <svg class="w-6 h-6 text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    @break
                                @case('id-card')
                                    <svg class="w-6 h-6 text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                    </svg>
                                    @break
                                @case('dollar-sign')
                                    <svg class="w-6 h-6 text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $action['title'] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $action['description'] }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Main Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Members -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Members</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $memberStats['total_members'] ?? 0 }}</dd>
                        <div class="flex items-center mt-1">
                            @if($growthMetrics['member_growth'] > 0)
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                <span class="text-sm text-green-600 dark:text-green-400 ml-1">
                                    +{{ $growthMetrics['member_growth'] }}%
                                </span>
                            @else
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                                <span class="text-sm text-red-600 dark:text-red-400 ml-1">
                                    {{ $growthMetrics['member_growth'] }}%
                                </span>
                            @endif
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Subscriptions</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $subscriptionStats['active_subscriptions'] ?? 0 }}</dd>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $subscriptionStats['total_subscriptions'] ?? 0 }} total
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Revenue ({{ ucfirst($period) }})</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($growthMetrics['revenue'], 2) }}</dd>
                        <div class="flex items-center mt-1">
                            @if($growthMetrics['revenue_growth'] > 0)
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                <span class="text-sm text-green-600 dark:text-green-400 ml-1">
                                    +{{ $growthMetrics['revenue_growth'] }}%
                                </span>
                            @else
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                                <span class="text-sm text-red-600 dark:text-red-400 ml-1">
                                    {{ $growthMetrics['revenue_growth'] }}%
                                </span>
                            @endif
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Cards Generated -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Cards Generated</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $cardStats['total_cards_generated'] ?? 0 }}</dd>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $cardStats['cards_today'] ?? 0 }} today
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Pending Fees -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Pending Fees</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($feeStats['pending_amount'] ?? 0, 2) }}</dd>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $feeStats['pending_fees'] ?? 0 }} fees
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Family Members -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Family Members</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $memberStats['total_family_members'] ?? 0 }}</dd>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $memberStats['members_with_families'] ?? 0 }} families
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Collection Rate -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Collection Rate</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $feeStats['collection_rate'] ?? 0 }}%
                        </dd>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            This period
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- New Members -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">New Members</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $growthMetrics['new_members'] }}</dd>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ ucfirst($period) }}
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Members -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Members</h3>
                <div class="space-y-3">
                    @forelse($recentMembers as $member)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                @if($member->photo_path)
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ Storage::url($member->photo_path) }}" alt="{{ $member->full_name }}">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                            {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $member->full_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $member->membership_number }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $member->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">
                            No recent members
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Expiring Soon</h3>
                <div class="space-y-3">
                    @forelse($this->expiringMembers->take(5) as $member)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                @if($member->photo_path)
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ Storage::url($member->photo_path) }}" alt="{{ $member->full_name }}">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                            {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $member->full_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $member->membership_number }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm text-red-600 dark:text-red-400">
                                {{ $member->expiry_date->format('M j, Y') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">
                            No members expiring soon
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

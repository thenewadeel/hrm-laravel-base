<div>
    <!-- Subscription Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Subscriptions</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $statistics['total_subscriptions'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Subscriptions</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $statistics['active_subscriptions'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Expiring Soon</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $this->expiringSubscriptions->count() }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Monthly Revenue</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($statistics['monthly_revenue'] ?? 0, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex space-x-3 mb-4 sm:mb-0">
                    @if(!$member)
                        <a href="{{ route('members.create') }}" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Add Member Subscription
                        </a>
                    @endif
                    <button
                        wire:click="showCreateForm"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        Create Subscription
                    </button>
                    <button
                        wire:click="processAutoRenewals"
                        wire:confirm="Process all due auto-renewals?"
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >
                        Process Auto-Renewals
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Subscription Form -->
    @if($showCreateForm)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Create New Subscription</h3>
                    <button
                        wire:click="hideCreateForm"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="createSubscription">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Member Selection (if not pre-selected) -->
                        @if(!$member)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Member <span class="text-red-500">*</span>
                                </label>
                                <select
                                    wire:model.live="member_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    required
                                >
                                    <option value="">Select Member</option>
                                    @foreach(\App\Models\Membership\Member::where('organization_id', auth()->user()->current_organization_id)->get() as $memberOption)
                                        <option value="{{ $memberOption->id }}">{{ $memberOption->full_name }} ({{ $memberOption->membership_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Subscription Plan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Subscription Plan <span class="text-red-500">*</span>
                            </label>
                            <select
                                wire:model.live="subscription_plan_id"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                required
                            >
                                <option value="">Select Plan</option>
                                @foreach($subscriptionPlans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} - ${{ number_format($plan->amount, 2) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                wire:model="start_date"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                required
                            />
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                End Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                wire:model="end_date"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                required
                            />
                        </div>

                        <!-- Discount Percentage -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Discount Percentage
                            </label>
                            <input
                                type="number"
                                wire:model="discount_percentage"
                                min="0"
                                max="100"
                                step="0.01"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Discount Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Discount Amount
                            </label>
                            <input
                                type="number"
                                wire:model="discount_amount"
                                min="0"
                                step="0.01"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Auto Renew -->
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                wire:model="auto_renew"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label for="auto_renew" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                Auto Renew
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Notes
                        </label>
                        <textarea
                            wire:model="notes"
                            rows="3"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        ></textarea>
                    </div>

                    <!-- Total Amount Display -->
                    @if($subscription_plan_id > 0)
                        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                Total Amount: ${{ number_format($this->calculateTotalAmount(), 2) }}
                            </div>
                        </div>
                    @endif

                    <!-- Form Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="hideCreateForm"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>Create Subscription</span>
                            <span wire:loading>Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="Search subscriptions by member name, plan..."
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        wire:model.live="status"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                    >
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <select
                        wire:model.live="perPage"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                    >
                        <option value="10">10 per page</option>
                        <option value="15">15 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscriptions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <button
                                wire:click="sort('created_at')"
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                            >
                                Date Created
                                @if($sortBy === 'created_at')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Member
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Plan
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button
                                wire:click="sort('start_date')"
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                            >
                                Period
                                @if($sortBy === 'start_date')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button
                                wire:click="sort('total_amount')"
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300"
                            >
                                Amount
                                @if($sortBy === 'total_amount')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $subscription->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $subscription->member->full_name }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $subscription->member->membership_number }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($subscription->subscriptionPlan)
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $subscription->subscriptionPlan->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $subscription->subscriptionPlan->plan_type }}
                                    </div>
                                @else
                                    <div class="text-sm text-red-600">
                                        Plan not found (ID: {{ $subscription->subscription_plan_id }})
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $subscription->start_date->format('M j, Y') }}</div>
                                <div>{{ $subscription->end_date->format('M j, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                ${{ number_format($subscription->total_amount, 2) }}
                                @if($subscription->paid_amount < $subscription->total_amount)
                                    <div class="text-xs text-red-600">
                                        Paid: ${{ number_format($subscription->paid_amount, 2) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($subscription->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($subscription->status === 'expired') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @elseif($subscription->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($subscription->status === 'suspended') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                    @endif">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                                @if($subscription->auto_renew)
                                    <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Auto
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    @if($subscription->status === 'active')
                                        <button wire:click="renewSubscription({{ $subscription->id }})" 
                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                            Renew
                                        </button>
                                    @endif
                                    @if(in_array($subscription->status, ['active', 'suspended']))
                                        <button wire:click="cancelSubscription({{ $subscription->id }})" 
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Cancel
                                        </button>
                                    @endif
                                    @if($subscription->status === 'active')
                                        <button wire:click="suspendSubscription({{ $subscription->id }}, 'Suspended by admin')" 
                                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                            Suspend
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No subscriptions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($subscriptions->hasPages())
            <div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    {{ $subscriptions->links() }}
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ $subscriptions->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $subscriptions->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $subscriptions->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        {{ $subscriptions->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

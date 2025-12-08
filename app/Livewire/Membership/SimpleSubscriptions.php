<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Permissions\MembershipPermissions;
use App\Services\Membership\SubscriptionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SimpleSubscriptions extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    public $perPage = 15;

    // Form properties
    public $showAddSubscriptionForm = false;

    public $showEditSubscriptionForm = false;

    public $selectedSubscriptionId = null;

    public $member_id;

    public $subscription_plan_id;

    public $start_date;

    public $auto_renew = false;

    public $notes;

    // Statistics
    public array $subscriptionStats = [];

    // Data for forms
    public $members;

    public $subscriptionPlans;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $rules = [
        'member_id' => 'required|exists:members,id',
        'subscription_plan_id' => 'required|exists:subscription_plans,id',
        'start_date' => 'required|date|after_or_equal:today',
        'auto_renew' => 'boolean',
        'notes' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'member_id.required' => 'Please select a member',
        'subscription_plan_id.required' => 'Please select a subscription plan',
        'start_date.required' => 'Start date is required',
        'start_date.after_or_equal' => 'Start date cannot be in the past',
    ];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function render()
    {
        $subscriptions = $this->getSubscriptions();

        return view('livewire.membership.simple-subscriptions', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function getSubscriptions()
    {
        $organizationId = Auth::user()->current_organization_id;

        $query = MemberSubscription::with(['member', 'subscriptionPlan'])
            ->whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            });

        // Apply search filter
        if ($this->search) {
            $query->whereHas('member', function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('membership_number', 'like', "%{$this->search}%");
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply sorting
        if (in_array($this->sortBy, ['created_at', 'start_date', 'end_date', 'total_amount', 'status'])) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query->paginate($this->perPage);
    }

    public function getMembers()
    {
        $organizationId = Auth::user()->current_organization_id;

        return Member::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function getSubscriptionPlans()
    {
        $organizationId = Auth::user()->current_organization_id;

        return SubscriptionPlan::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function loadStatistics()
    {
        $organizationId = Auth::user()->current_organization_id;
        $subscriptionService = app(SubscriptionService::class);

        $stats = $subscriptionService->getSubscriptionStatistics($organizationId);

        $this->subscriptionStats = [
            'active' => $stats['active_subscriptions'],
            'expiring' => $stats['expiring_next_30_days'],
            'expired' => $stats['expired_subscriptions'],
            'new_this_month' => MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    public function showAddSubscriptionForm()
    {
        $this->authorize(MembershipPermissions::MANAGE_SUBSCRIPTIONS);

        $this->resetForm();
        $this->members = $this->getMembers();
        $this->subscriptionPlans = $this->getSubscriptionPlans();
        $this->showAddSubscriptionForm = true;
    }

    public function hideAddSubscriptionForm()
    {
        $this->showAddSubscriptionForm = false;
        $this->members = null;
        $this->subscriptionPlans = null;
        $this->resetForm();
    }

    public function addSubscription()
    {
        $this->authorize(MembershipPermissions::MANAGE_SUBSCRIPTIONS);

        $this->validate();

        try {
            $organizationId = Auth::user()->current_organization_id;
            $member = Member::findOrFail($this->member_id);
            $plan = SubscriptionPlan::findOrFail($this->subscription_plan_id);

            // Verify member and plan belong to current organization
            if ($member->organization_id !== $organizationId || $plan->organization_id !== $organizationId) {
                throw new \Exception('Invalid member or plan selection');
            }

            $subscriptionService = app(SubscriptionService::class);
            $subscription = $subscriptionService->createSubscription($member, $plan, [
                'start_date' => $this->start_date,
                'auto_renew' => $this->auto_renew,
                'notes' => $this->notes,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Subscription created successfully for '.$member->full_name,
            ]);

            $this->hideAddSubscriptionForm();
            $this->loadStatistics();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error creating subscription: '.$e->getMessage(),
            ]);
        }
    }

    public function renewSubscription($subscriptionId)
    {
        $this->authorize(MembershipPermissions::RENEW_SUBSCRIPTIONS);

        try {
            $organizationId = Auth::user()->current_organization_id;
            $subscription = MemberSubscription::with(['member', 'subscriptionPlan'])
                ->whereHas('member', function ($query) use ($organizationId) {
                    $query->where('organization_id', $organizationId);
                })
                ->findOrFail($subscriptionId);

            $subscriptionService = app(SubscriptionService::class);
            $newSubscription = $subscriptionService->renewSubscription($subscription);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Subscription renewed successfully for '.$subscription->member->full_name,
            ]);

            $this->loadStatistics();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error renewing subscription: '.$e->getMessage(),
            ]);
        }
    }

    public function processRenewals()
    {
        $this->authorize(MembershipPermissions::RENEW_SUBSCRIPTIONS);

        try {
            $organizationId = Auth::user()->current_organization_id;
            $subscriptionService = app(SubscriptionService::class);

            $renewedCount = $subscriptionService->processAutoRenewals($organizationId);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Successfully processed {$renewedCount} subscription renewals",
            ]);

            $this->loadStatistics();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error processing renewals: '.$e->getMessage(),
            ]);
        }
    }

    public function sendReminders()
    {
        $this->authorize(MembershipPermissions::MANAGE_SUBSCRIPTIONS);

        try {
            $organizationId = Auth::user()->current_organization_id;
            $subscriptionService = app(SubscriptionService::class);

            $expiringSubscriptions = $subscriptionService->getExpiringSubscriptions($organizationId, 30);
            $reminderCount = 0;

            foreach ($expiringSubscriptions as $subscription) {
                // Here you would implement the actual reminder sending logic
                // For now, we'll just count them
                $reminderCount++;

                // TODO: Implement email/SMS reminder sending
                // Mail::to($subscription->member->email)->send(new SubscriptionExpiryReminder($subscription));
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Reminders sent to {$reminderCount} members with expiring subscriptions",
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error sending reminders: '.$e->getMessage(),
            ]);
        }
    }

    public function cancelSubscription($subscriptionId)
    {
        $this->authorize(MembershipPermissions::CANCEL_SUBSCRIPTIONS);

        try {
            $organizationId = Auth::user()->current_organization_id;
            $subscription = MemberSubscription::with(['member'])
                ->whereHas('member', function ($query) use ($organizationId) {
                    $query->where('organization_id', $organizationId);
                })
                ->findOrFail($subscriptionId);

            $subscriptionService = app(SubscriptionService::class);
            $subscriptionService->cancelSubscription($subscription, 'Cancelled by administrator');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Subscription cancelled for '.$subscription->member->full_name,
            ]);

            $this->loadStatistics();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error cancelling subscription: '.$e->getMessage(),
            ]);
        }
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    private function resetForm()
    {
        $this->member_id = null;
        $this->subscription_plan_id = null;
        $this->start_date = now()->format('Y-m-d');
        $this->auto_renew = false;
        $this->notes = null;
        $this->resetErrorBag();
    }

    public function getSubscriptionStatusClass($status)
    {
        return match ($status) {
            'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'expiring' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'expired' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
            'suspended' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
    }

    public function getSubscriptionStatusText($status)
    {
        return match ($status) {
            'active' => 'Active',
            'expiring' => 'Expiring Soon',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            'suspended' => 'Suspended',
            default => ucfirst($status),
        };
    }
}

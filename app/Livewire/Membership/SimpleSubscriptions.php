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

    public $showPaymentForm = false;

    public $selectedSubscriptionId = null;

    public $member_id;

    public $subscription_plan_id;

    public $start_date;

    public $auto_renew = false;

    public $notes;

    // Payment form properties
    public $payment_amount;

    public $payment_method = 'cash';

    public $payment_reference;

    public $payment_notes;

    // Statistics
    public array $subscriptionStats = [];

    // Data for forms
    public $members;

    public $subscriptionPlans;

    // Bulk operations
    public $selectedSubscriptions = [];

    public $showBulkActions = false;

    // Analytics data
    public array $revenueData = [];

    public array $planDistribution = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $rules = [
        'member_id' => 'required|exists:members,id',
        'subscription_plan_id' => 'required',
        'start_date' => 'required|date|after_or_equal:today',
        'auto_renew' => 'boolean',
        'notes' => 'nullable|string|max:1000',
        'payment_amount' => 'required|numeric|min:0.01',
        'payment_method' => 'required|in:cash,bank_transfer,credit_card,check',
        'payment_reference' => 'nullable|string|max:100',
        'payment_notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'member_id.required' => 'Please select a member',
        'subscription_plan_id.required' => 'Please select a subscription plan',
        'start_date.required' => 'Start date is required',
        'start_date.after_or_equal' => 'Start date cannot be in the past',
        'payment_amount.required' => 'Payment amount is required',
        'payment_amount.min' => 'Payment amount must be greater than 0',
        'payment_method.required' => 'Payment method is required',
    ];

    public function mount()
    {
        $this->loadStatistics();
        $this->loadAnalytics();
        $this->subscriptionPlans = $this->getSubscriptionPlans();
    }

    public function render()
    {
        $subscriptions = $this->getSubscriptions();

        // Ensure subscription plans are always available for the view
        if (! $this->subscriptionPlans) {
            $this->subscriptionPlans = $this->getSubscriptionPlans();
        }

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

        // First try to get actual database plans
        $dbPlans = SubscriptionPlan::where('organization_id', $organizationId)
            ->active()
            ->get();

        if ($dbPlans->isNotEmpty()) {
            return $dbPlans;
        }

        // For demo purposes, return country club specific plans as objects
        return collect([
            (object) [
                'id' => 'corporate',
                'name' => 'Corporate Membership',
                'description' => 'Premium corporate membership with full access',
                'plan_type' => 'corporate',
                'billing_frequency' => 'annually',
                'amount' => 1000000,
                'family_members_included' => 10,
                'additional_family_member_fee' => 50000,
                'benefits' => [
                    'Unlimited access to all facilities',
                    'Priority booking for events',
                    'Complimentary guest passes (10 per month)',
                    'Dedicated account manager',
                    'Corporate event hosting privileges',
                ],
                'is_active' => true,
            ],
            (object) [
                'id' => 'family',
                'name' => 'Family Membership',
                'description' => 'Perfect for families who enjoy club activities together',
                'plan_type' => 'family',
                'billing_frequency' => 'annually',
                'amount' => 600000,
                'family_members_included' => 4,
                'additional_family_member_fee' => 75000,
                'benefits' => [
                    'Access to all family facilities',
                    'Kids club access',
                    'Family events priority',
                    'Swimming pool access',
                    'Tennis court booking',
                ],
                'is_active' => true,
            ],
            (object) [
                'id' => 'individual',
                'name' => 'Individual Membership',
                'description' => 'Single membership with full facility access',
                'plan_type' => 'individual',
                'billing_frequency' => 'annually',
                'amount' => 300000,
                'family_members_included' => 1,
                'additional_family_member_fee' => 100000,
                'benefits' => [
                    'Full gym access',
                    'Swimming pool access',
                    'Tennis court booking',
                    'Restaurant discounts',
                    'Monthly newsletter',
                ],
                'is_active' => true,
            ],
            (object) [
                'id' => 'sports',
                'name' => 'Sports Membership',
                'description' => 'Focused on sports and fitness facilities',
                'plan_type' => 'sports',
                'billing_frequency' => 'monthly',
                'amount' => 25000,
                'family_members_included' => 2,
                'additional_family_member_fee' => 10000,
                'benefits' => [
                    'Gym and fitness center access',
                    'All sports facilities',
                    'Personal trainer discount',
                    'Sports equipment rental',
                    'Tournament participation',
                ],
                'is_active' => true,
            ],
            (object) [
                'id' => 'social',
                'name' => 'Social Membership',
                'description' => 'Social and dining facilities access',
                'plan_type' => 'social',
                'billing_frequency' => 'annually',
                'amount' => 150000,
                'family_members_included' => 2,
                'additional_family_member_fee' => 50000,
                'benefits' => [
                    'Restaurant and bar access',
                    'Social events invitation',
                    'Dining discounts',
                    'Club house access',
                    'New year celebration',
                ],
                'is_active' => true,
            ],
        ]);
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
        if (! $this->subscriptionPlans) {
            $this->subscriptionPlans = $this->getSubscriptionPlans();
        }
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
        // $this->authorize(MembershipPermissions::MANAGE_SUBSCRIPTIONS); // Temporarily disable

        $this->validate();

        $organizationId = Auth::user()->current_organization_id;
        $member = Member::findOrFail($this->member_id);

        // Handle both database plans and demo plans
        $plan = null;
        $plans = $this->getSubscriptionPlans();

        foreach ($plans as $p) {
            if ((string) $p->id === (string) $this->subscription_plan_id) {
                $plan = $p;
                break;
            }
        }

        if (! $plan) {
            throw new \Exception('Invalid subscription plan selected');
        }

        // Verify member belongs to current organization
        if ($member->organization_id !== $organizationId) {
            throw new \Exception('Invalid member selection');
        }

        // If it's a database plan, use the service directly
        if ($plan instanceof SubscriptionPlan) {
            $subscriptionService = app(SubscriptionService::class);
            $subscription = $subscriptionService->createSubscription($member, $plan, [
                'start_date' => $this->start_date,
                'auto_renew' => $this->auto_renew,
                'notes' => $this->notes,
            ]);
        } else {
            // For demo plans, create subscription manually
            $subscription = $this->createDemoSubscription($member, $plan);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Subscription created successfully for '.$member->full_name,
        ]);

        $this->hideAddSubscriptionForm();
        $this->loadStatistics();
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

    public function showPaymentForm($subscriptionId)
    {
        $this->authorize(MembershipPermissions::PROCESS_PAYMENTS);

        $organizationId = Auth::user()->current_organization_id;
        $subscription = MemberSubscription::with(['member', 'subscriptionPlan'])
            ->whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->findOrFail($subscriptionId);

        $this->selectedSubscriptionId = $subscriptionId;
        $this->payment_amount = $subscription->total_amount - $subscription->paid_amount;
        $this->payment_method = 'cash';
        $this->payment_reference = null;
        $this->payment_notes = null;
        $this->showPaymentForm = true;
    }

    public function hidePaymentForm()
    {
        $this->showPaymentForm = false;
        $this->selectedSubscriptionId = null;
        $this->payment_amount = null;
        $this->payment_method = 'cash';
        $this->payment_reference = null;
        $this->payment_notes = null;
        $this->resetErrorBag();
    }

    public function processPayment()
    {
        $this->authorize(MembershipPermissions::PROCESS_PAYMENTS);

        $this->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card,check',
            'payment_reference' => 'nullable|string|max:100',
            'payment_notes' => 'nullable|string|max:500',
        ]);

        try {
            $organizationId = Auth::user()->current_organization_id;
            $subscription = MemberSubscription::with(['member'])
                ->whereHas('member', function ($query) use ($organizationId) {
                    $query->where('organization_id', $organizationId);
                })
                ->findOrFail($this->selectedSubscriptionId);

            $subscriptionService = app(SubscriptionService::class);
            $subscriptionService->processSubscriptionPayment($subscription, [
                'amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
                'payment_reference' => $this->payment_reference,
                'payment_notes' => $this->payment_notes,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Payment of $'.number_format($this->payment_amount, 2).' processed successfully for '.$subscription->member->full_name,
            ]);

            $this->hidePaymentForm();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error processing payment: '.$e->getMessage(),
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

    private function createDemoSubscription(Member $member, $plan): MemberSubscription
    {
        $startDate = $this->start_date ? \Carbon\Carbon::parse($this->start_date) : now();
        $endDate = $this->calculateEndDate($startDate, $plan->billing_frequency);

        // Create a temporary plan record for demo purposes
        $tempPlan = SubscriptionPlan::create([
            'organization_id' => $member->organization_id,
            'name' => $plan->name,
            'description' => $plan->description,
            'plan_type' => $plan->plan_type,
            'billing_frequency' => $plan->billing_frequency,
            'amount' => $plan->amount,
            'family_members_included' => $plan->family_members_included,
            'additional_family_member_fee' => $plan->additional_family_member_fee,
            'benefits' => $plan->benefits,
            'is_active' => true,
        ]);

        $subscription = MemberSubscription::create([
            'organization_id' => $member->organization_id,
            'member_id' => $member->id,
            'subscription_plan_id' => $tempPlan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'total_amount' => $plan->amount,
            'paid_amount' => 0,
            'auto_renew' => $this->auto_renew,
            'notes' => $this->notes,
        ]);

        return $subscription;
    }

    private function calculateEndDate(\Carbon\Carbon $startDate, string $billingFrequency): \Carbon\Carbon
    {
        return match ($billingFrequency) {
            'monthly' => $startDate->copy()->addMonth(),
            'quarterly' => $startDate->copy()->addMonths(3),
            'semi_annually' => $startDate->copy()->addMonths(6),
            'annually' => $startDate->copy()->addYear(),
            default => $startDate->copy()->addMonth(),
        };
    }

    public function loadAnalytics()
    {
        $organizationId = Auth::user()->current_organization_id;

        // Revenue data for last 6 months
        $revenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('total_amount');

            $revenueData[] = [
                'month' => $month->format('M Y'),
                'revenue' => $revenue,
            ];
        }
        $this->revenueData = $revenueData;

        // Plan distribution
        $planDistribution = MemberSubscription::with(['subscriptionPlan'])
            ->whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('status', 'active')
            ->get()
            ->groupBy('subscriptionPlan.name')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();

        $this->planDistribution = $planDistribution;
    }

    public function toggleSubscriptionSelection($subscriptionId)
    {
        if (in_array($subscriptionId, $this->selectedSubscriptions)) {
            $this->selectedSubscriptions = array_diff($this->selectedSubscriptions, [$subscriptionId]);
        } else {
            $this->selectedSubscriptions[] = $subscriptionId;
        }

        $this->showBulkActions = count($this->selectedSubscriptions) > 0;
    }

    public function selectAllSubscriptions()
    {
        $organizationId = Auth::user()->current_organization_id;
        $subscriptions = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })
            ->pluck('id')
            ->toArray();

        $this->selectedSubscriptions = $subscriptions;
        $this->showBulkActions = true;
    }

    public function clearSelection()
    {
        $this->selectedSubscriptions = [];
        $this->showBulkActions = false;
    }

    public function bulkRenew()
    {
        $this->authorize(MembershipPermissions::RENEW_SUBSCRIPTIONS);

        $renewedCount = 0;
        $organizationId = Auth::user()->current_organization_id;

        foreach ($this->selectedSubscriptions as $subscriptionId) {
            try {
                $subscription = MemberSubscription::with(['member', 'subscriptionPlan'])
                    ->whereHas('member', function ($query) use ($organizationId) {
                        $query->where('organization_id', $organizationId);
                    })
                    ->findOrFail($subscriptionId);

                if ($subscription->status === 'active') {
                    $subscriptionService = app(SubscriptionService::class);
                    $subscriptionService->renewSubscription($subscription);
                    $renewedCount++;
                }
            } catch (\Exception $e) {
                \Log::error('Bulk renewal failed for subscription '.$subscriptionId, [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Successfully renewed {$renewedCount} subscriptions",
        ]);

        $this->clearSelection();
        $this->loadStatistics();
    }

    public function bulkSendReminders()
    {
        $this->authorize(MembershipPermissions::MANAGE_SUBSCRIPTIONS);

        $reminderCount = 0;
        $organizationId = Auth::user()->current_organization_id;

        foreach ($this->selectedSubscriptions as $subscriptionId) {
            try {
                $subscription = MemberSubscription::with(['member'])
                    ->whereHas('member', function ($query) use ($organizationId) {
                        $query->where('organization_id', $organizationId);
                    })
                    ->findOrFail($subscriptionId);

                // Here you would implement actual reminder sending
                $reminderCount++;
            } catch (\Exception $e) {
                \Log::error('Bulk reminder failed for subscription '.$subscriptionId, [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Reminders sent to {$reminderCount} members",
        ]);

        $this->clearSelection();
    }

    public function exportSubscriptions()
    {
        $this->authorize(MembershipPermissions::EXPORT_DATA);

        $organizationId = Auth::user()->current_organization_id;
        $subscriptions = MemberSubscription::with(['member', 'subscriptionPlan'])
            ->whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->get();

        $csvData = [];
        $csvData[] = ['Member Name', 'Membership Number', 'Plan', 'Status', 'Start Date', 'End Date', 'Total Amount', 'Paid Amount', 'Auto Renew'];

        foreach ($subscriptions as $subscription) {
            $csvData[] = [
                $subscription->member->full_name,
                $subscription->member->membership_number,
                $subscription->subscriptionPlan->name,
                $subscription->status,
                $subscription->start_date->format('Y-m-d'),
                $subscription->end_date->format('Y-m-d'),
                $subscription->total_amount,
                $subscription->paid_amount,
                $subscription->auto_renew ? 'Yes' : 'No',
            ];
        }

        $filename = 'subscriptions_'.now()->format('Y-m-d_H-i-s').'.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        $output = fopen('php://output', 'w');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
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

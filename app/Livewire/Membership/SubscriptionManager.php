<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Services\Membership\SubscriptionService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionManager extends Component
{
    use WithPagination;

    public ?Member $member = null;

    public ?MemberSubscription $subscription = null;

    public bool $editMode = false;

    public bool $showCreateForm = false;

    public string $search = '';

    public string $status = 'all';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 15;

    // Form fields
    public int $subscription_plan_id = 0;

    public int $member_id = 0;

    public string $start_date = '';

    public string $end_date = '';

    public bool $auto_renew = false;

    public string $notes = '';

    public float $discount_percentage = 0;

    public float $discount_amount = 0;

    public array $statuses = [
        'all' => 'All Subscriptions',
        'active' => 'Active',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        'suspended' => 'Suspended',
    ];

    public array $sortOptions = [
        'created_at' => 'Date Created',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'total_amount' => 'Amount',
        'status' => 'Status',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
    ];

    public function mount(?Member $member = null): void
    {
        $this->member = $member;

        // $this->authorize('membership.manage_subscriptions');
    }

    public function render(SubscriptionService $subscriptionService)
    {
        $organizationId = auth()->user()->current_organization_id;

        if (! $organizationId) {
            throw new \Exception('No organization context found');
        }

        $subscriptions = $subscriptionService->getSubscriptions(
            organizationId: $organizationId,
            memberId: $this->member?->id,
            filters: [
                'search' => $this->search,
                'status' => $this->status !== 'all' ? $this->status : null,
                'sort_by' => $this->sortBy,
                'sort_direction' => $this->sortDirection,
                'per_page' => $this->perPage,
            ]
        );

        // Debug: Log subscription data
        if (app()->environment('testing')) {
            foreach ($subscriptions->items() as $sub) {
                \Log::info('Subscription: '.$sub->id.', Plan ID: '.$sub->subscription_plan_id.', Plan: '.($sub->subscriptionPlan ? $sub->subscriptionPlan->name : 'NULL'));
            }
        }

        return view('livewire.membership.subscription-manager', [
            'subscriptions' => $subscriptions,
            'subscriptionPlans' => SubscriptionPlan::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'statistics' => $subscriptionService->getSubscriptionStatistics($organizationId),
        ]);
    }

    public function createSubscription(SubscriptionService $subscriptionService): void
    {
        if (app()->environment('testing')) {
            \Log::info('createSubscription method called');
        }

        $organizationId = auth()->user()->operating_organization_id;

        if (! $organizationId) {
            $this->dispatch('show-notification', message: 'Error: No organization context found', type: 'error');

            return;
        }

        $rules = [
            'subscription_plan_id' => 'required|exists:subscription_plans,id,organization_id,'.auth()->user()->current_organization_id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'discount_percentage' => 'numeric|min:0|max:100',
            'discount_amount' => 'numeric|min:0',
        ];

        // Add member validation if not pre-selected
        if (! $this->member) {
            $rules['member_id'] = 'required|exists:members,id,organization_id,'.auth()->user()->current_organization_id;
        }

        try {
            $this->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (app()->environment('testing')) {
                \Log::error('Validation failed: '.json_encode($e->errors()));
            }
            $this->dispatch('show-notification', message: 'Validation failed: '.implode(', ', $e->errors()->all()), type: 'error');

            return;
        } catch (\Exception $e) {
            if (app()->environment('testing')) {
                \Log::error('General error: '.json_encode(['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]));
            }
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');

            return;
        }

        try {
            $plan = SubscriptionPlan::findOrFail($this->subscription_plan_id);

            if ($plan->organization_id !== $organizationId) {
                throw new \Exception('Invalid subscription plan');
            }

            // Get member either from pre-selected or from form
            $memberForSubscription = $this->member;
            if (! $memberForSubscription || ($memberForSubscription && ! $memberForSubscription->exists)) {
                $memberForSubscription = Member::findOrFail($this->member_id);
                if ($memberForSubscription->organization_id !== $organizationId) {
                    throw new \Exception('Invalid member');
                }
            }

            $subscriptionData = [
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'auto_renew' => $this->auto_renew,
                'notes' => $this->notes,
                'discount_percentage' => $this->discount_percentage,
                'discount_amount' => $this->discount_amount,
            ];

            try {
                $subscription = $subscriptionService->createSubscription($memberForSubscription, $plan, $subscriptionData);

                $this->resetForm();
                $this->showCreateForm = false;

                $this->dispatch('subscription-created', subscriptionId: $subscription->id);
                $this->dispatch('show-notification', message: 'Subscription created successfully', type: 'success');
            } catch (\Exception $e) {
                $this->dispatch('show-notification', message: 'Service error: '.$e->getMessage(), type: 'error');
            }

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function renewSubscription(int $subscriptionId, SubscriptionService $subscriptionService): void
    {
        try {
            $organizationId = auth()->user()->current_organization_id;

            if (! $organizationId) {
                throw new \Exception('No organization context found');
            }

            $subscription = MemberSubscription::findOrFail($subscriptionId);

            if ($subscription->organization_id !== $organizationId) {
                abort(403);
            }

            $renewedSubscription = $subscriptionService->renewSubscription($subscription, [
                'auto_renew' => $subscription->auto_renew,
            ]);

            $this->dispatch('subscription-renewed', subscriptionId: $renewedSubscription->id);
            $this->dispatch('show-notification', message: 'Subscription renewed successfully', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function cancelSubscription(int $subscriptionId, SubscriptionService $subscriptionService): void
    {
        $this->dispatch('open-cancel-modal', subscriptionId: $subscriptionId);
    }

    public function confirmCancelSubscription(int $subscriptionId, string $reason, SubscriptionService $subscriptionService): void
    {
        try {
            $organizationId = auth()->user()->current_organization_id;

            if (! $organizationId) {
                throw new \Exception('No organization context found');
            }

            $subscription = MemberSubscription::findOrFail($subscriptionId);

            if ($subscription->organization_id !== $organizationId) {
                abort(403);
            }

            $subscriptionService->cancelSubscription($subscription, $reason);

            $this->dispatch('subscription-cancelled', subscriptionId: $subscriptionId);
            $this->dispatch('show-notification', message: 'Subscription cancelled successfully', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function suspendSubscription(int $subscriptionId, string $reason, SubscriptionService $subscriptionService): void
    {
        try {
            $subscription = MemberSubscription::findOrFail($subscriptionId);

            if ($subscription->organization_id !== auth()->user()->current_organization_id) {
                abort(403);
            }

            $subscription->update([
                'status' => 'suspended',
                'notes' => ($subscription->notes ?? '')."\n\nSuspended: ".$reason,
            ]);

            $this->dispatch('subscription-suspended', subscriptionId: $subscriptionId);
            $this->dispatch('show-notification', message: 'Subscription suspended successfully', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function processAutoRenewals(SubscriptionService $subscriptionService): void
    {
        try {
            $organizationId = auth()->user()->current_organization_id;

            if (! $organizationId) {
                throw new \Exception('No organization context found');
            }

            $processedCount = $subscriptionService->processAutoRenewals($organizationId);

            $this->dispatch('auto-renewals-processed', count: $processedCount);
            $this->dispatch('show-notification',
                message: "Processed {$processedCount} auto-renewals",
                type: 'success'
            );

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function showCreateForm(): void
    {
        $this->showCreateForm = true;
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addYear()->format('Y-m-d');
    }

    public function hideCreateForm(): void
    {
        $this->showCreateForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->subscription_plan_id = 0;
        $this->member_id = 0;
        $this->start_date = '';
        $this->end_date = '';
        $this->auto_renew = false;
        $this->notes = '';
        $this->discount_percentage = 0;
        $this->discount_amount = 0;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    #[On('subscription-created')]
    #[On('subscription-renewed')]
    #[On('subscription-cancelled')]
    #[On('subscription-suspended')]
    public function refreshSubscriptions(): void
    {
        $this->resetPage();
    }

    public function getExpiringSubscriptionsProperty(): \Illuminate\Support\Collection
    {
        $organizationId = auth()->user()->operating_organization_id;

        if (! $organizationId) {
            return collect();
        }

        return app(SubscriptionService::class)->getExpiringSubscriptions(
            $organizationId,
            30 // 30 days
        );
    }

    public function calculateTotalAmount(): float
    {
        if ($this->subscription_plan_id === 0) {
            return 0;
        }

        $plan = SubscriptionPlan::find($this->subscription_plan_id);
        if (! $plan) {
            return 0;
        }

        // Verify plan belongs to current organization
        $organizationId = auth()->user()->operating_organization_id;
        if (! $organizationId || $plan->organization_id !== $organizationId) {
            return 0;
        }

        $total = $plan->amount;

        // Apply percentage discount
        if ($this->discount_percentage > 0) {
            $total -= ($total * $this->discount_percentage / 100);
        }

        // Apply fixed amount discount
        if ($this->discount_amount > 0) {
            $total -= $this->discount_amount;
        }

        return max(0, $total);
    }
}

<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\StoreSubscriptionRequest;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Membership\Member;
use App\Services\Membership\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Display a listing of subscriptions.
     */
    public function index(Request $request): View
    {
        $organizationId = Auth::user()->current_organization_id;
        $filters = $request->only(['status', 'plan_type', 'member_search']);
        
        $query = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->with(['member', 'subscriptionPlan']);
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['plan_type'])) {
            $query->whereHas('subscriptionPlan', function ($q) use ($filters) {
                $q->where('plan_type', $filters['plan_type']);
            });
        }
        
        if (isset($filters['member_search'])) {
            $query->whereHas('member', function ($q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['member_search'] . '%')
                  ->orWhere('last_name', 'like', '%' . $filters['member_search'] . '%')
                  ->orWhere('membership_number', 'like', '%' . $filters['member_search'] . '%');
            });
        }
        
        $subscriptions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('membership.subscriptions.index', compact('subscriptions', 'filters'));
    }

    /**
     * Show the form for creating a new subscription.
     */
    public function create(Request $request): View
    {
        $organizationId = Auth::user()->current_organization_id;
        $memberId = $request->get('member_id');
        
        $member = Member::where('organization_id', $organizationId)
            ->findOrFail($memberId);
        
        $plans = SubscriptionPlan::where('organization_id', $organizationId)
            ->active()
            ->orderBy('name')
            ->get();
        
        return view('membership.subscriptions.create', compact('member', 'plans'));
    }

    /**
     * Store a newly created subscription in storage.
     */
    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        
        $member = Member::where('organization_id', $organizationId)
            ->findOrFail($request->member_id);
        
        $plan = SubscriptionPlan::where('organization_id', $organizationId)
            ->findOrFail($request->subscription_plan_id);
        
        $subscription = $this->subscriptionService->createSubscription($member, $plan, $request->validated());
        
        return redirect()
            ->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription created successfully.');
    }

    /**
     * Display the specified subscription.
     */
    public function show(MemberSubscription $subscription): View
    {
        $this->authorize('view', $subscription);
        
        $subscription->load(['member.familyMembers', 'subscriptionPlan', 'fees']);
        
        return view('membership.subscriptions.show', compact('subscription'));
    }

    /**
     * Show the form for editing the specified subscription.
     */
    public function edit(MemberSubscription $subscription): View
    {
        $this->authorize('update', $subscription);
        
        $subscription->load(['member', 'subscriptionPlan']);
        
        return view('membership.subscriptions.edit', compact('subscription'));
    }

    /**
     * Update the specified subscription in storage.
     */
    public function update(Request $request, MemberSubscription $subscription): RedirectResponse
    {
        $this->authorize('update', $subscription);
        
        $request->validate([
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $subscription->update($request->only(['auto_renew', 'notes']));
        
        return redirect()
            ->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription updated successfully.');
    }

    /**
     * Remove the specified subscription from storage.
     */
    public function destroy(MemberSubscription $subscription): RedirectResponse
    {
        $this->authorize('delete', $subscription);
        
        $subscription->delete();
        
        return redirect()
            ->route('subscriptions.index')
            ->with('success', 'Subscription deleted successfully.');
    }

    /**
     * Renew a subscription.
     */
    public function renew(Request $request, MemberSubscription $subscription): RedirectResponse
    {
        $this->authorize('update', $subscription);
        
        $request->validate([
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $renewedSubscription = $this->subscriptionService->renewSubscription($subscription, $request->only(['auto_renew', 'notes']));
        
        return redirect()
            ->route('subscriptions.show', $renewedSubscription)
            ->with('success', 'Subscription renewed successfully.');
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Request $request, MemberSubscription $subscription): RedirectResponse
    {
        $this->authorize('update', $subscription);
        
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        $this->subscriptionService->cancelSubscription($subscription, $request->reason);
        
        return redirect()
            ->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription cancelled successfully.');
    }

    /**
     * Suspend a subscription.
     */
    public function suspend(Request $request, MemberSubscription $subscription): RedirectResponse
    {
        $this->authorize('update', $subscription);
        
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        $this->subscriptionService->suspendSubscription($subscription, $request->reason);
        
        return redirect()
            ->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription suspended successfully.');
    }

    /**
     * Get subscription statistics.
     */
    public function statistics(): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $statistics = $this->subscriptionService->getSubscriptionStatistics($organizationId);
        
        return response()->json($statistics);
    }

    /**
     * Get expiring subscriptions.
     */
    public function expiring(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $days = $request->get('days', 30);
        
        $subscriptions = $this->subscriptionService->getExpiringSubscriptions($organizationId, $days);
        
        return response()->json($subscriptions);
    }

    /**
     * Process auto-renewals.
     */
    public function processAutoRenewals(): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $renewedCount = $this->subscriptionService->processAutoRenewals($organizationId);
        
        return response()->json([
            'success' => true,
            'renewed_count' => $renewedCount,
            'message' => "Processed {$renewedCount} auto-renewals.",
        ]);
    }
}

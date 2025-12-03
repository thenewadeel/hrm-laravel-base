<?php

namespace App\Services\Membership;

use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Membership\Member;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Create a new subscription for a member
     */
    public function createSubscription(Member $member, SubscriptionPlan $plan, array $options = []): MemberSubscription
    {
        return DB::transaction(function () use ($member, $plan, $options) {
            $startDate = isset($options['start_date']) 
                ? Carbon::parse($options['start_date']) 
                : now();
            
            $endDate = $this->calculateEndDate($startDate, $plan->billing_frequency);
            $totalAmount = $this->calculateSubscriptionAmount($plan, $member->familyMembers->count(), $options);
            
            // Deactivate existing active subscriptions
            $this->deactivateExistingSubscriptions($member);
            
            return MemberSubscription::create([
                'organization_id' => $member->organization_id,
                'member_id' => $member->id,
                'subscription_plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'auto_renew' => $options['auto_renew'] ?? false,
                'notes' => $options['notes'] ?? null,
            ]);
        });
    }

    /**
     * Renew an existing subscription
     */
    public function renewSubscription(MemberSubscription $subscription, array $options = []): MemberSubscription
    {
        return DB::transaction(function () use ($subscription, $options) {
            $plan = $subscription->subscriptionPlan;
            $member = $subscription->member;
            
            $newStartDate = $subscription->end_date->copy()->addDay();
            $newEndDate = $this->calculateEndDate($newStartDate, $plan->billing_frequency);
            $totalAmount = $this->calculateSubscriptionAmount($plan, $member->familyMembers->count(), $options);
            
            // Mark old subscription as expired
            $subscription->update(['status' => 'expired']);
            
            return MemberSubscription::create([
                'organization_id' => $member->organization_id,
                'member_id' => $member->id,
                'subscription_plan_id' => $plan->id,
                'start_date' => $newStartDate,
                'end_date' => $newEndDate,
                'status' => 'active',
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'auto_renew' => $options['auto_renew'] ?? $subscription->auto_renew,
                'notes' => ($options['notes'] ?? '') . "\n\nRenewed from subscription #{$subscription->id}",
            ]);
        });
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(MemberSubscription $subscription, string $reason = null): bool
    {
        return DB::transaction(function () use ($subscription, $reason) {
            $subscription->update([
                'status' => 'cancelled',
                'notes' => $subscription->notes . "\n\nCancelled: " . ($reason ?? 'No reason provided') . " - " . now()->toDateTimeString()
            ]);
            
            return true;
        });
    }

    /**
     * Suspend a subscription
     */
    public function suspendSubscription(MemberSubscription $subscription, string $reason = null): bool
    {
        return DB::transaction(function () use ($subscription, $reason) {
            $subscription->update([
                'status' => 'suspended',
                'notes' => $subscription->notes . "\n\nSuspended: " . ($reason ?? 'No reason provided') . " - " . now()->toDateTimeString()
            ]);
            
            return true;
        });
    }

    /**
     * Process subscription payment
     */
    public function processSubscriptionPayment(MemberSubscription $subscription, array $paymentData): bool
    {
        return DB::transaction(function () use ($subscription, $paymentData) {
            $subscription->update([
                'paid_amount' => $subscription->paid_amount + $paymentData['amount'],
                'notes' => $subscription->notes . "\n\nPayment: " . $paymentData['amount'] . " via " . ($paymentData['method'] ?? 'unknown') . " - " . now()->toDateTimeString()
            ]);
            
            // If fully paid, ensure status is active
            if ($subscription->paid_amount >= $subscription->total_amount) {
                $subscription->update(['status' => 'active']);
            }
            
            return true;
        });
    }

    /**
     * Process auto-renewals for due subscriptions
     */
    public function processAutoRenewals(int $organizationId): int
    {
        $renewedCount = 0;
        
        $dueSubscriptions = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('auto_renew', true)
            ->where('status', 'active')
            ->where('end_date', '<=', now()->addDays(7)) // Due within 7 days
            ->where('end_date', '>', now()) // Not expired yet
            ->with(['member', 'subscriptionPlan'])
            ->get();
        
        foreach ($dueSubscriptions as $subscription) {
            try {
                $this->renewSubscription($subscription, ['auto_renew' => true]);
                $renewedCount++;
            } catch (\Exception $e) {
                // Log error but continue with others
                \Log::error('Auto-renewal failed for subscription ' . $subscription->id, [
                    'error' => $e->getMessage(),
                    'subscription_id' => $subscription->id,
                    'member_id' => $subscription->member_id,
                ]);
            }
        }
        
        return $renewedCount;
    }

    /**
     * Update expired subscriptions
     */
    public function updateExpiredSubscriptions(int $organizationId): int
    {
        return MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Get subscriptions expiring soon
     */
    public function getExpiringSubscriptions(int $organizationId, int $days = 30)
    {
        return MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('status', 'active')
            ->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>', now())
            ->with(['member', 'subscriptionPlan'])
            ->orderBy('end_date')
            ->get();
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStatistics(int $organizationId): array
    {
        $total = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })->count();
        
        $active = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })->active()->count();
        
        $expired = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })->where('status', 'expired')->count();
        
        $cancelled = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })->where('status', 'cancelled')->count();
        
        $suspended = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })->where('status', 'suspended')->count();
        
        $expiringNext30Days = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->expiringSoon(30)
            ->count();
        
        $autoRenewEnabled = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('auto_renew', true)
            ->where('status', 'active')
            ->count();
        
        $totalRevenue = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');
        
        $outstandingRevenue = MemberSubscription::whereHas('member', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('paid_amount', '<', \DB::raw('total_amount'))
            ->sum(\DB::raw('total_amount - paid_amount'));
        
        return [
            'total_subscriptions' => $total,
            'active_subscriptions' => $active,
            'expired_subscriptions' => $expired,
            'cancelled_subscriptions' => $cancelled,
            'suspended_subscriptions' => $suspended,
            'expiring_next_30_days' => $expiringNext30Days,
            'auto_renew_enabled' => $autoRenewEnabled,
            'total_revenue' => $totalRevenue,
            'outstanding_revenue' => $outstandingRevenue,
            'renewal_rate' => $total > 0 ? round(($autoRenewEnabled / $active) * 100, 2) : 0,
        ];
    }

    /**
     * Calculate end date based on billing frequency
     */
    private function calculateEndDate(Carbon $startDate, string $billingFrequency): Carbon
    {
        return match($billingFrequency) {
            'monthly' => $startDate->copy()->addMonth(),
            'quarterly' => $startDate->copy()->addMonths(3),
            'semi_annually' => $startDate->copy()->addMonths(6),
            'annually' => $startDate->copy()->addYear(),
            default => $startDate->copy()->addMonth(),
        };
    }

    /**
     * Calculate subscription amount based on plan and family members
     */
    private function calculateSubscriptionAmount(SubscriptionPlan $plan, int $familyMemberCount, array $options = []): float
    {
        $baseAmount = $plan->amount;
        $additionalMembers = max(0, $familyMemberCount - $plan->family_members_included);
        $additionalFee = $additionalMembers * $plan->additional_family_member_fee;
        
        $totalAmount = $baseAmount + $additionalFee;
        
        // Apply discounts if provided
        if (isset($options['discount_percentage'])) {
            $totalAmount = $totalAmount * (1 - ($options['discount_percentage'] / 100));
        }
        
        if (isset($options['discount_amount'])) {
            $totalAmount = max(0, $totalAmount - $options['discount_amount']);
        }
        
        return round($totalAmount, 2);
    }

    /**
     * Deactivate existing active subscriptions for a member
     */
    private function deactivateExistingSubscriptions(Member $member): void
    {
        $member->subscriptions()
            ->where('status', 'active')
            ->update(['status' => 'expired']);
    }
}
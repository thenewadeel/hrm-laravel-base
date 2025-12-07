<?php

namespace App\Livewire\Membership;

use App\Services\Membership\CardPrintingService;
use App\Services\Membership\FeeService;
use App\Services\Membership\MembershipService;
use App\Services\Membership\SubscriptionService;
use Livewire\Component;

class MembershipDashboard extends Component
{
    public string $period = 'month'; // week, month, quarter, year

    public array $periods = [
        'week' => 'Last 7 Days',
        'month' => 'Last 30 Days',
        'quarter' => 'Last 3 Months',
        'year' => 'Last Year',
    ];

    public function mount(): void
    {
        $this->authorize('membership.view_dashboard');
    }

    public function render(
        MembershipService $membershipService,
        SubscriptionService $subscriptionService,
        FeeService $feeService,
        CardPrintingService $cardService
    ) {
        // Get the first organization the user is attached to for testing
        $userOrgs = auth()->user()->organizations()->pluck('organizations.id');
        $organizationId = auth()->user()->current_organization_id ??
                         auth()->user()->operating_organization_id ??
                         $userOrgs->first() ?? null;

        if (! $organizationId) {
            // Return empty data if no organization
            return view('livewire.membership.membership-dashboard', [
                'memberStats' => $this->getDefaultMemberStats(),
                'subscriptionStats' => $this->getDefaultSubscriptionStats(),
                'feeStats' => $this->getDefaultFeeStats(),
                'cardStats' => $this->getDefaultCardStats(),
                'recentMembers' => collect(),
                'growthMetrics' => $this->getDefaultGrowthMetrics(),
            ]);
        }

        // Get statistics from all services
        $memberStats = $membershipService->getMemberStatistics($organizationId);
        $subscriptionStats = $subscriptionService->getSubscriptionStatistics($organizationId);
        $feeStats = $feeService->getFeeStatistics($organizationId);
        $cardStats = $cardService->getCardStatistics($organizationId);

        // Get recent activity
        $recentMembers = $membershipService->getRecentMembers($organizationId, 5);

        // Calculate growth metrics
        $growthMetrics = $this->calculateGrowthMetrics($organizationId, $membershipService, $subscriptionService);

        return view('livewire.membership.membership-dashboard', [
            'memberStats' => $memberStats,
            'subscriptionStats' => $subscriptionStats,
            'feeStats' => $feeStats,
            'cardStats' => $cardStats,
            'recentMembers' => $recentMembers,
            'growthMetrics' => $growthMetrics,
        ]);
    }

    private function calculateGrowthMetrics(
        int $organizationId,
        MembershipService $membershipService,
        SubscriptionService $subscriptionService
    ): array {
        $now = now();
        $previousPeriod = match ($this->period) {
            'week' => $now->copy()->subDays(7),
            'month' => $now->copy()->subDays(30),
            'quarter' => $now->copy()->subMonths(3),
            'year' => $now->copy()->subYear(),
            default => $now->copy()->subDays(30),
        };

        $currentPeriodStart = match ($this->period) {
            'week' => $now->copy()->subDays(7),
            'month' => $now->copy()->subDays(30),
            'quarter' => $now->copy()->subMonths(3),
            'year' => $now->copy()->subYear(),
            default => $now->copy()->subDays(30),
        };

        // Get member counts for current and previous period
        $currentMembers = $membershipService->getMembersByDateRange($organizationId, $currentPeriodStart, $now);
        $previousMembers = $membershipService->getMembersByDateRange($organizationId, $previousPeriod, $currentPeriodStart);

        // Get subscription revenue for current and previous period
        $currentRevenue = $subscriptionService->getRevenueByDateRange($organizationId, $currentPeriodStart, $now);
        $previousRevenue = $subscriptionService->getRevenueByDateRange($organizationId, $previousPeriod, $currentPeriodStart);

        // Calculate growth percentages
        $memberGrowth = $previousMembers > 0 ? (($currentMembers - $previousMembers) / $previousMembers) * 100 : 0;
        $revenueGrowth = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        return [
            'member_growth' => round($memberGrowth, 2),
            'revenue_growth' => round($revenueGrowth, 2),
            'new_members' => $currentMembers,
            'revenue' => $currentRevenue,
        ];
    }

    public function updatedPeriod(): void
    {
        // Livewire will automatically re-render when properties are updated
    }

    public function getQuickActionsProperty(): array
    {
        return [
            [
                'title' => 'Add New Member',
                'description' => 'Register a new member in the system',
                'icon' => 'user-plus',
                'color' => 'blue',
                'route' => route('members.create'),
            ],
            [
                'title' => 'Create Subscription',
                'description' => 'Set up a new subscription plan',
                'icon' => 'credit-card',
                'color' => 'green',
                'route' => route('subscriptions.create'),
            ],
            [
                'title' => 'Generate Cards',
                'description' => 'Print membership cards',
                'icon' => 'id-card',
                'color' => 'purple',
                'route' => route('cards.index'),
            ],
            [
                'title' => 'Process Fees',
                'description' => 'Manage member fees and payments',
                'icon' => 'dollar-sign',
                'color' => 'yellow',
                'route' => route('fees.index'),
            ],
        ];
    }

    public function getExpiringMembersProperty(): \Illuminate\Support\Collection
    {
        // Get the same organization ID as render method
        $userOrgs = auth()->user()->organizations()->pluck('organizations.id');
        $organizationId = auth()->user()->current_organization_id ??
                         auth()->user()->operating_organization_id ??
                         $userOrgs->first() ?? null;

        if (! $organizationId) {
            return collect();
        }

        return app(MembershipService::class)->getExpiringMembers(
            $organizationId,
            30 // 30 days
        );
    }

    public function getExpiringSubscriptionsProperty(): \Illuminate\Support\Collection
    {
        // Get the same organization ID as render method
        $userOrgs = auth()->user()->organizations()->pluck('organizations.id');
        $organizationId = auth()->user()->current_organization_id ??
                         auth()->user()->operating_organization_id ??
                         $userOrgs->first() ?? null;

        if (! $organizationId) {
            return collect();
        }

        return app(SubscriptionService::class)->getExpiringSubscriptions(
            $organizationId,
            30 // 30 days
        );
    }

    public function getOverdueFeesProperty(): \Illuminate\Support\Collection
    {
        // Get same organization ID as render method
        $userOrgs = auth()->user()->organizations()->pluck('organizations.id');
        $organizationId = auth()->user()->current_organization_id ??
                         auth()->user()->operating_organization_id ??
                         $userOrgs->first() ?? null;

        if (! $organizationId) {
            return collect();
        }

        return app(FeeService::class)->getOverdueFees($organizationId);
    }

    public function getAlertsProperty(): array
    {
        $alerts = [];

        // Check for expiring members
        if ($this->expiringMembers->count() > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Members Expiring Soon',
                'message' => "{$this->expiringMembers->count()} members will expire in the next 30 days",
                'action' => route('members.index', ['status' => 'expiring']),
            ];
        }

        // Check for overdue fees
        if ($this->overdueFees->count() > 0) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Overdue Fees',
                'message' => "{$this->overdueFees->count()} fees are overdue",
                'action' => route('fees.index', ['status' => 'overdue']),
            ];
        }

        // Check for expiring subscriptions
        if ($this->expiringSubscriptions->count() > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Subscriptions Expiring Soon',
                'message' => "{$this->expiringSubscriptions->count()} subscriptions will expire in the next 30 days",
                'action' => route('subscriptions.index', ['status' => 'expiring']),
            ];
        }

        return $alerts;
    }

    private function getDefaultMemberStats(): array
    {
        return [
            'total_members' => 0,
            'active_members' => 0,
            'inactive_members' => 0,
            'suspended_members' => 0,
            'expired_members' => 0,
            'expiring_next_30_days' => 0,
            'members_with_family' => 0,
            'activation_rate' => 0,
        ];
    }

    private function getDefaultSubscriptionStats(): array
    {
        return [
            'total_subscriptions' => 0,
            'active_subscriptions' => 0,
            'expired_subscriptions' => 0,
            'cancelled_subscriptions' => 0,
            'suspended_subscriptions' => 0,
            'expiring_next_30_days' => 0,
            'auto_renew_enabled' => 0,
            'total_revenue' => 0,
            'outstanding_revenue' => 0,
            'renewal_rate' => 0,
        ];
    }

    private function getDefaultFeeStats(): array
    {
        return [
            'total_fees' => 0,
            'pending_fees' => 0,
            'paid_fees' => 0,
            'waived_fees' => 0,
            'overdue_fees' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'outstanding_amount' => 0,
            'collection_rate' => 0,
            'fees_by_type' => [],
            'monthly_trend' => [],
        ];
    }

    private function getDefaultCardStats(): array
    {
        return [
            'total_cards_generated' => 0,
            'cards_today' => 0,
            'total_members' => 0,
            'active_members' => 0,
            'members_with_photos' => 0,
            'photo_completion_rate' => 0,
            'total_family_members' => 0,
            'active_family_members' => 0,
            'total_cards_needed' => 0,
        ];
    }

    private function getDefaultGrowthMetrics(): array
    {
        return [
            'member_growth' => 0,
            'revenue_growth' => 0,
            'new_members' => 0,
            'revenue' => 0,
        ];
    }
}

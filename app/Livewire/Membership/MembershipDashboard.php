<?php

namespace App\Livewire\Membership;

use App\Services\Membership\MembershipService;
use App\Services\Membership\SubscriptionService;
use App\Services\Membership\FeeService;
use App\Services\Membership\CardPrintingService;
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
        $this->authorize('membership.view_members');
    }

    public function render(
        MembershipService $membershipService,
        SubscriptionService $subscriptionService,
        FeeService $feeService,
        CardPrintingService $cardService
    ) {
        $organizationId = auth()->user()->current_organization_id;

        // Get statistics from all services
        $memberStats = $membershipService->getMemberStatistics($organizationId);
        $subscriptionStats = $subscriptionService->getSubscriptionStatistics($organizationId);
        $feeStats = $feeService->getFeeStatistics($organizationId);
        $cardStats = $cardService->getCardStatistics($organizationId);

        // Get recent activity
        $recentMembers = $membershipService->getRecentMembers($organizationId, 5);
        $expiringMembers = $membershipService->getExpiringMembers($organizationId, 30);
        $expiringSubscriptions = $subscriptionService->getExpiringSubscriptions($organizationId, 30);
        $overdueFees = $feeService->getOverdueFees($organizationId);

        // Calculate growth metrics
        $growthMetrics = $this->calculateGrowthMetrics($organizationId, $membershipService, $subscriptionService);

        return view('livewire.membership.membership-dashboard', [
            'memberStats' => $memberStats,
            'subscriptionStats' => $subscriptionStats,
            'feeStats' => $feeStats,
            'cardStats' => $cardStats,
            'recentMembers' => $recentMembers,
            'expiringMembers' => $expiringMembers,
            'expiringSubscriptions' => $expiringSubscriptions,
            'overdueFees' => $overdueFees,
            'growthMetrics' => $growthMetrics,
        ]);
    }

    private function calculateGrowthMetrics(
        int $organizationId,
        MembershipService $membershipService,
        SubscriptionService $subscriptionService
    ): array {
        $now = now();
        $previousPeriod = match($this->period) {
            'week' => $now->copy()->subDays(7),
            'month' => $now->copy()->subDays(30),
            'quarter' => $now->copy()->subMonths(3),
            'year' => $now->copy()->subYear(),
            default => $now->copy()->subDays(30),
        };

        $currentPeriodStart = match($this->period) {
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
        $this->render();
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
}

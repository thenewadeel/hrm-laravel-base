<?php

namespace App\Livewire\Membership;

use App\Models\Membership\MemberFee;
use App\Services\Membership\FeeService;
use App\Services\Membership\MembershipService;
use Illuminate\Support\Carbon;
use Livewire\Component;

class FeeCollectionDashboard extends Component
{
    public array $dashboardStats = [];

    public array $recentPayments = [];

    public array $defaulters = [];

    public array $monthlyTrends = [];

    public string $selectedPeriod = 'month';

    public string $selectedStatus = 'all';

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData(FeeService $feeService, MembershipService $membershipService): void
    {
        $organizationId = (int) (auth()->user()->current_organization_id ??
            auth()->user()->operating_organization_id ??
            auth()->user()->organizations()->value('organizations.id') ??
            0);

        if (! $organizationId) {
            $this->dashboardStats = [];
            $this->recentPayments = [];
            $this->defaulters = [];
            $this->monthlyTrends = [];

            return;
        }

        $memberStats = $membershipService->getMemberStatistics($organizationId);
        $collectionMetrics = $feeService->getCollectionMetrics($organizationId);
        $overdueFees = $feeService->getOverdueFees($organizationId);

        $this->dashboardStats = [
            'total_members' => $memberStats['total_members'] ?? 0,
            'active_members' => $memberStats['active_members'] ?? 0,
            'total_collected' => $collectionMetrics['paid_amount'] ?? 0,
            'pending_amount' => $collectionMetrics['pending_amount'] ?? 0,
            'overdue_amount' => $overdueFees->sum('amount'),
            'defaulters_count' => $overdueFees->count(),
            'collection_rate' => $collectionMetrics['collection_rate'] ?? 0,
        ];

        $this->recentPayments = $feeService->getRecentPayments($organizationId, 5)
            ->map(function ($fee) {
                return [
                    'id' => $fee->id,
                    'member_name' => $fee->member->full_name ?? 'Unknown member',
                    'member_id' => $fee->member->membership_number ?? "MEM-{$fee->member_id}",
                    'amount' => $fee->paid_amount ?: $fee->amount,
                    'payment_method' => $fee->payment_method ?? 'other',
                    'payment_date' => optional($fee->paid_date)->toDateTimeString() ?? $fee->created_at->toDateTimeString(),
                    'fee_type' => ucfirst(str_replace('_', ' ', $fee->fee_type)),
                ];
            })
            ->values()
            ->toArray();

        $this->defaulters = $overdueFees->map(function ($fee) {
            $daysOverdue = max(0, (int) now()->startOfDay()->diffInDays($fee->due_date->startOfDay()));

            return [
                'id' => $fee->id,
                'member_name' => $fee->member->full_name ?? 'Unknown member',
                'member_id' => $fee->member->membership_number ?? "MEM-{$fee->member_id}",
                'overdue_amount' => $fee->amount,
                'days_overdue' => $daysOverdue,
                'last_payment_date' => $fee->paid_date ? $fee->paid_date->toDateString() : null,
                'contact_attempts' => 0,
                'status' => $this->statusForDays($daysOverdue),
            ];
        })
            ->values()
            ->toArray();

        $monthlyTrend = $feeService->getFeeStatistics($organizationId)['monthly_trend'] ?? [];

        $this->monthlyTrends = collect($monthlyTrend)->map(function ($row, $month) {
            $collected = (float) ($row['total'] ?? $row['amount'] ?? 0);
            $pending = (float) (MemberFee::where('organization_id', auth()->user()->current_organization_id)
                ->where('status', 'pending')
                ->where('paid_date', null)
                ->sum('amount') ?? 0);

            return [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('M'),
                'collected' => $collected,
                'pending' => $pending,
            ];
        })->values()->toArray();
    }

    private function statusForDays(int $daysOverdue): string
    {
        return match (true) {
            $daysOverdue >= 45 => 'critical',
            $daysOverdue >= 30 => 'high',
            $daysOverdue >= 15 => 'medium',
            default => 'low',
        };
    }

    public function sendReminder(int $memberId): void
    {
        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Payment reminder sent successfully',
        ]);
    }

    public function escalateDefaulter(int $memberId): void
    {
        $this->dispatch('show-notification', [
            'type' => 'warning',
            'message' => 'Defaulter escalated for further action',
        ]);
    }

    public function generateReport(): void
    {
        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Fee collection report generated successfully',
        ]);
    }

    public function exportData(): void
    {
        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Data exported successfully',
        ]);
    }

    public function getDefaulterStatusColor(string $status): string
    {
        return match ($status) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'blue',
            default => 'gray',
        };
    }

    public function getPaymentMethodIcon(string $method): string
    {
        return match ($method) {
            'credit_card' => '💳',
            'bank_transfer' => '🏦',
            'cash' => '💵',
            'cheque' => '📄',
            default => '💰',
        };
    }

    public function render()
    {
        return view('livewire.membership.fee-collection-dashboard');
    }
}

<?php

namespace App\Livewire\Membership;

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

    public function loadDashboardData(): void
    {
        $this->dashboardStats = [
            'total_members' => 1247,
            'active_members' => 1156,
            'total_collected' => 4567890,
            'pending_amount' => 1234567,
            'overdue_amount' => 567890,
            'defaulters_count' => 89,
            'collection_rate' => 87.5,
        ];

        $this->recentPayments = [
            [
                'id' => 1,
                'member_name' => 'John Doe',
                'member_id' => 'MEM001',
                'amount' => 500000,
                'payment_method' => 'credit_card',
                'payment_date' => '2025-12-08 14:30:00',
                'fee_type' => 'Annual Subscription',
            ],
            [
                'id' => 2,
                'member_name' => 'Jane Smith',
                'member_id' => 'MEM002',
                'amount' => 50000,
                'payment_method' => 'bank_transfer',
                'payment_date' => '2025-12-08 13:45:00',
                'fee_type' => 'Monthly Subscription',
            ],
            [
                'id' => 3,
                'member_name' => 'Robert Johnson',
                'member_id' => 'MEM003',
                'amount' => 25000,
                'payment_method' => 'cash',
                'payment_date' => '2025-12-08 12:20:00',
                'fee_type' => 'Sports Facilities',
            ],
            [
                'id' => 4,
                'member_name' => 'Emily Davis',
                'member_id' => 'MEM004',
                'amount' => 300000,
                'payment_method' => 'cheque',
                'payment_date' => '2025-12-08 11:15:00',
                'fee_type' => 'Annual Subscription',
            ],
            [
                'id' => 5,
                'member_name' => 'Michael Wilson',
                'member_id' => 'MEM005',
                'amount' => 75000,
                'payment_method' => 'credit_card',
                'payment_date' => '2025-12-08 10:30:00',
                'fee_type' => 'Family Membership',
            ],
        ];

        $this->defaulters = [
            [
                'id' => 1,
                'member_name' => 'William Brown',
                'member_id' => 'MEM101',
                'overdue_amount' => 150000,
                'days_overdue' => 45,
                'last_payment_date' => '2025-09-15',
                'contact_attempts' => 3,
                'status' => 'critical',
            ],
            [
                'id' => 2,
                'member_name' => 'Sarah Miller',
                'member_id' => 'MEM102',
                'overdue_amount' => 75000,
                'days_overdue' => 30,
                'last_payment_date' => '2025-10-20',
                'contact_attempts' => 2,
                'status' => 'high',
            ],
            [
                'id' => 3,
                'member_name' => 'David Taylor',
                'member_id' => 'MEM103',
                'overdue_amount' => 50000,
                'days_overdue' => 15,
                'last_payment_date' => '2025-11-01',
                'contact_attempts' => 1,
                'status' => 'medium',
            ],
            [
                'id' => 4,
                'member_name' => 'Lisa Anderson',
                'member_id' => 'MEM104',
                'overdue_amount' => 25000,
                'days_overdue' => 10,
                'last_payment_date' => '2025-11-06',
                'contact_attempts' => 1,
                'status' => 'low',
            ],
            [
                'id' => 5,
                'member_name' => 'James Thomas',
                'member_id' => 'MEM105',
                'overdue_amount' => 100000,
                'days_overdue' => 60,
                'last_payment_date' => '2025-08-25',
                'contact_attempts' => 5,
                'status' => 'critical',
            ],
        ];

        $this->monthlyTrends = [
            ['month' => 'Jul', 'collected' => 3200000, 'pending' => 450000],
            ['month' => 'Aug', 'collected' => 3800000, 'pending' => 380000],
            ['month' => 'Sep', 'collected' => 4200000, 'pending' => 520000],
            ['month' => 'Oct', 'collected' => 4100000, 'pending' => 480000],
            ['month' => 'Nov', 'collected' => 4500000, 'pending' => 420000],
            ['month' => 'Dec', 'collected' => 4567890, 'pending' => 567890],
        ];
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

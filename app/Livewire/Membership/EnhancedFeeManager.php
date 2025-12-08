<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Services\Membership\FeeService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class EnhancedFeeManager extends Component
{
    use WithPagination;

    public ?Member $member = null;

    public ?MemberFee $fee = null;

    // UI State
    public bool $showCreateForm = false;

    public bool $showPaymentForm = false;

    public bool $showInvoiceModal = false;

    public bool $showReceiptModal = false;

    public bool $showAnalytics = false;

    public bool $showReminderModal = false;

    // Filters and Search
    public string $search = '';

    public string $status = 'all';

    public string $feeType = 'all';

    public string $dateRange = 'all';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 15;

    // Fee Creation Fields
    public ?int $member_id = null;

    #[Validate('required|in:subscription,late_fee,penalty,additional_service,event_fee,donation')]
    public string $fee_type = 'subscription';

    #[Validate('required|string|max:255')]
    public string $description = '';

    #[Validate('required|numeric|min:0.01')]
    public float $amount = 0;

    #[Validate('required|date')]
    public string $due_date = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $notes = null;

    public bool $recurring = false;

    public string $recurring_frequency = 'monthly';

    public ?string $recurring_end_date = null;

    // Payment Processing Fields
    public int $selectedFeeId = 0;

    public float $payment_amount = 0;

    public string $payment_method = 'cash';

    public ?string $payment_reference = null;

    public ?string $payment_notes = null;

    public bool $send_receipt = true;

    public bool $generate_invoice = false;

    // Invoice/Receipt Fields
    public ?MemberFee $selectedFee = null;

    public string $invoiceNumber = '';

    public string $receiptNumber = '';

    // Reminder Fields
    public array $selectedFeeIds = [];

    public string $reminderMessage = '';

    public string $reminderType = 'email';

    // Analytics Data
    public array $analyticsData = [];

    // Options Arrays
    public array $statuses = [
        'all' => 'All Status',
        'pending' => 'Pending',
        'paid' => 'Paid',
        'waived' => 'Waived',
        'overdue' => 'Overdue',
    ];

    public array $feeTypes = [
        'all' => 'All Types',
        'subscription' => 'Subscription',
        'late_fee' => 'Late Fee',
        'penalty' => 'Penalty',
        'additional_service' => 'Additional Service',
        'event_fee' => 'Event Fee',
        'donation' => 'Donation',
    ];

    public array $paymentMethods = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'credit_card' => 'Credit Card',
        'debit_card' => 'Debit Card',
        'check' => 'Check',
        'online' => 'Online Payment',
        'mobile_money' => 'Mobile Money',
        'cryptocurrency' => 'Cryptocurrency',
    ];

    public array $dateRanges = [
        'all' => 'All Time',
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'quarter' => 'This Quarter',
        'year' => 'This Year',
    ];

    public array $recurringFrequencies = [
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly' => 'Yearly',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'feeType' => ['except' => 'all'],
        'dateRange' => ['except' => 'all'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
    ];

    public function mount(?Member $member = null): void
    {
        $this->member = $member;
        $this->authorize('membership.manage_fees');
        $this->loadAnalytics();
    }

    public function render(FeeService $feeService)
    {
        $filters = $this->buildFilters();
        $fees = $feeService->getFees(
            organizationId: auth()->user()->current_organization_id,
            memberId: $this->member?->id,
            filters: $filters
        );

        return view('livewire.membership.enhanced-fee-manager', [
            'fees' => $fees,
            'feeStatistics' => $feeService->getFeeStatistics(auth()->user()->current_organization_id, $filters),
            'overdueFees' => $feeService->getOverdueFees(auth()->user()->current_organization_id),
            'recentPayments' => $feeService->getRecentPayments(auth()->user()->current_organization_id, 10),
            'revenueAnalytics' => $this->analyticsData,
        ]);
    }

    private function buildFilters(): array
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->status !== 'all' ? $this->status : null,
            'fee_type' => $this->feeType !== 'all' ? $this->feeType : null,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
            'per_page' => $this->perPage,
        ];

        // Add date range filter
        if ($this->dateRange !== 'all') {
            $filters['date_range'] = $this->dateRange;
        }

        return $filters;
    }

    public function createFee(FeeService $feeService): void
    {
        $this->validate();

        try {
            $memberId = $this->member?->id ?? $this->member_id;
            if (! $memberId) {
                throw new \Exception('Please select a member first');
            }

            $member = Member::findOrFail($memberId);
            if ($member->organization_id !== auth()->user()->current_organization_id) {
                throw new \Exception('Invalid member selection');
            }

            $feeData = [
                'fee_type' => $this->fee_type,
                'description' => $this->description,
                'amount' => $this->amount,
                'due_date' => $this->due_date,
                'notes' => $this->notes,
                'recurring' => $this->recurring,
                'recurring_frequency' => $this->recurring_frequency,
                'recurring_end_date' => $this->recurring_end_date,
                'distribute_to_accounts' => true,
            ];

            $fee = $feeService->createFee($member, $feeData);

            // Handle recurring fees
            if ($this->recurring && $this->recurring_end_date) {
                $this->createRecurringFees($member, $feeData, $feeService);
            }

            $this->resetFeeForm();
            $this->showCreateForm = false;
            $this->member_id = null;

            $this->dispatch('fee-created', feeId: $fee->id);
            $this->dispatch('show-notification', message: 'Fee created successfully', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    private function createRecurringFees(Member $member, array $feeData, FeeService $feeService): void
    {
        $startDate = now()->parse($this->due_date);
        $endDate = now()->parse($this->recurring_end_date);
        $interval = $this->recurring_frequency;

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $feeData['due_date'] = $currentDate->format('Y-m-d');
            $feeData['description'] = $this->description.' ('.$currentDate->format('M Y').')';

            $feeService->createFee($member, $feeData);

            switch ($interval) {
                case 'weekly':
                    $currentDate->addWeek();
                    break;
                case 'monthly':
                    $currentDate->addMonth();
                    break;
                case 'quarterly':
                    $currentDate->addQuarter();
                    break;
                case 'yearly':
                    $currentDate->addYear();
                    break;
            }
        }
    }

    public function processPayment(FeeService $feeService): void
    {
        $this->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'payment_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $fee = MemberFee::findOrFail($this->selectedFeeId);
            if ($fee->organization_id !== auth()->user()->current_organization_id) {
                abort(403);
            }

            $paymentData = [
                'amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
                'payment_reference' => $this->payment_reference,
                'notes' => $this->payment_notes,
            ];

            $success = $feeService->processFeePayment($fee, $paymentData);

            if ($success) {
                // Generate receipt if requested
                if ($this->send_receipt) {
                    $this->generateReceipt($fee);
                }

                // Generate invoice if requested
                if ($this->generate_invoice) {
                    $this->generateInvoice($fee);
                }

                $this->resetPaymentForm();
                $this->showPaymentForm = false;

                $this->dispatch('payment-processed', feeId: $fee->id);
                $this->dispatch('show-notification', message: 'Payment processed successfully', type: 'success');
            } else {
                throw new \Exception('Failed to process payment');
            }

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function generateInvoice(MemberFee $fee): void
    {
        $this->selectedFee = $fee;
        $this->invoiceNumber = 'INV-'.date('Y').'-'.str_pad($fee->id, 6, '0', STR_PAD_LEFT);
        $this->showInvoiceModal = true;
    }

    public function generateReceipt(MemberFee $fee): void
    {
        $this->selectedFee = $fee;
        $this->receiptNumber = 'RCP-'.date('Y').'-'.str_pad($fee->id, 6, '0', STR_PAD_LEFT);
        $this->showReceiptModal = true;
    }

    public function printInvoice(): void
    {
        $this->dispatch('print-invoice');
    }

    public function printReceipt(): void
    {
        $this->dispatch('print-receipt');
    }

    public function sendReminders(FeeService $feeService): void
    {
        if (empty($this->selectedFeeIds)) {
            $this->dispatch('show-notification', message: 'Please select fees to send reminders', type: 'error');

            return;
        }

        try {
            $count = 0;
            foreach ($this->selectedFeeIds as $feeId) {
                $fee = MemberFee::find($feeId);
                if ($fee && $fee->organization_id === auth()->user()->current_organization_id) {
                    $feeService->sendPaymentReminder($fee, $this->reminderMessage, $this->reminderType);
                    $count++;
                }
            }

            $this->selectedFeeIds = [];
            $this->showReminderModal = false;
            $this->reminderMessage = '';

            $this->dispatch('show-notification', message: "Reminders sent to {$count} members", type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function bulkWaiveFees(array $feeIds, string $reason, FeeService $feeService): void
    {
        try {
            $count = 0;
            foreach ($feeIds as $feeId) {
                $fee = MemberFee::find($feeId);
                if ($fee && $fee->organization_id === auth()->user()->current_organization_id) {
                    $feeService->waiveFee($fee, $reason);
                    $count++;
                }
            }

            $this->dispatch('show-notification', message: "{$count} fees waived successfully", type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function exportData(string $format): void
    {
        $this->dispatch('export-fees', format: $format);
    }

    public function loadAnalytics(): void
    {
        $organizationId = auth()->user()->current_organization_id;

        // Monthly revenue trend (last 12 months)
        $monthlyTrend = MemberFee::where('organization_id', $organizationId)
            ->where('paid_date', '>=', now()->subMonths(12))
            ->selectRaw('strftime("%Y-%m", paid_date) as month, SUM(amount) as revenue, COUNT(*) as transactions')
            ->whereNotNull('paid_date')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();

        // Revenue by fee type
        $revenueByType = MemberFee::where('organization_id', $organizationId)
            ->where('status', 'paid')
            ->selectRaw('fee_type, SUM(amount) as revenue, COUNT(*) as count')
            ->groupBy('fee_type')
            ->get()
            ->toArray();

        // Payment method distribution
        $paymentMethods = MemberFee::where('organization_id', $organizationId)
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->toArray();

        // Aging analysis
        $agingAnalysis = [
            'current' => MemberFee::where('organization_id', $organizationId)
                ->where('status', 'pending')
                ->where('due_date', '>=', now())
                ->sum('amount'),
            '1_30_days' => MemberFee::where('organization_id', $organizationId)
                ->where('status', 'pending')
                ->where('due_date', '<', now())
                ->where('due_date', '>=', now()->subDays(30))
                ->sum('amount'),
            '31_60_days' => MemberFee::where('organization_id', $organizationId)
                ->where('status', 'pending')
                ->where('due_date', '<', now()->subDays(30))
                ->where('due_date', '>=', now()->subDays(60))
                ->sum('amount'),
            '61_90_days' => MemberFee::where('organization_id', $organizationId)
                ->where('status', 'pending')
                ->where('due_date', '<', now()->subDays(60))
                ->where('due_date', '>=', now()->subDays(90))
                ->sum('amount'),
            'over_90_days' => MemberFee::where('organization_id', $organizationId)
                ->where('status', 'pending')
                ->where('due_date', '<', now()->subDays(90))
                ->sum('amount'),
        ];

        $this->analyticsData = [
            'monthly_trend' => $monthlyTrend,
            'revenue_by_type' => $revenueByType,
            'payment_methods' => $paymentMethods,
            'aging_analysis' => $agingAnalysis,
        ];
    }

    // UI Helper Methods
    public function showCreateFeeForm(): void
    {
        $this->showCreateForm = true;
        $this->due_date = now()->addDays(30)->format('Y-m-d');
    }

    public function hideCreateFeeForm(): void
    {
        $this->showCreateForm = false;
        $this->resetFeeForm();
    }

    public function showPaymentForm(int $feeId): void
    {
        $this->selectedFeeId = $feeId;
        $fee = MemberFee::find($feeId);
        if ($fee) {
            $this->payment_amount = $fee->remaining_amount;
        } else {
            $this->payment_amount = 0.0;
        }
        $this->showPaymentForm = true;
    }

    public function hidePaymentForm(): void
    {
        $this->showPaymentForm = false;
        $this->resetPaymentForm();
    }

    public function showReminderModal(): void
    {
        $this->showReminderModal = true;
        $this->reminderMessage = 'This is a reminder that your fee payment is due. Please make your payment at your earliest convenience.';
    }

    public function hideReminderModal(): void
    {
        $this->showReminderModal = false;
        $this->reminderMessage = '';
        $this->selectedFeeIds = [];
    }

    private function resetFeeForm(): void
    {
        $this->fee_type = 'subscription';
        $this->description = '';
        $this->amount = 0;
        $this->due_date = '';
        $this->notes = null;
        $this->member_id = null;
        $this->recurring = false;
        $this->recurring_frequency = 'monthly';
        $this->recurring_end_date = null;
    }

    private function resetPaymentForm(): void
    {
        $this->selectedFeeId = 0;
        $this->payment_amount = 0;
        $this->payment_method = 'cash';
        $this->payment_reference = null;
        $this->payment_notes = null;
        $this->send_receipt = true;
        $this->generate_invoice = false;
    }

    // Event Listeners
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFeeType(): void
    {
        $this->resetPage();
    }

    public function updatedDateRange(): void
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

    #[On('fee-created')]
    #[On('payment-processed')]
    #[On('fee-waived')]
    #[On('overdue-fees-generated')]
    public function refreshFees(): void
    {
        $this->resetPage();
        $this->loadAnalytics();
    }

    public function getOverdueFeesProperty(): \Illuminate\Support\Collection
    {
        return app(FeeService::class)->getOverdueFees(auth()->user()->current_organization_id);
    }

    public function toggleAnalytics(): void
    {
        $this->showAnalytics = ! $this->showAnalytics;
        if ($this->showAnalytics) {
            $this->loadAnalytics();
        }
    }
}

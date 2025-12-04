<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Services\Membership\FeeService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class FeeManager extends Component
{
    use WithPagination;

    public ?Member $member = null;

    public ?MemberFee $fee = null;

    public bool $showCreateForm = false;

    public bool $showPaymentForm = false;

    public string $search = '';

    public string $status = 'all';

    public string $feeType = 'all';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 15;

    // Member selection for fee creation
    public ?int $member_id = null;

    // Form fields for creating fees
    #[Validate('required|in:subscription,late_fee,penalty,additional_service')]
    public string $fee_type = 'subscription';

    #[Validate('required|string|max:255')]
    public string $description = '';

    #[Validate('required|numeric|min:0.01')]
    public float $amount = 0;

    #[Validate('required|date')]
    public string $due_date = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $notes = null;

    // Form fields for processing payments
    public int $selectedFeeId = 0;

    public float $payment_amount = 0;

    public string $payment_method = 'cash';

    public ?string $payment_reference = null;

    public ?string $payment_notes = null;

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
    ];

    public array $paymentMethods = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'credit_card' => 'Credit Card',
        'debit_card' => 'Debit Card',
        'check' => 'Check',
        'online' => 'Online Payment',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'feeType' => ['except' => 'all'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
    ];

    public function mount(?Member $member = null): void
    {
        $this->member = $member;
        $this->authorize('membership.manage_fees');
    }

    public function render(FeeService $feeService)
    {
        $fees = $feeService->getFees(
            organizationId: auth()->user()->current_organization_id,
            memberId: $this->member?->id,
            filters: [
                'search' => $this->search,
                'status' => $this->status !== 'all' ? $this->status : null,
                'fee_type' => $this->feeType !== 'all' ? $this->feeType : null,
                'sort_by' => $this->sortBy,
                'sort_direction' => $this->sortDirection,
                'per_page' => $this->perPage,
            ]
        );

        return view('livewire.membership.fee-manager', [
            'fees' => $fees,
            'feeStatistics' => $feeService->getFeeStatistics(auth()->user()->current_organization_id),
        ]);
    }

    public function createFee(FeeService $feeService): void
    {
        $this->validate();

        // Set up authentication for service calls
        if (! auth()->check()) {
            auth()->login($this->user);
        }

        try {
            $memberId = $this->member?->id ?? $this->member_id;
            \Log::info('createFee: memberId', ['memberId' => $memberId]);

            if (! $memberId) {
                throw new \Exception('Please select a member first');
            }

            $member = Member::findOrFail($memberId);
            \Log::info('createFee: member found', ['member' => $member->id]);

            if ($member->organization_id !== auth()->user()->current_organization_id) {
                throw new \Exception('Invalid member selection');
            }

            $feeData = [
                'fee_type' => $this->fee_type,
                'description' => $this->description,
                'amount' => $this->amount,
                'due_date' => $this->due_date,
                'notes' => $this->notes,
                'distribute_to_accounts' => false, // Disable accounting for now
            ];

            \Log::info('createFee: about to call service', ['feeData' => $feeData]);

            $fee = $feeService->createFee($member, $feeData);
            \Log::info('createFee: service returned', ['fee' => $fee ? $fee->id : 'null']);

            \Log::info('createFee: about to reset form');
            $this->resetFeeForm();
            $this->showCreateForm = false;
            $this->member_id = null;

            \Log::info('createFee: about to dispatch events');
            $this->dispatch('fee-created', feeId: $fee->id);
            $this->dispatch('show-notification', message: 'Fee created successfully', type: 'success');
            \Log::info('createFee: events dispatched');

        } catch (\Exception $e) {
            \Log::error('createFee: exception', ['error' => $e->getMessage()]);
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function processPayment(FeeService $feeService): void
    {
        \Log::info('processPayment called', [
            'selectedFeeId' => $this->selectedFeeId,
            'payment_amount' => $this->payment_amount,
            'payment_method' => $this->payment_method,
        ]);

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

            \Log::info('processPayment: calling service', ['paymentData' => $paymentData]);
            $success = $feeService->processFeePayment($fee, $paymentData);
            \Log::info('processPayment: service returned', ['success' => $success]);

            if ($success) {
                $this->resetPaymentForm();
                $this->showPaymentForm = false;

                $this->dispatch('payment-processed', feeId: $fee->id);
                $this->dispatch('show-notification', message: 'Payment processed successfully', type: 'success');
            } else {
                throw new \Exception('Failed to process payment');
            }

        } catch (\Exception $e) {
            \Log::error('processPayment: exception', ['error' => $e->getMessage()]);
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function waiveFee(int $feeId, string $reason, FeeService $feeService): void
    {
        \Log::info('waiveFee called', ['feeId' => $feeId, 'reason' => $reason]);

        try {
            $fee = MemberFee::findOrFail($feeId);

            if ($fee->organization_id !== auth()->user()->current_organization_id) {
                abort(403);
            }

            \Log::info('waiveFee: calling service');
            $success = $feeService->waiveFee($fee, $reason);
            \Log::info('waiveFee: service returned', ['success' => $success]);

            if ($success) {
                $this->dispatch('fee-waived', feeId: $feeId);
                $this->dispatch('show-notification', message: 'Fee waived successfully', type: 'success');
                \Log::info('waiveFee: events dispatched');
            } else {
                throw new \Exception('Failed to waive fee');
            }

        } catch (\Exception $e) {
            \Log::error('waiveFee: exception', ['error' => $e->getMessage()]);
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function generateOverdueFees(FeeService $feeService): void
    {
        try {
            $generatedCount = $feeService->generateOverdueFees(auth()->user()->current_organization_id);

            $this->dispatch('overdue-fees-generated', count: $generatedCount);
            $this->dispatch('show-notification',
                message: "Generated {$generatedCount} overdue fees",
                type: 'success'
            );

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

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

    private function resetFeeForm(): void
    {
        $this->fee_type = 'subscription';
        $this->description = '';
        $this->amount = 0;
        $this->due_date = '';
        $this->notes = null;
        $this->member_id = null;
    }

    private function resetPaymentForm(): void
    {
        $this->selectedFeeId = 0;
        $this->payment_amount = 0;
        $this->payment_method = 'cash';
        $this->payment_reference = null;
        $this->payment_notes = null;
    }

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
    }

    public function getOverdueFeesProperty(): \Illuminate\Support\Collection
    {
        return app(FeeService::class)->getOverdueFees(auth()->user()->current_organization_id);
    }
}

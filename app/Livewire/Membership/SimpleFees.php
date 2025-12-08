<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Services\Membership\FeeService;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class SimpleFees extends Component
{
    use WithPagination;

    public bool $showAddFeeForm = false;

    public string $search = '';

    public string $status = 'all';

    public string $feeType = 'all';

    public int $perPage = 10;

    // Form fields for creating fees
    #[Validate('required|exists:members,id')]
    public ?int $member_id = null;

    #[Validate('required|in:subscription,late_fee,penalty,additional_service,registration,locker,other')]
    public string $fee_type = 'subscription';

    #[Validate('required|string|max:255')]
    public string $description = '';

    #[Validate('required|numeric|min:0.01')]
    public float $amount = 0;

    #[Validate('required|date|after_or_equal:today')]
    public string $due_date = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $notes = null;

    public function getFeeTypesProperty(): array
    {
        return [
            'subscription' => 'Subscription Fee',
            'late_fee' => 'Late Fee',
            'penalty' => 'Penalty',
            'additional_service' => 'Additional Service',
            'registration' => 'Registration Fee',
            'locker' => 'Locker Fee',
            'other' => 'Other Fee',
        ];
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'feeType' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        try {
            // Check if user has current organization
            if (! auth()->user()->current_organization_id) {
                // Allow component to render but with empty data
                return;
            }
            $this->authorize('membership.view_fees');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        } catch (\Exception $e) {
            // Handle other exceptions gracefully
        }
    }

    public function render(FeeService $feeService)
    {
        try {
            $organizationId = auth()->user()->current_organization_id;
        } catch (\Exception $e) {
            $organizationId = null;
        }

        // Get recent fees with pagination
        $fees = $feeService->getFees(
            organizationId: $organizationId,
            filters: [
                'search' => $this->search,
                'status' => $this->status !== 'all' ? $this->status : null,
                'fee_type' => $this->feeType !== 'all' ? $this->feeType : null,
                'per_page' => $this->perPage,
                'sort_by' => 'created_at',
                'sort_direction' => 'desc',
            ]
        );

        // Transform recent fees for the view (get first 10 for display)
        $recentFees = $fees->getCollection()->take(10)->map(function ($fee) {
            return [
                'member' => $fee->member->full_name,
                'type' => ucfirst(str_replace('_', ' ', $fee->fee_type)),
                'amount' => $fee->amount,
                'status' => $fee->status,
                'date' => $fee->created_at->format('M d, Y'),
            ];
        })->toArray();

        return view('livewire.membership.simple-fees', [
            'fees' => $fees,
            'recentFees' => $recentFees,
            'feeStats' => $this->feeStats,
            'canManageFees' => $this->canManageFees,
            'feeTypes' => $this->feeTypes,
            'members' => Member::where('organization_id', $organizationId)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
        ]);
    }

    public function addFee(FeeService $feeService): void
    {
        $this->validate();

        try {
            $member = Member::findOrFail($this->member_id);

            if ($member->organization_id !== auth()->user()->current_organization_id) {
                throw new \Exception('Invalid member selection');
            }

            $feeData = [
                'fee_type' => $this->fee_type,
                'description' => $this->description,
                'amount' => $this->amount,
                'due_date' => $this->due_date,
                'notes' => $this->notes,
                'distribute_to_accounts' => false, // Disable accounting for simplicity
            ];

            $fee = $feeService->createFee($member, $feeData);

            $this->resetFeeForm();
            $this->showAddFeeForm = false;

            $this->dispatch('fee-created', feeId: $fee->id);
            $this->dispatch('show-notification',
                message: 'Fee created successfully for '.$member->full_name,
                type: 'success'
            );

        } catch (\Exception $e) {
            $this->dispatch('show-notification',
                message: 'Error: '.$e->getMessage(),
                type: 'error'
            );
        }
    }

    public function showAddFeeForm(): void
    {
        try {
            $this->authorize('membership.manage_fees');
            $this->showAddFeeForm = true;
            $this->due_date = now()->addDays(30)->format('Y-m-d');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        }
    }

    public function hideAddFeeForm(): void
    {
        $this->showAddFeeForm = false;
        $this->resetFeeForm();
    }

    public function processPayment(int $feeId, FeeService $feeService): void
    {
        try {
            $this->authorize('membership.manage_fees');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        }

        try {
            $fee = MemberFee::findOrFail($feeId);

            if ($fee->organization_id !== auth()->user()->current_organization_id) {
                abort(403);
            }

            $paymentData = [
                'amount' => $fee->remaining_amount,
                'payment_method' => 'cash',
                'payment_reference' => 'PAY-'.uniqid(),
            ];

            $success = $feeService->processFeePayment($fee, $paymentData);

            if ($success) {
                $this->dispatch('payment-processed', feeId: $fee->id);
                $this->dispatch('show-notification',
                    message: 'Payment processed successfully',
                    type: 'success'
                );
            } else {
                throw new \Exception('Failed to process payment');
            }

        } catch (\Exception $e) {
            $this->dispatch('show-notification',
                message: 'Error: '.$e->getMessage(),
                type: 'error'
            );
        }
    }

    public function waiveFee(int $feeId, string $reason, FeeService $feeService): void
    {
        try {
            $this->authorize('membership.manage_fees');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        }

        try {
            $fee = MemberFee::findOrFail($feeId);

            if ($fee->organization_id !== auth()->user()->current_organization_id) {
                abort(403);
            }

            $success = $feeService->waiveFee($fee, $reason);

            if ($success) {
                $this->dispatch('fee-waived', feeId: $feeId);
                $this->dispatch('show-notification',
                    message: 'Fee waived successfully',
                    type: 'success'
                );
            } else {
                throw new \Exception('Failed to waive fee');
            }

        } catch (\Exception $e) {
            $this->dispatch('show-notification',
                message: 'Error: '.$e->getMessage(),
                type: 'error'
            );
        }
    }

    private function resetFeeForm(): void
    {
        $this->member_id = null;
        $this->fee_type = 'subscription';
        $this->description = '';
        $this->amount = 0;
        $this->due_date = '';
        $this->notes = null;
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

    #[On('fee-created')]
    #[On('payment-processed')]
    #[On('fee-waived')]
    public function refreshFees(): void
    {
        $this->resetPage();
    }

    public function getCanManageFeesProperty(): bool
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return false;
            }

            return $user->can('membership.manage_fees');
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getFeeStatsProperty(): array
    {
        try {
            $organizationId = auth()->user()?->current_organization_id;

            if (! $organizationId) {
                return [
                    'total_collected' => 0,
                    'pending' => 0,
                    'overdue' => 0,
                    'this_month' => 0,
                ];
            }

            $totalCollected = MemberFee::where('organization_id', $organizationId)
                ->where('status', 'paid')
                ->whereMonth('paid_date', now()->month)
                ->sum('amount');

            // Pending excludes overdue fees
            $pending = MemberFee::where('organization_id', $organizationId)
                ->where('status', 'pending')
                ->where('due_date', '>=', now())
                ->sum('amount');

            $overdue = MemberFee::where('organization_id', $organizationId)
                ->where(function ($query) {
                    $query->where('status', 'overdue')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('status', 'pending')
                                ->where('due_date', '<', now());
                        });
                })
                ->sum('amount');

            $thisMonth = MemberFee::where('organization_id', $organizationId)
                ->where('status', 'paid')
                ->whereMonth('paid_date', now()->month)
                ->sum('amount');

            return [
                'total_collected' => $totalCollected,
                'pending' => $pending,
                'overdue' => $overdue,
                'this_month' => $thisMonth,
            ];
        } catch (\Exception $e) {
            return [
                'total_collected' => 0,
                'pending' => 0,
                'overdue' => 0,
                'this_month' => 0,
            ];
        }
    }
}

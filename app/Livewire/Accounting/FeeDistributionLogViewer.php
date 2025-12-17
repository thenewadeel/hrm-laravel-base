<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\FeeDistributionLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FeeDistributionLogViewer extends Component
{
    use WithPagination;

    protected $listeners = ['refreshLogs' => '$refresh'];

    public $search = '';

    public $statusFilter = '';

    public $feeTypeFilter = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $showDetailsModal = false;

    public $selectedLog = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'feeTypeFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        if (! Auth::check()) {
            abort(401);
        }

        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function getLogsProperty()
    {
        if (! Auth::check()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        $organizationId = Auth::user()->current_organization_id;
        if (! $organizationId) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        $query = FeeDistributionLog::where('organization_id', $organizationId)
            ->with(['rule', 'memberFee.member', 'journalEntry']);

        // Apply all filters in one place to avoid conflicts
        if ($this->search) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('memberFee', function ($subQ) use ($searchTerm) {
                    $subQ->where('description', 'like', '%'.$searchTerm.'%');
                })->orWhereHas('memberFee.member', function ($memberQ) use ($searchTerm) {
                    $memberQ->where('first_name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('last_name', 'like', '%'.$searchTerm.'%');
                });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->feeTypeFilter) {
            $query->whereHas('memberFee', function ($q) {
                $q->where('fee_type', $this->feeTypeFilter);
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('distributed_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('distributed_at', '<=', $this->dateTo);
        }

        return $query->orderBy('distributed_at', 'desc')->paginate(15);
    }

    public function showDetails(FeeDistributionLog $log)
    {
        // Verify the log belongs to the user's organization
        if ($log->organization_id !== Auth::user()->current_organization_id) {
            abort(403);
        }

        $this->selectedLog = $log->load(['rule.items.chartOfAccount', 'memberFee.member', 'journalEntry.ledgerEntries.chartOfAccount']);
        $this->showDetailsModal = true;
    }

    public function getFeeTypesProperty()
    {
        return [
            'subscription' => 'Subscription Fee',
            'late_fee' => 'Late Fee',
            'penalty' => 'Penalty',
            'registration' => 'Registration Fee',
            'renewal' => 'Renewal Fee',
            'other' => 'Other Fee',
        ];
    }

    public function getStatusOptionsProperty()
    {
        return [
            'success' => 'Success',
            'failed' => 'Failed',
            'partial' => 'Partial',
        ];
    }

    public function getSummaryProperty()
    {
        if (! Auth::check()) {
            return [
                'total_amount' => 0.0,
                'total_distributed' => 0.0,
                'success_count' => 0,
                'failed_count' => 0,
                'partial_count' => 0,
                'total_count' => 0,
            ];
        }

        $organizationId = Auth::user()->current_organization_id;

        // Create separate queries to avoid modification issues
        $baseQuery = function () use ($organizationId) {
            return FeeDistributionLog::where('organization_id', $organizationId)
                ->when($this->dateFrom, function ($query) {
                    $query->whereDate('distributed_at', '>=', $this->dateFrom);
                })
                ->when($this->dateTo, function ($query) {
                    $query->whereDate('distributed_at', '<=', $this->dateTo);
                });
        };

        // Get all logs for distributed amount calculation
        $allLogs = $baseQuery()->get();

        return [
            'total_amount' => (float) $baseQuery()->where('status', 'success')->sum('total_amount'),
            'total_distributed' => (float) $allLogs->sum(function ($log) {
                return $log->actual_distributed_amount;
            }),
            'success_count' => $baseQuery()->where('status', 'success')->count(),
            'failed_count' => $baseQuery()->where('status', 'failed')->count(),
            'partial_count' => $baseQuery()->where('status', 'partial')->count(),
            'total_count' => $baseQuery()->count(),
        ];
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter', 'feeTypeFilter']);
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        if (! Auth::check()) {
            abort(401);
        }

        // Ensure the user has a current organization
        if (! Auth::user()->current_organization_id) {
            abort(403, 'No organization selected');
        }

        return view('livewire.accounting.fee-distribution-log-viewer', [
            'logs' => $this->logs,
            'summary' => $this->summary,
            'feeTypes' => $this->feeTypes,
            'statusOptions' => $this->statusOptions,
            'showDetailsModal' => $this->showDetailsModal,
            'selectedLog' => $this->selectedLog,
        ]);
    }
}

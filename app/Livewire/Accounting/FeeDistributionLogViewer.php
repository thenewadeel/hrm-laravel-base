<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\FeeDistributionLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FeeDistributionLogViewer extends Component
{
    use WithPagination;

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
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function getLogsProperty()
    {
        return FeeDistributionLog::where('organization_id', Auth::user()->current_organization_id)
            ->with(['rule', 'memberFee.member', 'journalEntry'])
            ->when($this->search, function ($query) {
                $query->whereHas('memberFee', function ($q) {
                    $q->where('description', 'like', '%'.$this->search.'%')
                        ->orWhereHas('member', function ($mq) {
                            $mq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->feeTypeFilter, function ($query) {
                $query->whereHas('memberFee', function ($q) {
                    $q->where('fee_type', $this->feeTypeFilter);
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('distributed_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('distributed_at', '<=', $this->dateTo);
            })
            ->orderBy('distributed_at', 'desc')
            ->paginate(15);
    }

    public function showDetails(FeeDistributionLog $log)
    {
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
        $baseFilters = function ($query) {
            $query->where('organization_id', Auth::user()->current_organization_id)
                ->when($this->dateFrom, function ($query) {
                    $query->whereDate('distributed_at', '>=', $this->dateFrom);
                })
                ->when($this->dateTo, function ($query) {
                    $query->whereDate('distributed_at', '<=', $this->dateTo);
                });
        };

        return [
            'total_amount' => FeeDistributionLog::where($baseFilters)->sum('total_amount'),
            'total_distributed' => FeeDistributionLog::where($baseFilters)->get()->sum(function ($log) {
                return $log->actual_distributed_amount;
            }),
            'success_count' => FeeDistributionLog::where($baseFilters)->where('status', 'success')->count(),
            'failed_count' => FeeDistributionLog::where($baseFilters)->where('status', 'failed')->count(),
            'partial_count' => FeeDistributionLog::where($baseFilters)->where('status', 'partial')->count(),
            'total_count' => FeeDistributionLog::where($baseFilters)->count(),
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
        return view('livewire.accounting.fee-distribution-log-viewer');
    }
}

<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\TaxFiling;
use App\Models\Accounting\TaxRate;
use App\Services\TaxComplianceService;
use Livewire\Component;
use Livewire\WithPagination;

class TaxFilingManager extends Component
{
    use WithPagination;

    public $search = '';

    public $filterStatus = '';

    public $filterType = '';

    public $sortBy = 'due_date';

    public $sortDirection = 'asc';

    public $showCreateModal = false;

    public $selectedTaxRateId = '';

    public $filingType = 'quarterly';

    public $periodStart;

    public $periodEnd;

    protected $taxComplianceService;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterType' => ['except' => ''],
        'sortBy' => ['except' => 'due_date'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function boot(TaxComplianceService $taxComplianceService): void
    {
        $this->taxComplianceService = $taxComplianceService;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', TaxFiling::class);
        $this->periodStart = now()->startOfQuarter()->format('Y-m-d');
        $this->periodEnd = now()->endOfQuarter()->format('Y-m-d');
    }

    public function render()
    {
        $query = TaxFiling::where('organization_id', auth()->user()->current_organization_id)
            ->with(['taxRate', 'jurisdiction', 'creator', 'approver'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('filing_number', 'like', '%'.$this->search.'%')
                        ->orWhere('confirmation_number', 'like', '%'.$this->search.'%')
                        ->orWhere('filing_notes', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterType, function ($query) {
                $query->where('filing_type', $this->filterType);
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $filings = $query->paginate(15);

        return view('livewire.accounting.tax-filing-manager', [
            'filings' => $filings,
            'taxRates' => TaxRate::where('organization_id', auth()->user()->current_organization_id)
                ->active()
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function createFiling(): void
    {
        $this->validate([
            'selectedTaxRateId' => 'required|exists:tax_rates,id',
            'filingType' => 'required|in:monthly,quarterly,annual,special',
            'periodStart' => 'required|date',
            'periodEnd' => 'required|date|after:periodStart',
        ]);

        try {
            $filing = $this->taxComplianceService->createTaxFiling(
                auth()->user()->current_organization_id,
                $this->selectedTaxRateId,
                $this->filingType,
                $this->periodStart,
                $this->periodEnd,
                auth()->user()
            );

            $this->dispatch('show-message', 'Tax filing created successfully.', 'success');
            $this->showCreateModal = false;
            $this->reset(['selectedTaxRateId', 'filingType', 'periodStart', 'periodEnd']);
        } catch (\Exception $e) {
            $this->dispatch('show-message', 'Error creating tax filing: '.$e->getMessage(), 'error');
        }
    }

    public function approveFiling(int $id): void
    {
        $filing = TaxFiling::findOrFail($id);
        $this->authorize('update', $filing);

        $filing->approve(auth()->user());
        $this->dispatch('show-message', 'Tax filing approved successfully.', 'success');
    }

    public function markAsPaid(int $id): void
    {
        $filing = TaxFiling::findOrFail($id);
        $this->authorize('update', $filing);

        $filing->markAsPaid();
        $this->dispatch('show-message', 'Tax filing marked as paid.', 'success');
    }

    public function deleteFiling(int $id): void
    {
        $filing = TaxFiling::findOrFail($id);
        $this->authorize('delete', $filing);

        if ($filing->status === 'accepted' || $filing->status === 'paid') {
            $this->dispatch('show-message', 'Cannot delete filed tax return.', 'error');

            return;
        }

        $filing->delete();
        $this->dispatch('show-message', 'Tax filing deleted successfully.', 'success');
    }

    public function generateQuarterlyFilings(): void
    {
        try {
            $quarter = ceil(now()->month / 3);
            $year = now()->year;

            $filings = $this->taxComplianceService->generateQuarterlyFilings(
                auth()->user()->current_organization_id,
                "Q{$quarter}",
                (string) $year,
                auth()->user()
            );

            $this->dispatch('show-message', "Generated {$filings->count()} quarterly tax filings.", 'success');
        } catch (\Exception $e) {
            $this->dispatch('show-message', 'Error generating quarterly filings: '.$e->getMessage(), 'error');
        }
    }

    public function calculatePenalties(): void
    {
        $this->taxComplianceService->calculatePenaltiesAndInterest();
        $this->dispatch('show-message', 'Penalties and interest calculated successfully.', 'success');
    }
}

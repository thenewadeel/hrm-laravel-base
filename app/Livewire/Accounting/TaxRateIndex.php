<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\TaxRate;
use Livewire\Component;
use Livewire\WithPagination;

class TaxRateIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $filterType = '';

    public $filterStatus = '';

    public $sortBy = 'name';

    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', TaxRate::class);
    }

    public function render()
    {
        $query = TaxRate::where('organization_id', auth()->user()->current_organization_id)
            ->with(['jurisdiction'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterType, function ($query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->filterStatus === 'active', function ($query) {
                $query->active();
            })
            ->when($this->filterStatus === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $taxRates = $query->paginate(10);

        return view('livewire.accounting.tax-rate-index', [
            'taxRates' => $taxRates,
            'taxTypes' => TaxRate::distinct()->pluck('type'),
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

    public function delete(int $id): void
    {
        $taxRate = TaxRate::findOrFail($id);
        $this->authorize('delete', $taxRate);

        if ($taxRate->calculations()->exists()) {
            $this->dispatch('show-message',
                'Cannot delete tax rate: it has associated calculations.',
                'error'
            );

            return;
        }

        $taxRate->delete();
        $this->dispatch('show-message', 'Tax rate deleted successfully.', 'success');
    }

    public function toggleStatus(int $id): void
    {
        $taxRate = TaxRate::findOrFail($id);
        $this->authorize('update', $taxRate);

        $taxRate->update(['is_active' => ! $taxRate->is_active]);

        $status = $taxRate->is_active ? 'activated' : 'deactivated';
        $this->dispatch('show-message', "Tax rate {$status} successfully.", 'success');
    }
}

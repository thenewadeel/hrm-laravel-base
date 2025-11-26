<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\TaxExemption;
use Livewire\Component;
use Livewire\WithPagination;

class TaxExemptionIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $filterType = '';

    public $filterStatus = '';

    public $sortBy = 'certificate_number';

    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'sortBy' => ['except' => 'certificate_number'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', TaxExemption::class);
    }

    public function render()
    {
        $query = TaxExemption::where('organization_id', auth()->user()->current_organization_id)
            ->with(['exemptible', 'taxRate'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('certificate_number', 'like', '%'.$this->search.'%')
                        ->orWhere('exemption_type', 'like', '%'.$this->search.'%')
                        ->orWhere('reason', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterType, function ($query) {
                $query->where('exemption_type', $this->filterType);
            })
            ->when($this->filterStatus === 'active', function ($query) {
                $query->active();
            })
            ->when($this->filterStatus === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->when($this->filterStatus === 'expired', function ($query) {
                $query->where('expiry_date', '<', now());
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $exemptions = $query->paginate(10);

        return view('livewire.accounting.tax-exemption-index', [
            'exemptions' => $exemptions,
            'exemptionTypes' => TaxExemption::distinct()->pluck('exemption_type'),
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
        $exemption = TaxExemption::findOrFail($id);
        $this->authorize('delete', $exemption);

        $exemption->delete();
        $this->dispatch('show-message', 'Tax exemption deleted successfully.', 'success');
    }

    public function toggleStatus(int $id): void
    {
        $exemption = TaxExemption::findOrFail($id);
        $this->authorize('update', $exemption);

        $exemption->update(['is_active' => ! $exemption->is_active]);

        $status = $exemption->is_active ? 'activated' : 'deactivated';
        $this->dispatch('show-message', "Tax exemption {$status} successfully.", 'success');
    }
}

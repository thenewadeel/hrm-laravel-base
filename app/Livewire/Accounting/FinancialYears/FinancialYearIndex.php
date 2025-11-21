<?php

namespace App\Livewire\Accounting\FinancialYears;

use App\Models\Accounting\FinancialYear;
use Livewire\Component;
use Livewire\WithPagination;

class FinancialYearIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function render()
    {
        $query = FinancialYear::query()
            ->with(['lockedBy', 'closedBy'])
            ->where('organization_id', auth()->user()->current_organization_id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $financialYears = $query->orderBy('start_date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.accounting.financial-years.financial-year-index', [
            'financialYears' => $financialYears,
        ]);
    }

    public function delete(FinancialYear $financialYear)
    {
        if ($financialYear->status === 'active') {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Cannot delete an active financial year.',
            ]);

            return;
        }

        if ($financialYear->isClosed()) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Cannot delete a closed financial year.',
            ]);

            return;
        }

        $financialYear->delete();

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Financial year deleted successfully.',
        ]);
    }

    public function activate(FinancialYear $financialYear)
    {
        if ($financialYear->isClosed()) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Cannot activate a closed financial year.',
            ]);

            return;
        }

        $financialYear->update(['status' => 'active']);

        // Deactivate other active financial years
        FinancialYear::where('organization_id', auth()->user()->current_organization_id)
            ->where('id', '!=', $financialYear->id)
            ->where('status', 'active')
            ->update(['status' => 'draft']);

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Financial year activated successfully.',
        ]);
    }

    public function lock(FinancialYear $financialYear)
    {
        if ($financialYear->isClosed()) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Cannot lock a closed financial year.',
            ]);

            return;
        }

        $financialYear->lock();

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Financial year locked successfully.',
        ]);
    }

    public function unlock(FinancialYear $financialYear)
    {
        if ($financialYear->isClosed()) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Cannot unlock a closed financial year.',
            ]);

            return;
        }

        $financialYear->unlock();

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Financial year unlocked successfully.',
        ]);
    }
}

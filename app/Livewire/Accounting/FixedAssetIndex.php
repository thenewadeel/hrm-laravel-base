<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\FixedAsset;
use App\Models\Accounting\FixedAssetCategory;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class FixedAssetIndex extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $category = '';

    #[Url]
    public $status = '';

    #[Url]
    public $location = '';

    public $selectedAssets = [];

    public $selectAll = false;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->resetFilters();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'category', 'status', 'location']);
        $this->resetPage();
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedAssets = $this->assets->pluck('id')->toArray();
        } else {
            $this->selectedAssets = [];
        }
    }

    public function updatedSelectedAssets(): void
    {
        $this->selectAll = count($this->selectedAssets) === $this->assets->count();
    }

    public function bulkDepreciation(): void
    {
        if (empty($this->selectedAssets)) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Please select assets to depreciate.',
            ]);

            return;
        }

        $this->dispatch('open-bulk-depreciation', [
            'assetIds' => $this->selectedAssets,
        ]);
    }

    public function bulkDispose(): void
    {
        if (empty($this->selectedAssets)) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Please select assets to dispose.',
            ]);

            return;
        }

        $this->dispatch('open-bulk-disposal', [
            'assetIds' => $this->selectedAssets,
        ]);
    }

    #[Computed]
    public function assets()
    {
        return FixedAsset::query()
            ->with(['category', 'assetAccount', 'accumulatedDepreciationAccount'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('asset_tag', 'like', '%'.$this->search.'%')
                        ->orWhere('serial_number', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->category, function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->where('code', $this->category);
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->location, function ($query) {
                $query->where('location', 'like', '%'.$this->location.'%');
            })
            ->latest('purchase_date')
            ->paginate(15);
    }

    #[Computed]
    public function categories()
    {
        return FixedAssetCategory::active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    #[Computed]
    public function statusOptions()
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'disposed' => 'Disposed',
            'under_maintenance' => 'Under Maintenance',
        ];
    }

    #[Computed]
    public function totalAssets()
    {
        return $this->assets->total();
    }

    #[Computed]
    public function totalBookValue()
    {
        return FixedAsset::query()
            ->when($this->category, function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->where('code', $this->category);
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->location, function ($query) {
                $query->where('location', 'like', '%'.$this->location.'%');
            })
            ->sum('current_book_value');
    }

    public function render()
    {
        return view('livewire.accounting.fixed-asset-index');
    }
}

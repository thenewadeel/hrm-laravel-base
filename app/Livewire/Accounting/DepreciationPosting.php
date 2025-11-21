<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\FixedAsset;
use App\Services\FixedAssetService;
use Livewire\Component;
use Livewire\WithPagination;

class DepreciationPosting extends Component
{
    use WithPagination;

    public $selectedDate;

    public $processing = false;

    public $results = [];

    public $showResults = false;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function postDepreciation(): void
    {
        $this->validate([
            'selectedDate' => 'required|date|before_or_equal:today',
        ]);

        $this->processing = true;

        try {
            $fixedAssetService = app(FixedAssetService::class);
            $date = new \DateTime($this->selectedDate);

            $result = $fixedAssetService->bulkDepreciation($date);

            $this->results = $result;
            $this->showResults = true;

            $this->dispatch('show-message', [
                'type' => $result['errors'] > 0 ? 'warning' : 'success',
                'message' => "Depreciation processed: {$result['processed']} assets, {$result['errors']} errors.",
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Error posting depreciation: '.$e->getMessage(),
            ]);
        } finally {
            $this->processing = false;
        }
    }

    public function getAssetsForDepreciation()
    {
        return FixedAsset::query()
            ->active()
            ->where(fn ($query) => $query
                ->whereNull('last_depreciation_date')
                ->orWhere('last_depreciation_date', '<', $this->selectedDate)
            )
            ->with(['category', 'assetAccount'])
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.accounting.depreciation-posting', [
            'assets' => $this->getAssetsForDepreciation(),
        ]);
    }
}

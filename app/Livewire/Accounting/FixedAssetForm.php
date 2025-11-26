<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\FixedAsset;
use App\Models\Accounting\FixedAssetCategory;
use App\Models\ChartOfAccount;
use App\Services\FixedAssetService;
use Livewire\Component;

class FixedAssetForm extends Component
{
    public FixedAsset $asset;

    public $isEditing = false;

    public $categories = [];

    public $assetAccounts = [];

    public $accumulatedDepreciationAccounts = [];

    public $depreciationMethods = [
        'straight_line' => 'Straight Line',
        'declining_balance' => 'Declining Balance',
        'sum_of_years' => 'Sum of Years Digits',
    ];

    protected $rules = [
        'asset.fixed_asset_category_id' => 'required|exists:fixed_asset_categories,id',
        'asset.chart_of_account_id' => 'nullable|exists:chart_of_accounts,id',
        'asset.accumulated_depreciation_account_id' => 'nullable|exists:chart_of_accounts,id',
        'asset.asset_tag' => 'required|string|max:50',
        'asset.name' => 'required|string|max:255',
        'asset.description' => 'nullable|string|max:1000',
        'asset.serial_number' => 'nullable|string|max:100',
        'asset.location' => 'nullable|string|max:255',
        'asset.department' => 'nullable|string|max:255',
        'asset.assigned_to' => 'nullable|string|max:255',
        'asset.purchase_date' => 'required|date|before_or_equal:today',
        'asset.purchase_cost' => 'required|numeric|min:0',
        'asset.salvage_value' => 'nullable|numeric|min:0',
        'asset.useful_life_years' => 'required|integer|min:1|max:50',
        'asset.depreciation_method' => 'required|in:straight_line,declining_balance,sum_of_years',
        'asset.notes' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'asset.purchase_cost.required' => 'Purchase cost is required.',
        'asset.purchase_cost.min' => 'Purchase cost must be greater than or equal to 0.',
        'asset.useful_life_years.required' => 'Useful life is required.',
        'asset.useful_life_years.min' => 'Useful life must be at least 1 year.',
        'asset.asset_tag.required' => 'Asset tag is required.',
        'asset.asset_tag.unique' => 'Asset tag must be unique within the organization.',
    ];

    public function mount(?FixedAsset $asset = null): void
    {
        $this->asset = $asset ?? new FixedAsset;
        $this->isEditing = $asset->exists;

        $this->loadDropdownData();

        if (! $this->isEditing) {
            $this->asset->depreciation_method = 'straight_line';
            $this->asset->status = 'active';
        }
    }

    public function loadDropdownData(): void
    {
        $this->categories = FixedAssetCategory::active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'default_useful_life_years', 'default_depreciation_method']);

        $this->assetAccounts = ChartOfAccount::query()
            ->where('type', 'asset')
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $this->accumulatedDepreciationAccounts = ChartOfAccount::query()
            ->where('type', 'asset') // Contra-asset accounts
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }

    public function updatedAssetFixedAssetCategoryId(): void
    {
        $category = $this->categories->firstWhere('id', $this->asset->fixed_asset_category_id);

        if ($category && ! $this->isEditing) {
            $this->asset->useful_life_years = $category->default_useful_life_years;
            $this->asset->depreciation_method = $category->default_depreciation_method;
        }
    }

    public function save(): void
    {
        $this->validate();

        try {
            $fixedAssetService = app(FixedAssetService::class);

            if ($this->isEditing) {
                $this->asset->updated_by = auth()->id();
                $this->asset->save();
                $message = 'Asset updated successfully.';
            } else {
                $fixedAssetService->registerAsset($this->asset->toArray());
                $message = 'Asset registered successfully.';
            }

            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => $message,
            ]);

            $this->dispatch('asset-saved');

        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.accounting.fixed-asset-form');
    }
}

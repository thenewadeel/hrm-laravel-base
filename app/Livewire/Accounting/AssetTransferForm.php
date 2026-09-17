<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\FixedAsset;
use App\Services\FixedAssetService;
use Livewire\Component;

class AssetTransferForm extends Component
{
    public FixedAsset $asset;

    public string $toLocation = '';

    public string $toDepartment = '';

    public string $toAssignedTo = '';

    public string $transferDate = '';

    public string $reason = '';

    public string $notes = '';

    protected $rules = [
        'toLocation' => 'required|string|max:255',
        'toDepartment' => 'nullable|string|max:255',
        'toAssignedTo' => 'nullable|string|max:255',
        'transferDate' => 'required|date',
        'reason' => 'nullable|string|max:500',
        'notes' => 'nullable|string|max:1000',
    ];

    public function mount(FixedAsset $asset): void
    {
        $this->asset = $asset;
        $this->transferDate = now()->format('Y-m-d');
    }

    public function transfer()
    {
        $this->validate();

        try {
            app(FixedAssetService::class)->transferAsset($this->asset, [
                'to_location' => $this->toLocation,
                'to_department' => $this->toDepartment,
                'to_assigned_to' => $this->toAssignedTo,
                'transfer_date' => $this->transferDate,
                'reason' => $this->reason,
                'notes' => $this->notes,
            ]);

            session()->flash('success', 'Asset transferred successfully.');

            return redirect()->route('accounting.fixed-assets.index');
        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.accounting.asset-transfer-form');
    }
}

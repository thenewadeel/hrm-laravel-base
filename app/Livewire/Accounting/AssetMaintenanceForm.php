<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\FixedAsset;
use App\Services\FixedAssetService;
use Livewire\Component;

class AssetMaintenanceForm extends Component
{
    public FixedAsset $asset;

    public string $maintenanceDate = '';

    public string $maintenanceType = '';

    public string $description = '';

    public float $cost = 0;

    public string $performedBy = '';

    public string $vendor = '';

    public string $notes = '';

    public string $nextMaintenanceDate = '';

    protected $rules = [
        'maintenanceDate' => 'required|date',
        'maintenanceType' => 'required|string|max:255',
        'description' => 'required|string|max:1000',
        'cost' => 'nullable|numeric|min:0',
        'performedBy' => 'nullable|string|max:255',
        'vendor' => 'nullable|string|max:255',
        'notes' => 'nullable|string|max:1000',
        'nextMaintenanceDate' => 'nullable|date|after_or_equal:maintenanceDate',
    ];

    public array $maintenanceTypes = [
        'preventive' => 'Preventive',
        'corrective' => 'Corrective',
        'repair' => 'Repair',
        'inspection' => 'Inspection',
        'calibration' => 'Calibration',
        'overhaul' => 'Overhaul',
    ];

    public function mount(FixedAsset $asset): void
    {
        $this->asset = $asset;
        $this->maintenanceDate = now()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate();

        try {
            app(FixedAssetService::class)->recordMaintenance($this->asset, [
                'maintenance_date' => $this->maintenanceDate,
                'maintenance_type' => $this->maintenanceType,
                'description' => $this->description,
                'cost' => $this->cost,
                'performed_by' => $this->performedBy,
                'vendor' => $this->vendor,
                'notes' => $this->notes,
                'next_maintenance_date' => $this->nextMaintenanceDate ?: null,
            ]);

            session()->flash('success', 'Maintenance record created successfully.');

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
        return view('livewire.accounting.asset-maintenance-form');
    }
}

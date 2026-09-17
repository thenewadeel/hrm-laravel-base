<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\FixedAsset;
use App\Services\FixedAssetService;
use Livewire\Component;

class AssetDisposalForm extends Component
{
    public FixedAsset $asset;

    public string $disposalDate = '';

    public string $disposalType = '';

    public float $disposalValue = 0;

    public float $proceeds = 0;

    public string $disposedTo = '';

    public string $reason = '';

    public string $notes = '';

    protected $rules = [
        'disposalDate' => 'required|date',
        'disposalType' => 'required|string|max:255',
        'disposalValue' => 'nullable|numeric|min:0',
        'proceeds' => 'nullable|numeric|min:0',
        'disposedTo' => 'nullable|string|max:255',
        'reason' => 'nullable|string|max:500',
        'notes' => 'nullable|string|max:1000',
    ];

    public array $disposalTypes = [
        'sale' => 'Sale',
        'scrap' => 'Scrap',
        'donation' => 'Donation',
        'trade_in' => 'Trade-In',
        'write_off' => 'Write-Off',
        'theft' => 'Theft / Loss',
    ];

    public function mount(FixedAsset $asset): void
    {
        $this->asset = $asset;
        $this->disposalDate = now()->format('Y-m-d');
        $this->disposalValue = $asset->current_book_value;
        $this->proceeds = $asset->current_book_value;
    }

    public function save()
    {
        $this->validate();

        try {
            app(FixedAssetService::class)->disposeAsset($this->asset, [
                'disposal_date' => $this->disposalDate,
                'disposal_type' => $this->disposalType,
                'disposal_value' => $this->disposalValue,
                'proceeds' => $this->proceeds,
                'disposed_to' => $this->disposedTo,
                'reason' => $this->reason,
                'notes' => $this->notes,
            ]);

            session()->flash('success', 'Asset disposed successfully.');

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
        return view('livewire.accounting.asset-disposal-form');
    }
}

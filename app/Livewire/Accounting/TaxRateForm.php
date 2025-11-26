<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\TaxJurisdiction;
use App\Models\Accounting\TaxRate;
use Livewire\Component;

class TaxRateForm extends Component
{
    public TaxRate $taxRate;

    public $jurisdictions;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->taxRate = TaxRate::findOrFail($id);
            $this->authorize('update', $this->taxRate);
        } else {
            $this->taxRate = new TaxRate([
                'organization_id' => auth()->user()->current_organization_id,
                'is_active' => true,
                'is_compound' => false,
                'effective_date' => now()->format('Y-m-d'),
                'rate' => 0,
            ]);
            $this->authorize('create', TaxRate::class);
        }

        $this->jurisdictions = TaxJurisdiction::where('organization_id', auth()->user()->current_organization_id)
            ->active()
            ->orderBy('name')
            ->get();
    }

    protected function rules(): array
    {
        return [
            'taxRate.name' => 'required|string|max:255',
            'taxRate.code' => 'required|string|max:50|unique:tax_rates,code,'.$this->taxRate->id.',id,organization_id,'.auth()->user()->current_organization_id,
            'taxRate.type' => 'required|in:sales,purchase,withholding,income,vat,service,other',
            'taxRate.rate' => 'required|numeric|min:0|max:100',
            'taxRate.tax_jurisdiction_id' => 'nullable|exists:tax_jurisdictions,id',
            'taxRate.is_compound' => 'boolean',
            'taxRate.is_active' => 'boolean',
            'taxRate.effective_date' => 'required|date',
            'taxRate.end_date' => 'nullable|date|after:taxRate.effective_date',
            'taxRate.description' => 'nullable|string|max:1000',
            'taxRate.gl_account_code' => 'nullable|string|max:50',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->taxRate->organization_id = auth()->user()->current_organization_id;
        $this->taxRate->save();

        $message = $this->taxRate->wasRecentlyCreated ? 'Tax rate created successfully.' : 'Tax rate updated successfully.';
        $this->dispatch('show-message', $message, 'success');
        $this->dispatch('tax-rate-saved');
    }

    public function render()
    {
        return view('livewire.accounting.tax-rate-form', [
            'taxTypes' => [
                'sales' => 'Sales Tax',
                'purchase' => 'Purchase Tax',
                'withholding' => 'Withholding Tax',
                'income' => 'Income Tax',
                'vat' => 'VAT/GST',
                'service' => 'Service Tax',
                'other' => 'Other Tax',
            ],
        ]);
    }
}

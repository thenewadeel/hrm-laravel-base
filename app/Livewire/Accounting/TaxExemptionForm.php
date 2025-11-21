<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\TaxExemption;
use App\Models\Accounting\TaxRate;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Vendor;
use Livewire\Component;

class TaxExemptionForm extends Component
{
    public TaxExemption $taxExemption;

    public $taxRates;

    public $customers;

    public $vendors;

    public $employees;

    public $entityType = '';

    public $entityId = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->taxExemption = TaxExemption::findOrFail($id);
            $this->authorize('update', $this->taxExemption);

            // Set entity type and ID for existing exemption
            $this->entityType = class_basename($this->taxExemption->exemptible_type);
            $this->entityId = $this->taxExemption->exemptible_id;
        } else {
            $this->taxExemption = new TaxExemption([
                'organization_id' => auth()->user()->current_organization_id,
                'is_active' => true,
                'exemption_percentage' => 100,
                'issue_date' => now()->format('Y-m-d'),
            ]);
            $this->authorize('create', TaxExemption::class);
        }

        $this->loadRelatedData();
    }

    protected function rules(): array
    {
        return [
            'entityType' => 'required|in:Customer,Vendor,Employee',
            'entityId' => 'required|integer',
            'taxExemption.certificate_number' => 'required|string|max:100|unique:tax_exemptions,certificate_number,'.$this->taxExemption->id.',id,organization_id,'.auth()->user()->current_organization_id,
            'taxExemption.exemption_type' => 'required|in:resale,charitable,government,manufacturing,educational,religious,export,agricultural,research,other',
            'taxExemption.exemption_percentage' => 'required|numeric|min:0|max:100',
            'taxExemption.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'taxExemption.issue_date' => 'required|date',
            'taxExemption.expiry_date' => 'nullable|date|after:taxExemption.issue_date',
            'taxExemption.is_active' => 'boolean',
            'taxExemption.reason' => 'nullable|string|max:1000',
            'taxExemption.issuing_authority' => 'nullable|string|max:255',
            'taxExemption.notes' => 'nullable|string|max:1000',
        ];
    }

    public function save(): void
    {
        $this->validate();

        // Set the exemptible relationship
        $entityClass = match ($this->entityType) {
            'Customer' => Customer::class,
            'Vendor' => Vendor::class,
            'Employee' => Employee::class,
            default => null,
        };

        if ($entityClass && $this->entityId) {
            $this->taxExemption->exemptible_type = $entityClass;
            $this->taxExemption->exemptible_id = $this->entityId;
        }

        $this->taxExemption->organization_id = auth()->user()->current_organization_id;
        $this->taxExemption->save();

        $message = $this->taxExemption->wasRecentlyCreated ? 'Tax exemption created successfully.' : 'Tax exemption updated successfully.';
        $this->dispatch('show-message', $message, 'success');
        $this->dispatch('tax-exemption-saved');
    }

    public function loadRelatedData(): void
    {
        $organizationId = auth()->user()->current_organization_id;

        $this->taxRates = TaxRate::where('organization_id', $organizationId)
            ->active()
            ->orderBy('name')
            ->get();

        $this->customers = Customer::where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();

        $this->vendors = Vendor::where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();

        $this->employees = Employee::where('organization_id', $organizationId)
            ->orderBy('first_name')
            ->get();
    }

    public function render()
    {
        return view('livewire.accounting.tax-exemption-form', [
            'exemptionTypes' => [
                'resale' => 'Resale Certificate',
                'charitable' => 'Charitable Organization',
                'government' => 'Government Entity',
                'manufacturing' => 'Manufacturing Exemption',
                'educational' => 'Educational Institution',
                'religious' => 'Religious Organization',
                'export' => 'Export Exemption',
                'agricultural' => 'Agricultural Exemption',
                'research' => 'Research & Development',
                'other' => 'Other Exemption',
            ],
            'entityOptions' => [
                'Customer' => $this->customers,
                'Vendor' => $this->vendors,
                'Employee' => $this->employees,
            ],
        ]);
    }
}

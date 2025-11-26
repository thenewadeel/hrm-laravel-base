<?php

namespace App\Livewire\Payroll;

use App\Models\AllowanceType;
use App\Models\Employee;
use App\Models\EmployeeAllowance;
use Livewire\Component;
use Livewire\WithPagination;

class AllowanceManagement extends Component
{
    use WithPagination;

    public $employee_id;

    public $allowance_type_id;

    public $amount;

    public $percentage;

    public $effective_date;

    public $end_date;

    public $notes;

    public $showCreateForm = false;

    public $search = '';

    // Allowance Type Management
    public $showTypeForm = false;

    public $type_name;

    public $type_code;

    public $type_description;

    public $calculation_type = 'fixed_amount';

    public $default_value;

    public $is_taxable = true;

    public $is_recurring = true;

    protected $rules = [
        'employee_id' => 'required|exists:employees,id',
        'allowance_type_id' => 'required|exists:allowance_types,id',
        'amount' => 'required_without:percentage|numeric|min:0',
        'percentage' => 'required_without:amount|numeric|min:0|max:100',
        'effective_date' => 'required|date|after_or_equal:today',
        'end_date' => 'nullable|date|after:effective_date',
        'notes' => 'nullable|string|max:500',
    ];

    protected $typeRules = [
        'type_name' => 'required|string|max:255',
        'type_code' => 'required|string|max:50|unique:allowance_types,code',
        'type_description' => 'nullable|string|max:500',
        'calculation_type' => 'required|in:fixed_amount,percentage_of_basic,percentage_of_gross',
        'default_value' => 'required|numeric|min:0',
        'is_taxable' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    public function render()
    {
        $query = EmployeeAllowance::with(['employee', 'allowanceType'])
            ->where('organization_id', auth()->user()->currentOrganization->id)
            ->latest();

        if ($this->search) {
            $query->whereHas('employee', function ($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%');
            });
        }

        $allowances = $query->paginate(10);
        $employees = Employee::where('organization_id', auth()->user()->currentOrganization->id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $allowanceTypes = AllowanceType::where('organization_id', auth()->user()->currentOrganization->id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('livewire.payroll.allowance-management', [
            'allowances' => $allowances,
            'employees' => $employees,
            'allowanceTypes' => $allowanceTypes,
        ]);
    }

    public function createAllowance()
    {
        $this->validate();

        EmployeeAllowance::create([
            'employee_id' => $this->employee_id,
            'organization_id' => auth()->user()->currentOrganization->id,
            'allowance_type_id' => $this->allowance_type_id,
            'amount' => $this->amount,
            'percentage' => $this->percentage,
            'effective_date' => $this->effective_date,
            'end_date' => $this->end_date,
            'notes' => $this->notes,
        ]);

        $this->reset(['employee_id', 'allowance_type_id', 'amount', 'percentage', 'effective_date', 'end_date', 'notes', 'showCreateForm']);

        $this->dispatch('allowance-created');
    }

    public function createAllowanceType()
    {
        $this->validate($this->typeRules);

        AllowanceType::create([
            'organization_id' => auth()->user()->currentOrganization->id,
            'name' => $this->type_name,
            'code' => $this->type_code,
            'description' => $this->type_description,
            'calculation_type' => $this->calculation_type,
            'default_value' => $this->default_value,
            'is_taxable' => $this->is_taxable,
            'is_recurring' => $this->is_recurring,
        ]);

        $this->reset(['type_name', 'type_code', 'type_description', 'calculation_type', 'default_value', 'is_taxable', 'is_recurring', 'showTypeForm']);

        $this->dispatch('allowance-type-created');
    }

    public function toggleAllowanceStatus($allowanceId)
    {
        $allowance = EmployeeAllowance::findOrFail($allowanceId);
        $allowance->update(['is_active' => ! $allowance->is_active]);

        $this->dispatch('allowance-status-toggled');
    }

    public function deleteAllowance($allowanceId)
    {
        $allowance = EmployeeAllowance::findOrFail($allowanceId);
        $allowance->delete();

        $this->dispatch('allowance-deleted');
    }
}

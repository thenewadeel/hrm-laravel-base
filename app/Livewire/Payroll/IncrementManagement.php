<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Models\EmployeeIncrement;
use Livewire\Component;
use Livewire\WithPagination;

class IncrementManagement extends Component
{
    use WithPagination;

    public $employee_id;

    public $increment_type = 'percentage';

    public $increment_value;

    public $effective_date;

    public $reason;

    public $showCreateForm = false;

    public $search = '';

    protected $rules = [
        'employee_id' => 'required|exists:employees,id',
        'increment_type' => 'required|in:percentage,fixed_amount',
        'increment_value' => 'required|numeric|min:0',
        'effective_date' => 'required|date|after_or_equal:today',
        'reason' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'increment_value.min' => 'Increment value must be greater than 0',
        'effective_date.after_or_equal' => 'Effective date cannot be in the past',
    ];

    public function render()
    {
        $query = EmployeeIncrement::with(['employee', 'approver'])
            ->where('organization_id', auth()->user()->currentOrganization->id)
            ->latest();

        if ($this->search) {
            $query->whereHas('employee', function ($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%');
            });
        }

        $increments = $query->paginate(10);
        $employees = Employee::where('organization_id', auth()->user()->currentOrganization->id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('livewire.payroll.increment-management', [
            'increments' => $increments,
            'employees' => $employees,
        ]);
    }

    public function createIncrement()
    {
        $this->validate();

        $employee = Employee::findOrFail($this->employee_id);
        $currentSalary = $employee->basic_salary;

        $newSalary = $this->increment_type === 'percentage'
            ? $currentSalary + ($currentSalary * ($this->increment_value / 100))
            : $currentSalary + $this->increment_value;

        EmployeeIncrement::create([
            'employee_id' => $this->employee_id,
            'organization_id' => auth()->user()->currentOrganization->id,
            'increment_type' => $this->increment_type,
            'increment_value' => $this->increment_value,
            'previous_salary' => $currentSalary,
            'new_salary' => $newSalary,
            'effective_date' => $this->effective_date,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        $this->reset(['employee_id', 'increment_type', 'increment_value', 'effective_date', 'reason', 'showCreateForm']);

        $this->dispatch('increment-created');
    }

    public function approveIncrement($incrementId)
    {
        $increment = EmployeeIncrement::findOrFail($incrementId);
        $increment->approve(auth()->user(), 'Approved through payroll system');

        $this->dispatch('increment-approved');
    }

    public function implementIncrement($incrementId)
    {
        $increment = EmployeeIncrement::findOrFail($incrementId);
        $increment->implement();

        $this->dispatch('increment-implemented');
    }

    public function deleteIncrement($incrementId)
    {
        $increment = EmployeeIncrement::findOrFail($incrementId);

        if ($increment->status === 'pending') {
            $increment->delete();
            $this->dispatch('increment-deleted');
        }
    }
}

<?php

namespace App\Livewire\Accounting;

use App\Models\Employee;
use App\Services\SalaryVoucherService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class SalaryVoucherForm extends Component
{
    #[Validate('required|date')]
    public $entry_date = '';

    #[Validate('required|string|max:255')]
    public $description = '';

    #[Validate('required|exists:employees,id')]
    public $employee_id = '';

    #[Validate('required|numeric|min:0')]
    public $salary_amount = 0;

    #[Validate('nullable|numeric|min:0')]
    public $tax_deduction = 0;

    #[Validate('nullable|numeric|min:0')]
    public $other_deductions = 0;

    #[Validate('nullable|string|max:50')]
    public $payroll_period = '';

    public function mount(): void
    {
        $this->entry_date = now()->format('Y-m-d');
        $this->description = 'Monthly salary payment';
    }

    public function calculateNetSalary(): float
    {
        return $this->salary_amount - $this->tax_deduction - $this->other_deductions;
    }

    public function createSalaryVoucher(): void
    {
        $this->validate();

        $data = [
            'entry_date' => $this->entry_date,
            'description' => $this->description,
            'employee_id' => $this->employee_id,
            'salary_amount' => $this->salary_amount,
            'tax_deduction' => $this->tax_deduction,
            'other_deductions' => $this->other_deductions,
            'payroll_period' => $this->payroll_period,
        ];

        try {
            $voucher = app(SalaryVoucherService::class)->createSalaryVoucher($data);

            $this->dispatch('salary-voucher-created', voucher: $voucher);
            $this->dispatch('show-message', message: 'Salary voucher created successfully!', type: 'success');

            // Reset form
            $this->mount();
        } catch (\Exception $e) {
            $this->dispatch('show-message', message: 'Error creating salary voucher: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.accounting.salary-voucher-form', [
            'employees' => Employee::where('organization_id', auth()->user()->current_organization_id)
                ->where('is_active', true)
                ->with('user')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
        ]);
    }
}

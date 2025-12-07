<?php

namespace App\Livewire\Demo;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Employee;
use Livewire\Component;

class ModelSelectDemo extends Component
{
    public $selectedEmployee = '';

    public $selectedEmployees = [];

    public $selectedAccount = '';

    public $selectedDepartment = '';

    public function render()
    {
        // Generate sample data for demonstration
        $employees = Employee::limit(50)->get()->map(function ($employee) {
            return [
                'value' => $employee->id,
                'label' => $employee->first_name.' '.$employee->last_name,
                'description' => $employee->employee_id ?? null,
                'searchTerms' => [
                    $employee->first_name,
                    $employee->last_name,
                    $employee->employee_id ?? '',
                ],
            ];
        })->toArray();

        $accounts = ChartOfAccount::limit(30)->get()->map(function ($account) {
            return [
                'value' => $account->id,
                'label' => $account->name,
                'description' => $account->code,
                'searchTerms' => [
                    $account->name,
                    $account->code,
                ],
            ];
        })->toArray();

        $departments = [
            ['value' => 'hr', 'label' => 'Human Resources', 'description' => 'HR Department'],
            ['value' => 'it', 'label' => 'Information Technology', 'description' => 'IT Department'],
            ['value' => 'finance', 'label' => 'Finance', 'description' => 'Finance Department'],
            ['value' => 'marketing', 'label' => 'Marketing', 'description' => 'Marketing Department'],
            ['value' => 'operations', 'label' => 'Operations', 'description' => 'Operations Department'],
            ['value' => 'sales', 'label' => 'Sales', 'description' => 'Sales Department'],
        ];

        return view('livewire.demo.model-select-demo', [
            'employees' => $employees,
            'accounts' => $accounts,
            'departments' => $departments,
        ]);
    }
}

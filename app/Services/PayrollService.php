<?php

namespace App\Services;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Voucher;
use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class PayrollService
{
    /**
     * Process payroll for multiple employees
     */
    public function processPayroll(int $organizationId, array $payrollData): array
    {
        return DB::transaction(function () use ($organizationId, $payrollData) {
            $processedPayrolls = [];

            foreach ($payrollData as $data) {
                $employee = Employee::findOrFail($data['employee_id']);

                // Verify employee belongs to organization
                if ($employee->organization_id !== $organizationId) {
                    throw new \Exception('Employee does not belong to the specified organization');
                }

                $payroll = $this->processEmployeePayroll($employee, $data);
                $processedPayrolls[] = $payroll;

                // Create salary voucher in accounting
                $this->createSalaryVoucher($organizationId, $employee, $data);
            }

            return $processedPayrolls;
        });
    }

    /**
     * Process payroll for a single employee
     */
    private function processEmployeePayroll(Employee $employee, array $data): array
    {
        $basicSalary = $data['basic_salary'];
        $allowances = collect($data['allowances'] ?? [])->sum('amount');
        $deductions = collect($data['deductions'] ?? [])->sum('amount');

        $grossSalary = $basicSalary + $allowances;
        $netSalary = $grossSalary - $deductions;

        $payrollRecord = [
            'employee_id' => $employee->id,
            'pay_period' => $data['pay_period'],
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'gross_salary' => $grossSalary,
            'net_salary' => $netSalary,
            'status' => 'processed',
            'processed_at' => now(),
        ];

        // Dispatch payroll processed event
        Event::dispatch('App\\Events\\Payroll\\PayrollProcessed', [
            'employee' => $employee,
            'payroll_data' => $payrollRecord,
            'organization_id' => $employee->organization_id,
            'user_id' => auth()->id(),
        ]);

        return $payrollRecord;
    }

    /**
     * Create salary voucher in accounting system
     */
    private function createSalaryVoucher(int $organizationId, Employee $employee, array $data): Voucher
    {
        // Get default salary expense and cash accounts
        $salaryAccount = ChartOfAccount::where('organization_id', $organizationId)
            ->where('type', 'expense')
            ->where(function ($query) {
                $query->where('name', 'like', '%salary%')
                    ->orWhere('name', 'Salary Expense');
            })
            ->first();

        $cashAccount = ChartOfAccount::where('organization_id', $organizationId)
            ->where('type', 'asset')
            ->where(function ($query) {
                $query->where('name', 'like', '%cash%')
                    ->orWhere('name', 'Cash Account');
            })
            ->first();

        if (! $salaryAccount || ! $cashAccount) {
            throw new \Exception('Required accounts for salary voucher not found');
        }

        $netSalary = $data['basic_salary'] +
                    collect($data['allowances'] ?? [])->sum('amount') -
                    collect($data['deductions'] ?? [])->sum('amount');

        return Voucher::create([
            'organization_id' => $organizationId,
            'type' => 'salary',
            'date' => now(),
            'amount' => $netSalary,
            'description' => "Salary payment for {$employee->first_name} {$employee->last_name} - {$data['pay_period']}",
            'number' => Voucher::generateNumber('salary', $organizationId),
            'status' => 'posted',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Calculate payroll for preview
     */
    public function calculatePayroll(Employee $employee, array $data): array
    {
        $basicSalary = $data['basic_salary'];
        $allowances = collect($data['allowances'] ?? [])->sum('amount');
        $deductions = collect($data['deductions'] ?? [])->sum('amount');

        return [
            'employee' => $employee,
            'basic_salary' => $basicSalary,
            'allowances' => $data['allowances'] ?? [],
            'deductions' => $data['deductions'] ?? [],
            'gross_salary' => $basicSalary + $allowances,
            'total_deductions' => $deductions,
            'net_salary' => $basicSalary + $allowances - $deductions,
        ];
    }
}

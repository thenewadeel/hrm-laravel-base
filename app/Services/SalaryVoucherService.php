<?php

// app/Services/SalaryVoucherService.php

namespace App\Services;

use App\Models\Accounting\JournalEntry;
use App\Models\Employee;
use Illuminate\Validation\ValidationException;

class SalaryVoucherService extends VoucherService
{
    protected function getVoucherType(): string
    {
        return 'SALARY';
    }

    protected function generateReferenceNumber(): string
    {
        $latest = JournalEntry::where('voucher_type', 'SALARY')
            ->where('organization_id', auth()->user()->current_organization_id)
            ->orderBy('reference_number', 'desc')
            ->first();

        $nextNumber = $latest ? (int) str_replace('SALARY-', '', $latest->reference_number) + 1 : 1;

        return 'SALARY-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    protected function validateVoucherData(array $data): void
    {
        if (empty($data['employee_id'])) {
            throw ValidationException::withMessages(['employee_id' => 'Employee is required for salary voucher']);
        }

        if (empty($data['salary_amount']) || $data['salary_amount'] <= 0) {
            throw ValidationException::withMessages(['salary_amount' => 'Valid salary amount is required']);
        }
    }

    protected function prepareLedgerEntries(array $data): array
    {
        $entries = [];
        $employee = $this->validateEmployee($data['employee_id']);

        // Get salary expense account (default: 5100)
        $salaryAccount = $this->getAccountByCode('5100');

        // Get cash/bank account (default: 1000)
        $cashAccount = $this->getAccountByCode('1000');

        // Get tax payable account (default: 2100)
        $taxPayableAccount = $this->getAccountByCode('2100');

        // Get other deductions account (default: 2101)
        $deductionsAccount = $this->getAccountByCode('2101');

        $grossSalary = $data['salary_amount'];
        $taxDeduction = $data['tax_deduction'] ?? 0;
        $otherDeductions = $data['other_deductions'] ?? 0;
        $netSalary = $grossSalary - $taxDeduction - $otherDeductions;

        // Debit salary expense with gross salary
        $entries[] = [
            'account' => $salaryAccount,
            'type' => 'debit',
            'amount' => $grossSalary,
            'description' => "Salary for {$employee->first_name} {$employee->last_name}",
        ];

        // Credit tax payable
        if ($taxDeduction > 0) {
            $entries[] = [
                'account' => $taxPayableAccount,
                'type' => 'credit',
                'amount' => $taxDeduction,
                'description' => "Tax deduction for {$employee->first_name} {$employee->last_name}",
            ];
        }

        // Credit other deductions payable
        if ($otherDeductions > 0) {
            $entries[] = [
                'account' => $deductionsAccount,
                'type' => 'credit',
                'amount' => $otherDeductions,
                'description' => "Other deductions for {$employee->first_name} {$employee->last_name}",
            ];
        }

        // Credit cash/bank with net salary (actually credit reduces cash, so this is correct)
        $entries[] = [
            'account' => $cashAccount,
            'type' => 'credit',
            'amount' => $netSalary,
            'description' => "Net salary payment to {$employee->first_name} {$employee->last_name}",
        ];

        return $entries;
    }

    public function createSalaryVoucher(array $data): JournalEntry
    {
        $data['total_amount'] = $data['salary_amount'];
        $data['tax_amount'] = $data['tax_deduction'] ?? 0;

        return $this->createVoucher($data);
    }

    protected function validateEmployee(int $employeeId): Employee
    {
        return Employee::where('id', $employeeId)
            ->where('organization_id', auth()->user()->current_organization_id)
            ->firstOrFail();
    }
}

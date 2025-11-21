<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaxBracket;
use Carbon\Carbon;

class PayrollCalculationService
{
    /**
     * Calculate complete payroll for an employee for a given period
     */
    public function calculateEmployeePayroll(Employee $employee, string $period): array
    {
        $basicSalary = $this->getEffectiveBasicSalary($employee, $period);

        $allowances = $this->calculateAllowances($employee, $basicSalary, $period);
        $deductions = $this->calculateDeductions($employee, $basicSalary, $period);
        $loanDeductions = $this->calculateLoanDeductions($employee, $period);
        $advanceDeductions = $this->calculateAdvanceDeductions($employee, $period);

        $grossPay = $basicSalary + $allowances['total'];
        $taxableIncome = $this->calculateTaxableIncome($grossPay, $deductions['tax_exempt']);
        $tax = $this->calculateTax($employee, $taxableIncome);

        $totalDeductions = $deductions['total'] + $loanDeductions + $advanceDeductions + $tax;
        $netPay = $grossPay - $totalDeductions;

        return [
            'employee_id' => $employee->id,
            'period' => $period,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'loan_deductions' => $loanDeductions,
            'advance_deductions' => $advanceDeductions,
            'gross_pay' => $grossPay,
            'taxable_income' => $taxableIncome,
            'tax' => $tax,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
        ];
    }

    /**
     * Get effective basic salary considering increments
     */
    public function getEffectiveBasicSalary(Employee $employee, string $period): float
    {
        $periodDate = Carbon::parse($period.'-01');

        // Get the latest implemented increment effective before or in this period
        $increment = $employee->increments()
            ->implemented()
            ->where('effective_date', '<=', $periodDate->endOfMonth())
            ->latest('effective_date')
            ->first();

        if ($increment) {
            return $increment->new_salary;
        }

        return $employee->basic_salary ?? 0;
    }

    /**
     * Calculate all active allowances for an employee
     */
    public function calculateAllowances(Employee $employee, float $basicSalary, string $period): array
    {
        $periodDate = Carbon::parse($period.'-01');

        $allowances = $employee->allowances()
            ->active()
            ->effective($periodDate)
            ->with('allowanceType')
            ->get();

        $allowanceBreakdown = [];
        $totalAllowances = 0;

        foreach ($allowances as $allowance) {
            $amount = $allowance->getCalculatedAmount($basicSalary);

            if ($amount > 0) {
                $allowanceBreakdown[] = [
                    'name' => $allowance->allowanceType->name,
                    'code' => $allowance->allowanceType->code,
                    'amount' => $amount,
                    'is_taxable' => $allowance->allowanceType->is_taxable,
                ];

                $totalAllowances += $amount;
            }
        }

        return [
            'breakdown' => $allowanceBreakdown,
            'total' => $totalAllowances,
            'taxable' => collect($allowanceBreakdown)->where('is_taxable', true)->sum('amount'),
            'non_taxable' => collect($allowanceBreakdown)->where('is_taxable', false)->sum('amount'),
        ];
    }

    /**
     * Calculate all active deductions for an employee
     */
    public function calculateDeductions(Employee $employee, float $basicSalary, string $period): array
    {
        $periodDate = Carbon::parse($period.'-01');

        $deductions = $employee->deductions()
            ->active()
            ->effective($periodDate)
            ->with('deductionType')
            ->get();

        $deductionBreakdown = [];
        $totalDeductions = 0;

        foreach ($deductions as $deduction) {
            $amount = $deduction->getCalculatedAmount($basicSalary);

            if ($amount > 0) {
                $deductionBreakdown[] = [
                    'name' => $deduction->deductionType->name,
                    'code' => $deduction->deductionType->code,
                    'amount' => $amount,
                    'is_tax_exempt' => $deduction->deductionType->is_tax_exempt,
                ];

                $totalDeductions += $amount;
            }
        }

        return [
            'breakdown' => $deductionBreakdown,
            'total' => $totalDeductions,
            'tax_exempt' => collect($deductionBreakdown)->where('is_tax_exempt', true)->sum('amount'),
            'taxable' => collect($deductionBreakdown)->where('is_tax_exempt', false)->sum('amount'),
        ];
    }

    /**
     * Calculate loan deductions for the period
     */
    public function calculateLoanDeductions(Employee $employee, string $period): float
    {
        $totalLoanDeductions = 0;

        $activeLoans = $employee->loans()->active()->get();

        foreach ($activeLoans as $loan) {
            // Check if payment is due this month
            $firstPaymentDate = Carbon::parse($loan->first_payment_date);
            $currentPeriod = Carbon::parse($period.'-01');

            if ($currentPeriod >= $firstPaymentDate && $loan->balance_amount > 0) {
                $totalLoanDeductions += $loan->monthly_installment;
            }
        }

        return $totalLoanDeductions;
    }

    /**
     * Calculate salary advance deductions for the period
     */
    public function calculateAdvanceDeductions(Employee $employee, string $period): float
    {
        $totalAdvanceDeductions = 0;

        $activeAdvances = $employee->salaryAdvances()->active()->get();

        foreach ($activeAdvances as $advance) {
            if ($advance->shouldDeductThisMonth($period)) {
                $totalAdvanceDeductions += $advance->monthly_deduction;
            }
        }

        return $totalAdvanceDeductions;
    }

    /**
     * Calculate taxable income
     */
    public function calculateTaxableIncome(float $grossPay, float $taxExemptDeductions): float
    {
        return max(0, $grossPay - $taxExemptDeductions);
    }

    /**
     * Calculate tax based on organization's tax brackets
     */
    public function calculateTax(Employee $employee, float $taxableIncome): float
    {
        if ($taxableIncome <= 0) {
            return 0;
        }

        $taxBracket = TaxBracket::where('organization_id', $employee->organization_id)
            ->active()
            ->where('min_income', '<=', $taxableIncome)
            ->orderBy('min_income', 'desc')
            ->first();

        if (! $taxBracket) {
            return 0;
        }

        return $taxBracket->calculateTax($taxableIncome);
    }

    /**
     * Process payroll for multiple employees
     */
    public function processBatchPayroll(array $employeeIds, string $period): array
    {
        $results = [];

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::find($employeeId);

            if ($employee) {
                $results[$employeeId] = $this->calculateEmployeePayroll($employee, $period);
            }
        }

        return $results;
    }

    /**
     * Generate payroll summary for organization
     */
    public function generatePayrollSummary(int $organizationId, string $period): array
    {
        $employees = Employee::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->get();

        $summary = [
            'period' => $period,
            'total_employees' => $employees->count(),
            'total_basic_salary' => 0,
            'total_allowances' => 0,
            'total_gross_pay' => 0,
            'total_tax' => 0,
            'total_deductions' => 0,
            'total_net_pay' => 0,
            'employee_breakdown' => [],
        ];

        foreach ($employees as $employee) {
            $payroll = $this->calculateEmployeePayroll($employee, $period);

            $summary['total_basic_salary'] += $payroll['basic_salary'];
            $summary['total_allowances'] += $payroll['allowances']['total'];
            $summary['total_gross_pay'] += $payroll['gross_pay'];
            $summary['total_tax'] += $payroll['tax'];
            $summary['total_deductions'] += $payroll['total_deductions'];
            $summary['total_net_pay'] += $payroll['net_pay'];

            $summary['employee_breakdown'][] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->first_name.' '.$employee->last_name,
                'net_pay' => $payroll['net_pay'],
            ];
        }

        return $summary;
    }
}

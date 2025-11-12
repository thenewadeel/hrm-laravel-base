<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PayrollEntry;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollEntryFactory extends Factory
{
    protected $model = PayrollEntry::class;

    public function definition()
    {
        $basicSalary = $this->faker->randomFloat(2, 3000, 8000);
        $housingAllowance = $basicSalary * 0.2;
        $transportAllowance = $basicSalary * 0.1;
        $overtimePay = $this->faker->randomFloat(2, 0, 500);
        $bonus = $this->faker->randomFloat(2, 0, 1000);

        $grossPay = $basicSalary + $housingAllowance + $transportAllowance + $overtimePay + $bonus;

        $taxDeduction = $grossPay * 0.15;
        $insuranceDeduction = $grossPay * 0.05;
        $otherDeductions = $this->faker->randomFloat(2, 0, 200);

        $totalDeductions = $taxDeduction + $insuranceDeduction + $otherDeductions;
        $netPay = $grossPay - $totalDeductions;

        $period = $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m');

        return [
            'employee_id' => Employee::factory(),
            'organization_id' => Organization::factory(),
            'period' => $period,
            'basic_salary' => $basicSalary,
            'housing_allowance' => $housingAllowance,
            'transport_allowance' => $transportAllowance,
            'overtime_pay' => $overtimePay,
            'bonus' => $bonus,
            'gross_pay' => $grossPay,
            'tax_deduction' => $taxDeduction,
            'insurance_deduction' => $insuranceDeduction,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'status' => $this->faker->randomElement(['draft', 'processed', 'paid']),
            'paid_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'paid_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    public function processed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processed',
                'paid_at' => null,
            ];
        });
    }

    public function draft()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
                'paid_at' => null,
            ];
        });
    }

    public function forPeriod(string $period)
    {
        return $this->state(function (array $attributes) use ($period) {
            return [
                'period' => $period,
            ];
        });
    }

    public function forOrganization(Organization $organization)
    {
        return $this->state(function (array $attributes) use ($organization) {
            return [
                'organization_id' => $organization->id,
            ];
        });
    }

    public function forEmployee(Employee $employee)
    {
        return $this->state(function (array $attributes) use ($employee) {
            return [
                'employee_id' => $employee->id,
                'organization_id' => $employee->user->current_organization_id,
            ];
        });
    }

    public function withSalary(float $basicSalary)
    {
        return $this->state(function (array $attributes) use ($basicSalary) {
            $housingAllowance = $basicSalary * 0.2;
            $transportAllowance = $basicSalary * 0.1;
            $overtimePay = $this->faker->randomFloat(2, 0, 500);
            $bonus = $this->faker->randomFloat(2, 0, 1000);

            $grossPay = $basicSalary + $housingAllowance + $transportAllowance + $overtimePay + $bonus;

            $taxDeduction = $grossPay * 0.15;
            $insuranceDeduction = $grossPay * 0.05;
            $otherDeductions = $this->faker->randomFloat(2, 0, 200);

            $totalDeductions = $taxDeduction + $insuranceDeduction + $otherDeductions;
            $netPay = $grossPay - $totalDeductions;

            return [
                'basic_salary' => $basicSalary,
                'housing_allowance' => $housingAllowance,
                'transport_allowance' => $transportAllowance,
                'gross_pay' => $grossPay,
                'tax_deduction' => $taxDeduction,
                'insurance_deduction' => $insuranceDeduction,
                'total_deductions' => $totalDeductions,
                'net_pay' => $netPay,
            ];
        });
    }
}

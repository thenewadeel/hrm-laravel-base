<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PayrollSlip;
use App\Models\PayrollRuns;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollSlipFactory extends Factory
{
    protected $model = PayrollSlip::class;

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
        // $table->foreignId('payroll_run_id')->constrained();
        //         $table->foreignId('employee_id')->constrained();
        //         $table->foreignId('organization_id')->constrained()->onDelete('cascade');
        //         $table->decimal('gross_pay', 12, 2);
        //         $table->decimal('deductions', 12, 2);
        //         $table->decimal('net_pay', 12, 2);

        return [
            'payroll_run_id' => PayrollRuns::factory(),
            'employee_id' => Employee::factory(),
            'organization_id' => Organization::factory(),
            // 'basic_salary' => $basicSalary,
            // 'housing_allowance' => $housingAllowance,
            // 'transport_allowance' => $transportAllowance,
            // 'overtime_pay' => $overtimePay,
            // 'bonus' => $bonus,
            'gross_pay' => $grossPay,
            'deductions' =>  $this->faker->randomFloat(2, 0, 200),
            // 'insurance_deduction' => $insuranceDeduction,
            // 'other_deductions' => $otherDeductions,
            // 'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            // 'status' => $this->faker->randomElement(['generated', 'sent', 'viewed']),
            // 'generated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            // 'sent_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            // 'viewed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function generated()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'generated',
                'sent_at' => null,
                'viewed_at' => null,
            ];
        });
    }

    public function sent()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'sent',
                'sent_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'viewed_at' => null,
            ];
        });
    }

    public function viewed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'viewed',
                'sent_at' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
                'viewed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }

    public function forPayrollRun(PayrollRuns $payrollRun)
    {
        return $this->state(function (array $attributes) use ($payrollRun) {
            return [
                'payroll_run_id' => $payrollRun->id,
                'organization_id' => $payrollRun->organization_id,
            ];
        });
    }

    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
                'organization_id' => $user->current_organization_id,
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

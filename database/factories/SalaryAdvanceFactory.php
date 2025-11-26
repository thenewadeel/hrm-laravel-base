<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\SalaryAdvance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalaryAdvance>
 */
class SalaryAdvanceFactory extends Factory
{
    protected $model = SalaryAdvance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(500, 5000);
        $repaymentMonths = $this->faker->numberBetween(3, 12);

        return [
            'employee_id' => Employee::factory(),
            'organization_id' => 1, // Will be overridden in tests
            'approved_by' => null,
            'advance_reference' => 'ADV-'.date('Y').'-'.str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'amount' => $amount,
            'balance_amount' => $amount,
            'repayment_months' => $repaymentMonths,
            'monthly_deduction' => $amount / $repaymentMonths,
            'months_repaid' => 0,
            'request_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'approval_date' => $this->faker->optional(0.7)->dateTimeBetween('-6 months', 'now'),
            'first_deduction_month' => $this->faker->dateTimeBetween('now', '+1 month'),
            'reason' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['pending', 'approved', 'active', 'completed']),
            'approval_notes' => $this->faker->optional()->sentence,
            'approved_at' => $this->faker->optional(0.7)->dateTime(),
        ];
    }
}

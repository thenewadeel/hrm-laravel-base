<?php

namespace Database\Factories;

use App\Models\Accounting\JournalEntry;
use App\Models\PayrollRuns;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollRunsFactory extends Factory
{
    protected $model = PayrollRuns::class;

    public function definition()
    {
        $period = $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m');
        $startDate = $this->faker->dateTimeBetween("-1 month", "now")->format('Y-m-01');
        $endDate = date('Y-m-t', strtotime($startDate));

        $totalGross = $this->faker->randomFloat(2, 50000, 200000);
        $totalDeductions = $totalGross * 0.25;
        $totalNet = $totalGross - $totalDeductions;

        return [
            'organization_id' => Organization::factory(),
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $this->faker->randomElement(['draft', 'calculated', 'processed', 'paid']),
            'total_gross' => $totalGross,
            // 'total_deductions' => $totalDeductions,
            'total_net' => $totalNet,
            // 'employee_count' => $this->faker->numberBetween(5, 50),
            'journal_entry_id' => JournalEntry::factory(),
            // 'processed_by' => null,
            // 'processed_at' => null,
            // 'paid_at' => null,
        ];
    }

    public function draft()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
                'processed_by' => null,
                'processed_at' => null,
                'paid_at' => null,
            ];
        });
    }

    public function calculated()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'calculated',
                'processed_by' => null,
                'processed_at' => null,
                'paid_at' => null,
            ];
        });
    }

    public function processed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processed',
                'processed_by' => $this->faker->name(),
                'processed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'paid_at' => null,
            ];
        });
    }

    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'processed_by' => $this->faker->name(),
                'processed_at' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
                'paid_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }

    public function forPeriod(string $period)
    {
        return $this->state(function (array $attributes) use ($period) {
            $startDate = "{$period}-01";
            $endDate = date('Y-m-t', strtotime($startDate));

            return [
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate,
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

    public function withJournalEntry(JournalEntry $journalEntry)
    {
        return $this->state(function (array $attributes) use ($journalEntry) {
            return [
                'journal_entry_id' => $journalEntry->id,
            ];
        });
    }
}

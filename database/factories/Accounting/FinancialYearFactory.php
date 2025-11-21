<?php

namespace Database\Factories\Accounting;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\FinancialYear>
 */
class FinancialYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = $this->faker->year;
        $startYear = $year;
        $endYear = $year + 1;

        return [
            'organization_id' => Organization::factory(),
            'name' => "Fiscal Year {$startYear}-{$endYear}",
            'code' => "FY{$startYear}-{$endYear}",
            'start_date' => "{$startYear}-01-01",
            'end_date' => "{$endYear}-12-31",
            'status' => $this->faker->randomElement(['draft', 'active', 'closed']),
            'is_locked' => $this->faker->boolean(20),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'is_locked' => false,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'is_locked' => true,
            'closed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'is_locked' => false,
        ]);
    }
}

<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FinancialYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\OpeningBalance>
 */
class OpeningBalanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isDebit = $this->faker->boolean(50);

        return [
            'organization_id' => fn () => FinancialYear::factory()->create()->organization_id,
            'financial_year_id' => FinancialYear::factory(),
            'chart_of_account_id' => ChartOfAccount::factory(),
            'debit_amount' => $isDebit ? $this->faker->randomFloat(2, 100, 100000) : 0,
            'credit_amount' => ! $isDebit ? $this->faker->randomFloat(2, 100, 100000) : 0,
            'description' => $this->faker->optional(0.5)->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function debit(): static
    {
        return $this->state(fn (array $attributes) => [
            'debit_amount' => $this->faker->randomFloat(2, 100, 100000),
            'credit_amount' => 0,
        ]);
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'debit_amount' => 0,
            'credit_amount' => $this->faker->randomFloat(2, 100, 100000),
        ]);
    }
}

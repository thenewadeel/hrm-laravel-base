<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\FinancialYear;
use App\Models\Accounting\JournalEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\ClosingEntry>
 */
class ClosingEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => fn () => FinancialYear::factory()->create()->organization_id,
            'financial_year_id' => FinancialYear::factory(),
            'journal_entry_id' => JournalEntry::factory(),
            'type' => $this->faker->randomElement(['revenue_closure', 'expense_closure', 'profit_transfer']),
            'amount' => $this->faker->randomFloat(2, 100, 100000),
            'description' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function revenueClosure(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'revenue_closure',
            'description' => 'Revenue accounts closure',
        ]);
    }

    public function expenseClosure(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense_closure',
            'description' => 'Expense accounts closure',
        ]);
    }

    public function profitTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'profit_transfer',
            'description' => 'Net income transfer to retained earnings',
        ]);
    }
}

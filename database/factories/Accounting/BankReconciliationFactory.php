<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankReconciliation;
use App\Models\Accounting\BankStatement;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\BankReconciliation>
 */
class BankReconciliationFactory extends Factory
{
    protected $model = BankReconciliation::class;

    public function definition(): array
    {
        $statementBalance = $this->faker->randomFloat(2, 1000, 50000);
        $bookBalance = $this->faker->randomFloat(2, 1000, 50000);
        $difference = $statementBalance - $bookBalance;

        return [
            'organization_id' => Organization::factory(),
            'bank_account_id' => BankAccount::factory(),
            'bank_statement_id' => BankStatement::factory(),
            'reconciliation_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'statement_balance' => $statementBalance,
            'book_balance' => $bookBalance,
            'difference' => $difference,
            'outstanding_deposits' => $this->faker->randomFloat(2, 0, 5000),
            'outstanding_withdrawals' => $this->faker->randomFloat(2, 0, 3000),
            'transactions_reconciled' => $this->faker->numberBetween(10, 50),
            'total_transactions' => $this->faker->numberBetween(50, 100),
            'status' => $this->faker->randomElement(['in_progress', 'completed', 'failed']),
            'notes' => $this->faker->optional()->sentence(),
            'reconciled_by' => User::factory(),
            'reconciled_at' => $this->faker->optional()->dateTime(),
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'in_progress']);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'reconciled_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'failed']);
    }

    public function balanced(): static
    {
        $balance = $this->faker->randomFloat(2, 1000, 50000);

        return $this->state(fn (array $attributes) => [
            'statement_balance' => $balance,
            'book_balance' => $balance,
            'difference' => 0,
        ]);
    }
}

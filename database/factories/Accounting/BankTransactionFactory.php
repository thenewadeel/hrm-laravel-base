<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankStatement;
use App\Models\Accounting\BankTransaction;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\BankTransaction>
 */
class BankTransactionFactory extends Factory
{
    protected $model = BankTransaction::class;

    public function definition(): array
    {
        $transactionType = $this->faker->randomElement(['debit', 'credit']);
        $amount = $this->faker->randomFloat(2, 10, 5000);

        return [
            'organization_id' => Organization::factory(),
            'bank_account_id' => BankAccount::factory(),
            'bank_statement_id' => BankStatement::factory(),
            'transaction_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'transaction_number' => $this->faker->optional()->unique()->numerify('TXN########'),
            'reference_number' => $this->faker->optional()->numerify('REF########'),
            'description' => $this->faker->sentence(6),
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'balance_after' => $this->faker->randomFloat(2, 1000, 50000),
            'status' => $this->faker->randomElement(['pending', 'cleared', 'reconciled']),
            'reconciliation_status' => $this->faker->randomElement(['unmatched', 'matched', 'partially_matched']),
            'matched_ledger_entry_id' => null,
            'notes' => $this->faker->optional()->sentence(),
            'metadata' => $this->faker->optional()->randomElements(['category' => $this->faker->word, 'tags' => $this->faker->words(3)]),
        ];
    }

    public function debit(): static
    {
        return $this->state(fn (array $attributes) => ['transaction_type' => 'debit']);
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes) => ['transaction_type' => 'credit']);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending']);
    }

    public function cleared(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'cleared']);
    }

    public function reconciled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'reconciled']);
    }

    public function unmatched(): static
    {
        return $this->state(fn (array $attributes) => ['reconciliation_status' => 'unmatched']);
    }

    public function matched(): static
    {
        return $this->state(fn (array $attributes) => ['reconciliation_status' => 'matched']);
    }
}

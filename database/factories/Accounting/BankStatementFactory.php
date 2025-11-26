<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankStatement;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\BankStatement>
 */
class BankStatementFactory extends Factory
{
    protected $model = BankStatement::class;

    public function definition(): array
    {
        $statementDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $openingBalance = $this->faker->randomFloat(2, 1000, 50000);
        $totalDebits = $this->faker->randomFloat(2, 0, 10000);
        $totalCredits = $this->faker->randomFloat(2, 0, 15000);
        $closingBalance = $openingBalance - $totalDebits + $totalCredits;

        return [
            'organization_id' => Organization::factory(),
            'bank_account_id' => BankAccount::factory(),
            'statement_number' => 'STMT-'.$this->faker->unique()->randomNumber(6),
            'statement_date' => $statementDate,
            'period_start_date' => $this->faker->dateTimeBetween('-1 year', $statementDate),
            'period_end_date' => $statementDate,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'transaction_count' => $this->faker->numberBetween(10, 100),
            'status' => $this->faker->randomElement(['imported', 'reconciled', 'partial']),
            'notes' => $this->faker->optional()->sentence(),
            'file_path' => $this->faker->optional()->filePath(),
        ];
    }

    public function imported(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'imported']);
    }

    public function reconciled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'reconciled']);
    }

    public function partial(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'partial']);
    }
}

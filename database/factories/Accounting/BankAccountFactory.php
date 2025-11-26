<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\BankAccount>
 */
class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'chart_of_account_id' => ChartOfAccount::factory(),
            'account_number' => $this->faker->unique()->bankAccountNumber(),
            'account_name' => $this->faker->company().' Account',
            'bank_name' => $this->faker->company(),
            'branch_name' => $this->faker->city(),
            'routing_number' => $this->faker->randomNumber(9, true),
            'swift_code' => $this->faker->swiftBicNumber(),
            'currency' => 'USD',
            'opening_balance' => $this->faker->randomFloat(2, 1000, 50000),
            'current_balance' => $this->faker->randomFloat(2, 1000, 50000),
            'opening_balance_date' => $this->faker->date(),
            'account_type' => $this->faker->randomElement(['checking', 'savings', 'money_market', 'cd']),
            'status' => 'active',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function checking(): static
    {
        return $this->state(fn (array $attributes) => ['account_type' => 'checking']);
    }

    public function savings(): static
    {
        return $this->state(fn (array $attributes) => ['account_type' => 'savings']);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'inactive']);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'closed']);
    }
}

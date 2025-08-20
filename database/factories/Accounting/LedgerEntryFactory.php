<?php
// database/factories/LedgerEntryFactory.php

namespace Database\Factories\Accounting;

use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class LedgerEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'entry_date' => now(),
            'chart_of_account_id' => ChartOfAccount::factory(),
            'type' => $this->faker->randomElement(['debit', 'credit']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->sentence(),
        ];
    }
}

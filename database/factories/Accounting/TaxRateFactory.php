<?php

namespace Database\Factories\Accounting;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\TaxRate>
 */
class TaxRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => strtoupper(fake()->lexify('???')),
            'type' => fake()->randomElement(['sales', 'purchase', 'withholding', 'income', 'vat', 'service', 'other']),
            'rate' => fake()->randomFloat(4, 0, 25),
            'tax_jurisdiction_id' => null,
            'is_compound' => false,
            'is_active' => true,
            'effective_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'end_date' => null,
            'description' => fake()->sentence(),
            'applicable_accounts' => null,
            'gl_account_code' => null,
        ];
    }
}

<?php

namespace Database\Factories\Accounting;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\TaxFiling>
 */
class TaxFilingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tax_jurisdiction_id' => null, // Will be set in test
            'tax_rate_id' => null, // Will be set in test
            'filing_number' => 'FILE-'.fake()->numerify('######'),
            'filing_type' => fake()->randomElement(['monthly', 'quarterly', 'annual', 'special']),
            'period_start' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'period_end' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'filing_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'due_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'status' => fake()->randomElement(['draft', 'filed', 'accepted', 'rejected', 'paid']),
            'total_tax_collected' => fake()->randomFloat(2, 100, 50000),
            'total_tax_paid' => fake()->randomFloat(2, 0, 50000),
            'tax_due' => fake()->randomFloat(2, 0, 10000),
            'penalty_amount' => fake()->randomFloat(2, 0, 1000),
            'interest_amount' => fake()->randomFloat(2, 0, 500),
            'confirmation_number' => fake()->optional()->numerify('##########'),
            'filing_notes' => fake()->optional()->sentence(),
            'filing_data' => [
                'transaction_count' => fake()->numberBetween(10, 1000),
                'average_tax_rate' => fake()->randomFloat(4, 5, 15),
            ],
            'created_by' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}

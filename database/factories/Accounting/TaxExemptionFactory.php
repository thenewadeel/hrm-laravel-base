<?php

namespace Database\Factories\Accounting;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\TaxExemption>
 */
class TaxExemptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exemptible_type' => null, // Will be set in test
            'exemptible_id' => null, // Will be set in test
            'tax_rate_id' => null,
            'certificate_number' => 'EXEMPT-'.fake()->numerify('######'),
            'exemption_type' => fake()->randomElement(['resale', 'charitable', 'government', 'manufacturing', 'educational', 'religious', 'export', 'agricultural', 'research', 'other']),
            'exemption_percentage' => fake()->randomElement([50, 75, 100]),
            'issue_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'expiry_date' => fake()->dateTimeBetween('now', '+5 years')->format('Y-m-d'),
            'is_active' => true,
            'reason' => fake()->sentence(),
            'applicable_taxes' => null,
            'issuing_authority' => fake()->company(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

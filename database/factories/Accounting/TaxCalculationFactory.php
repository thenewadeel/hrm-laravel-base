<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\TaxCalculation;
use App\Models\Accounting\TaxRate;
use App\Models\Accounting\Voucher;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaxCalculation>
 */
class TaxCalculationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'calculable_type' => Voucher::class,
            'calculable_id' => Voucher::factory(),
            'tax_rate_id' => TaxRate::factory(),
            'tax_exemption_id' => null,
            'base_amount' => fake()->randomFloat(2, 100, 10000),
            'taxable_amount' => fake()->randomFloat(2, 100, 10000),
            'tax_rate' => fake()->randomFloat(4, 0, 25),
            'tax_amount' => fake()->randomFloat(2, 5, 2000),
            'calculation_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'calculation_method' => 'percentage',
            'calculation_details' => [
                'exemption_percentage' => 0,
                'is_compound' => false,
            ],
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

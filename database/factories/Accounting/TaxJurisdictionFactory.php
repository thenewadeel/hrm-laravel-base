<?php

namespace Database\Factories\Accounting;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\TaxJurisdiction>
 */
class TaxJurisdictionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Tax Authority',
            'code' => strtoupper(fake()->lexify('???')),
            'type' => fake()->randomElement(['country', 'state', 'province', 'city', 'county', 'municipality', 'other']),
            'parent_code' => null,
            'tax_id_number' => fake()->numerify('#########'),
            'is_active' => true,
            'address' => fake()->address(),
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'filing_requirements' => [
                'frequency' => fake()->randomElement(['monthly', 'quarterly', 'annual']),
                'due_days' => fake()->numberBetween(15, 45),
            ],
        ];
    }
}

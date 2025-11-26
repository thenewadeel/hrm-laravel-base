<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AllowanceType>
 */
class AllowanceTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'code' => strtoupper($this->faker->lexify('???')),
            'description' => $this->faker->sentence,
            'calculation_type' => $this->faker->randomElement(['fixed_amount', 'percentage_of_basic', 'percentage_of_gross']),
            'default_value' => $this->faker->numberBetween(50, 500),
            'is_taxable' => $this->faker->boolean(80), // 80% chance of being taxable
            'is_recurring' => $this->faker->boolean(90), // 90% chance of being recurring
            'account_code' => $this->faker->numerify('####'),
        ];
    }
}

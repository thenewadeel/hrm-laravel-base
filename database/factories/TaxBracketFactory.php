<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxBracket>
 */
class TaxBracketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'min_income' => $this->faker->numberBetween(0, 10000),
            'max_income' => $this->faker->optional(0.3)->numberBetween(1000, 50000),
            'rate' => $this->faker->numberBetween(5, 35),
            'base_tax' => $this->faker->numberBetween(0, 2000),
            'exemption_amount' => $this->faker->numberBetween(0, 1000),
            'effective_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}

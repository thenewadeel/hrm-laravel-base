<?php
// database/factories/ChartOfAccountFactory.php

namespace Database\Factories\Accounting;

use Illuminate\Database\Eloquent\Factories\Factory;

class ChartOfAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(['asset', 'liability', 'equity', 'revenue', 'expense']),
            'description' => $this->faker->sentence(),
            'organization_id' => auth()->user()->current_organization_id ?? 1,
        ];
    }
}

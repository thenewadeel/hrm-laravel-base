<?php

namespace Database\Factories\Accounting;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\FixedAssetCategory>
 */
class FixedAssetCategoryFactory extends Factory
{
    /**
     * Define model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Buildings',
            'Machinery & Equipment',
            'Furniture & Fixtures',
            'Vehicles',
            'Computer Equipment',
            'Office Equipment',
            'Land Improvements',
        ];

        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->randomElement($categories),
            'code' => $this->faker->unique()->lexify('CAT-???'),
            'description' => $this->faker->sentence(),
            'default_useful_life_years' => $this->faker->numberBetween(3, 20),
            'default_depreciation_method' => $this->faker->randomElement(['straight_line', 'declining_balance', 'sum_of_years']),
            'default_depreciation_rate' => $this->faker->numberBetween(10, 30),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

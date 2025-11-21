<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\FixedAssetCategory;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\FixedAsset>
 */
class FixedAssetFactory extends Factory
{
    /**
     * Define model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchaseCost = $this->faker->numberBetween(1000, 50000);
        $usefulLife = $this->faker->numberBetween(3, 10);

        return [
            'organization_id' => Organization::factory(),
            'fixed_asset_category_id' => FixedAssetCategory::factory(),
            'chart_of_account_id' => \App\Models\Accounting\ChartOfAccount::factory(),
            'accumulated_depreciation_account_id' => \App\Models\Accounting\ChartOfAccount::factory(),
            'asset_tag' => 'AST-'.$this->faker->unique()->numerify('#####'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'serial_number' => $this->faker->optional()->numerify('SN-########'),
            'location' => $this->faker->city(),
            'department' => $this->faker->optional()->word(),
            'assigned_to' => $this->faker->optional()->name(),
            'purchase_date' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
            'purchase_cost' => $purchaseCost,
            'salvage_value' => $purchaseCost * 0.1, // 10% salvage value
            'useful_life_years' => $usefulLife,
            'depreciation_method' => $this->faker->randomElement(['straight_line', 'declining_balance', 'sum_of_years']),
            'current_book_value' => $purchaseCost,
            'accumulated_depreciation' => 0,
            'status' => 'active',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function disposed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disposed',
        ]);
    }
}

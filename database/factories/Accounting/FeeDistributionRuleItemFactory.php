<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FeeDistributionRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\FeeDistributionRuleItem>
 */
class FeeDistributionRuleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $distributionType = $this->faker->randomElement(['percentage', 'fixed']);

        return [
            'fee_distribution_rule_id' => FeeDistributionRule::factory(),
            'chart_of_account_id' => ChartOfAccount::factory(),
            'distribution_type' => $distributionType,
            'percentage' => $distributionType === 'percentage' ? $this->faker->randomFloat(2, 1, 100) : null,
            'fixed_amount' => $distributionType === 'fixed' ? $this->faker->randomFloat(2, 10, 1000) : null,
            'priority' => $this->faker->numberBetween(1, 10),
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the item is percentage-based.
     */
    public function percentage(?float $percentage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'distribution_type' => 'percentage',
            'percentage' => $percentage ?? $this->faker->randomFloat(2, 1, 100),
            'fixed_amount' => null,
        ]);
    }

    /**
     * Indicate that the item is fixed amount-based.
     */
    public function fixed(?float $amount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'distribution_type' => 'fixed',
            'fixed_amount' => $amount ?? $this->faker->randomFloat(2, 10, 1000),
            'percentage' => null,
        ]);
    }

    /**
     * Indicate that the item has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 1,
        ]);
    }

    /**
     * Indicate that the item has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 10,
        ]);
    }

    /**
     * Indicate that the item has a specific percentage.
     */
    public function withPercentage(float $percentage): static
    {
        return $this->state(fn (array $attributes) => [
            'distribution_type' => 'percentage',
            'percentage' => $percentage,
            'fixed_amount' => null,
        ]);
    }

    /**
     * Indicate that the item has a specific fixed amount.
     */
    public function withFixedAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'distribution_type' => 'fixed',
            'fixed_amount' => $amount,
            'percentage' => null,
        ]);
    }

    /**
     * Indicate that the item has a specific priority.
     */
    public function withPriority(int $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $priority,
        ]);
    }
}

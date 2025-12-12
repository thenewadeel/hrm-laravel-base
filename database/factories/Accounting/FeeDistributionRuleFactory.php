<?php

namespace Database\Factories\Accounting;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\FeeDistributionRule>
 */
class FeeDistributionRuleFactory extends Factory
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
            'name' => $this->faker->words(3, true).' Distribution Rule',
            'fee_type' => $this->faker->randomElement(['subscription', 'late_fee', 'penalty', 'registration', 'renewal', 'other']),
            'rule_type' => $this->faker->randomElement(['percentage', 'fixed', 'priority']),
            'conditions' => null,
            'is_active' => true,
            'priority' => $this->faker->numberBetween(1, 10),
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the rule is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the rule has conditions.
     */
    public function withConditions(array $conditions = []): static
    {
        return $this->state(fn (array $attributes) => [
            'conditions' => array_merge([
                'min_amount' => $this->faker->numberBetween(50, 200),
                'max_amount' => $this->faker->numberBetween(500, 2000),
            ], $conditions),
        ]);
    }

    /**
     * Indicate that the rule is for subscription fees.
     */
    public function forSubscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'fee_type' => 'subscription',
        ]);
    }

    /**
     * Indicate that the rule is for late fees.
     */
    public function forLateFee(): static
    {
        return $this->state(fn (array $attributes) => [
            'fee_type' => 'late_fee',
        ]);
    }

    /**
     * Indicate that the rule is percentage-based.
     */
    public function percentageBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'percentage',
        ]);
    }

    /**
     * Indicate that the rule is fixed-based.
     */
    public function fixedBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'fixed',
        ]);
    }

    /**
     * Indicate that the rule has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 1,
        ]);
    }

    /**
     * Indicate that the rule has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 10,
        ]);
    }
}

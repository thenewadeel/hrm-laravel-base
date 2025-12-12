<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\FeeDistributionRule;
use App\Models\Membership\MemberFee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\FeeDistributionLog>
 */
class FeeDistributionLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['success', 'failed', 'partial']);
        $hasBreakdown = $status === 'success';

        return [
            'organization_id' => 1,
            'member_fee_id' => MemberFee::factory(),
            'fee_distribution_rule_id' => FeeDistributionRule::factory(),
            'journal_entry_id' => $status === 'success' ? null : null, // Will be set in successful() method
            'total_amount' => $this->faker->randomFloat(2, 100, 5000),
            'distribution_breakdown' => $hasBreakdown ? [
                [
                    'account_id' => $this->faker->numberBetween(1, 100),
                    'type' => 'percentage',
                    'value' => $this->faker->randomFloat(2, 1, 100),
                    'amount' => $this->faker->randomFloat(2, 50, 1000),
                ],
                [
                    'account_id' => $this->faker->numberBetween(1, 100),
                    'type' => 'fixed',
                    'value' => $this->faker->randomFloat(2, 10, 500),
                    'amount' => $this->faker->randomFloat(2, 50, 1000),
                ],
            ] : [], // Empty array for failed/partial distributions
            'status' => $status,
            'error_message' => $status === 'failed' ? $this->faker->sentence() : null,
            'distributed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the distribution was successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'success',
            'error_message' => null,
            'journal_entry_id' => function () use ($attributes) {
                // Create a proper journal entry with organization_id and user
                $organizationId = $attributes['organization_id'] ?? 1;
                $organization = \App\Models\Organization::find($organizationId) ?: \App\Models\Organization::factory()->create(['id' => $organizationId]);
                $user = \App\Models\User::factory()->create(['current_organization_id' => $organizationId]);

                $journalEntry = \App\Models\Accounting\JournalEntry::factory()->create([
                    'organization_id' => $organizationId,
                    'created_by' => $user->id,
                ]);

                return $journalEntry->id;
            },
        ]);
    }

    /**
     * Indicate that the distribution failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => $this->faker->sentence(),
            'journal_entry_id' => null,
            'distribution_breakdown' => [],
        ]);
    }

    /**
     * Indicate that the distribution was partial.
     */
    public function partial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'partial',
            'error_message' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the log has no journal entry.
     */
    public function withoutJournalEntry(): static
    {
        return $this->state(fn (array $attributes) => [
            'journal_entry_id' => null,
        ]);
    }

    /**
     * Indicate that the log has no rule.
     */
    public function withoutRule(): static
    {
        return $this->state(fn (array $attributes) => [
            'fee_distribution_rule_id' => null,
        ]);
    }

    /**
     * Indicate that the log has a specific total amount.
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $amount,
        ]);
    }

    /**
     * Indicate that the log was created recently.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'distributed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the log was created in the past.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'distributed_at' => $this->faker->dateTimeBetween('-1 year', '-6 months'),
        ]);
    }
}

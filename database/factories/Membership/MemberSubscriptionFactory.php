<?php

namespace Database\Factories\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership\MemberSubscription>
 */
class MemberSubscriptionFactory extends Factory
{
    protected $model = MemberSubscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $billingPeriod = $this->faker->randomElement([1, 3, 6, 12]); // months
        $endDate = (clone $startDate)->modify("+{$billingPeriod} months");
        
        return [
            'organization_id' => Organization::factory(),
            'member_id' => Member::factory(),
            'subscription_plan_id' => SubscriptionPlan::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'total_amount' => $this->faker->randomFloat(2, 50, 1000),
            'paid_amount' => $this->faker->randomFloat(2, 0, 1000),
            'auto_renew' => $this->faker->boolean(70),
            'notes' => $this->faker->optional(0.3)->sentence,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'end_date' => $this->faker->dateTimeBetween('+1 day', '+1 year'),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'start_date' => $this->faker->dateTimeBetween('-2 years', '-1 year'),
            'end_date' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'end_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['total_amount'],
        ]);
    }

    public function partiallyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['total_amount'] * $this->faker->randomFloat(2, 0.1, 0.8),
        ]);
    }

    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => 0,
        ]);
    }

    public function withAutoRenew(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_renew' => true,
        ]);
    }

    public function withoutAutoRenew(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_renew' => false,
        ]);
    }
}
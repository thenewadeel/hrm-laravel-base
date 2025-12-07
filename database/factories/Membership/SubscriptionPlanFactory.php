<?php

namespace Database\Factories\Membership;

use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership\SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $planTypes = ['individual', 'family', 'corporate'];
        $billingFrequencies = ['monthly', 'quarterly', 'semi_annually', 'annually'];

        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(10),
            'plan_type' => $this->faker->randomElement($planTypes),
            'billing_frequency' => $this->faker->randomElement($billingFrequencies),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'family_members_included' => $this->faker->numberBetween(0, 5),
            'additional_family_member_fee' => $this->faker->randomFloat(2, 5, 50),
            'benefits' => [
                $this->faker->sentence(3),
                $this->faker->sentence(3),
                $this->faker->sentence(3),
            ],
            'is_active' => true,
        ];
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_type' => 'individual',
            'family_members_included' => 0,
            'additional_family_member_fee' => 0,
        ]);
    }

    public function family(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_type' => 'family',
            'family_members_included' => $this->faker->numberBetween(2, 5),
            'additional_family_member_fee' => $this->faker->randomFloat(2, 10, 30),
        ]);
    }

    public function corporate(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_type' => 'corporate',
            'family_members_included' => $this->faker->numberBetween(5, 10),
            'additional_family_member_fee' => $this->faker->randomFloat(2, 5, 20),
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_frequency' => 'monthly',
        ]);
    }

    public function annually(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_frequency' => 'annually',
            'amount' => $attributes['amount'] * 10, // Annual discount
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function familyPlan(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => 50.00,
            'family_members_included' => 1,
            'additional_family_member_fee' => 15.00,
        ]);
    }
}

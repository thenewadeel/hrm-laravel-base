<?php

namespace Database\Factories\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership\MemberFee>
 */
class MemberFeeFactory extends Factory
{
    protected $model = MemberFee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $feeTypes = ['subscription', 'late_fee', 'penalty', 'additional_service'];
        $statuses = ['pending', 'paid', 'waived', 'overdue'];
        
        return [
            'organization_id' => Organization::factory(),
            'member_id' => Member::factory(),
            'fee_type' => $this->faker->randomElement($feeTypes),
            'description' => $this->faker->sentence(6),
            'amount' => $this->faker->randomFloat(2, 5, 500),
            'due_date' => $this->faker->dateTimeBetween('-30 days', '+30 days'),
            'paid_date' => null,
            'status' => 'pending',
            'payment_method' => null,
            'payment_reference' => null,
        ];
    }

    public function subscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'fee_type' => 'subscription',
            'description' => 'Annual membership subscription fee',
        ]);
    }

    public function lateFee(): static
    {
        return $this->state(fn (array $attributes) => [
            'fee_type' => 'late_fee',
            'description' => 'Late payment penalty',
            'amount' => $this->faker->randomFloat(2, 10, 50),
        ]);
    }

    public function penalty(): static
    {
        return $this->state(fn (array $attributes) => [
            'fee_type' => 'penalty',
            'description' => $this->faker->randomElement(['Violation penalty', 'Rule breach penalty']),
            'amount' => $this->faker->randomFloat(2, 25, 200),
        ]);
    }

    public function additionalService(): static
    {
        return $this->state(fn (array $attributes) => [
            'fee_type' => 'additional_service',
            'description' => $this->faker->randomElement([
                'Personal training session',
                'Equipment rental',
                'Special event registration',
                'Premium service upgrade',
            ]),
            'amount' => $this->faker->randomFloat(2, 15, 150),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_date' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'bank_transfer', 'online']),
            'payment_reference' => 'PAY-' . $this->faker->unique()->numerify('##########'),
        ]);
    }

    public function waived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waived',
            'paid_date' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => $this->faker->dateTimeBetween('-60 days', '-1 day'),
        ]);
    }

    public function dueInPast(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    public function dueInFuture(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
        ]);
    }
}
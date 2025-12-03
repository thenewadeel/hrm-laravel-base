<?php

namespace Database\Factories\Membership;

use App\Models\Membership\Member;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership\Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'membership_number' => 'MEM-' . $this->faker->unique()->numerify('######'),
            'title' => $this->faker->randomElement(['Mr', 'Mrs', 'Ms', 'Dr']),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'date_of_birth' => $this->faker->dateTimeBetween('-70 years', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'country' => $this->faker->country,
            'barcode_number' => 'BC-' . $this->faker->unique()->numerify('##########'),
            'photo_path' => null,
            'status' => 'active',
            'join_date' => $this->faker->dateTimeBetween('-2 years', '-1 month'),
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
            'notes' => $this->faker->optional(0.3)->sentence,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expiry_date' => $this->faker->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
        ]);
    }

    public function withPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'photo_path' => 'members/photos/' . $this->faker->uuid() . '.jpg',
        ]);
    }
}
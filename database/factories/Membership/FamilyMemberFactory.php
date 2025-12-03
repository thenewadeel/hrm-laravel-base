<?php

namespace Database\Factories\Membership;

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership\FamilyMember>
 */
class FamilyMemberFactory extends Factory
{
    protected $model = FamilyMember::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'primary_member_id' => Member::factory(),
            'relationship' => $this->faker->randomElement(['Spouse', 'Child', 'Parent', 'Sibling', 'Dependent']),
            'title' => $this->faker->randomElement(['Mr', 'Mrs', 'Ms', 'Dr', 'Master', 'Miss']),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'date_of_birth' => $this->faker->dateTimeBetween('-70 years', '-1 year'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'barcode_number' => 'FAM-' . $this->faker->unique()->numerify('##########'),
            'photo_path' => null,
            'status' => 'active',
            'notes' => $this->faker->optional(0.2)->sentence,
        ];
    }

    public function spouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'Spouse',
            'date_of_birth' => $this->faker->dateTimeBetween('-70 years', '-18 years'),
        ]);
    }

    public function child(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'Child',
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-1 year'),
        ]);
    }

    public function dependent(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'Dependent',
            'date_of_birth' => $this->faker->dateTimeBetween('-25 years', '-18 years'),
        ]);
    }

    public function withPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'photo_path' => 'members/family-photos/' . $this->faker->uuid() . '.jpg',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
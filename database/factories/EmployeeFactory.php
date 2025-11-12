<?php
// database/factories/EmployeeFactory.php
namespace Database\Factories;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null, // Not all employees need user accounts
            'organization_id' => Organization::factory(),
            'organization_unit_id' => OrganizationUnit::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'zip_code' => $this->faker->postcode(),
            'photo' => $this->faker->optional()->imageUrl(),
            'is_active' => true,
            'is_admin' => false,
        ];
    }
    public function withBiometricId()
    {
        return $this->state(function (array $attributes) {
            return [
                'biometric_id' => $this->faker->unique()->numerify('BIO#####'),
            ];
        });
    }
    // State methods for Employee-only concerns
    public function withUserAccount(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::factory(),
            ];
        });
    }

    public function forExistingUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
                'email' => $user->email, // Keep email consistent
                'first_name' => $user->name, // Or extract first name
            ];
        });
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn(array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    public function forUnit(OrganizationUnit $unit): static
    {
        return $this->state(fn(array $attributes) => [
            'organization_unit_id' => $unit->id,
            'organization_id' => $unit->organization_id,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

// database/factories/OrganizationUserFactory.php

class OrganizationUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'organization_id' => Organization::factory(),
            'organization_unit_id' => OrganizationUnit::factory(),
            'position' => $this->faker->jobTitle(),
            'roles' => ['user'],
            'permissions' => [],
            'custom_fields' => [],
        ];
    }

    public function withRoles(array $roles): static
    {
        return $this->state(fn(array $attributes) => [
            'roles' => $roles,
        ]);
    }

    public function withPermissions(array $permissions): static
    {
        return $this->state(fn(array $attributes) => [
            'permissions' => $permissions,
        ]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn(array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'roles' => ['admin'],
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn(array $attributes) => [
            'roles' => ['manager'],
        ]);
    }
}

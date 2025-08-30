<?php
// tests/Traits/SetupOrganization.php

namespace Tests\Traits;

use App\Models\Organization;
use App\Models\User;

trait SetupOrganization
{
    protected function createOrganizationWithUser($user = null, array $roles = ['admin'])
    {
        $organization = Organization::factory()->create();
        $user = $user ?: User::factory()->create();

        $organization->users()->attach($user, [
            'roles' => json_encode($roles)
        ]);

        return [$organization, $user];
    }
    // protected function createOrganizationWithUser($userAttributes = [], $organizationAttributes = [])
    // {
    //     $user = User::factory()->create(array_merge(['role' => 'admin'], $userAttributes));
    //     $organization = Organization::factory()->create($organizationAttributes);

    //     return [$organization, $user];
    // }

    protected function createOrganizationsForSorting()
    {
        $user = User::factory()->create();

        $organizations = [
            Organization::factory()->create(['name' => 'Beta Organization']),
            Organization::factory()->create(['name' => 'Alpha Organization']),
            Organization::factory()->create(['name' => 'Gamma Organization']),
        ];

        foreach ($organizations as $organization) {
            $organization->users()->attach($user, [
                'roles' => json_encode(['admin'])
            ]);
        }
        return [$organizations, $user];
    }
}

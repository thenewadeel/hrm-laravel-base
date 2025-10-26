<?php
// tests/Traits/SetupOrganization.php

namespace Tests\Traits;

use App\Models\Organization;
use App\Models\User;

trait SetupOrganization
{
    public $user;
    public $organization;
    protected function setupOrganization()
    {
        // dd('o');
        // parent::setUp();
        // $this->createOrganizationWithUser();
        $this->user = $this->createOrganizationWithUser();
        $this->actingAs($this->user);
        return $this->user;
    }
    protected function createOrganizationWithUser($user = null, array $roles = ['admin'])
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();

        // dd($user);
        $organization->users()->attach($user, [
            'roles' => json_encode($roles),
            'organization_id' => $organization->id
        ]);
        $this->$user = $user;
        $this->organization = $organization;
        // return [$organization, $user];
        return $user;
    }
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
                'roles' => json_encode(['admin']),
                'organization_id' => $organization->id
            ]);
        }
        $this->actingAs($user);
        return [$organizations, $user];
    }
}

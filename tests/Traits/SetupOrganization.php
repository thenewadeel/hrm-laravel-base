<?php
// tests/Traits/SetupOrganization.php

namespace Tests\Traits;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\OrganizationUser;
use App\Models\User;
use App\Roles\InventoryRoles;
use App\Roles\OrganizationRoles;
use Illuminate\Support\Facades\Auth;

trait SetupOrganization
{
    public User $user;
    public Organization $organization;
    public OrganizationUnit $organizationUnit;
    protected function setupOrganization()
    {
        // parent::setUp();
        // $this->createOrganizationWithUser();
        $this->user = $this->createOrganizationWithUser()['user'];
        $this->actingAs($this->user);
        // dd('o');
        // dd($this->organizationUnit);

        return $this->user;
    }
    protected function createOrganizationWithUser($user = null, array $roles = [OrganizationRoles::ORGANIZATION_ADMIN])
    {
        Auth::logout();
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organizationUnit = OrganizationUnit::factory()->create([
            'organization_id' => $organization->id
        ]);

        // $organization->users()->attach($user, [
        //     'roles' => json_encode($roles),
        //     'organization_id' => $organization->id,
        //     // 'organization_unit_id' => $organizationUnit->id
        // ]);
        $user->organizations()->attach($organization, [
            'roles' => json_encode($roles),
            'organization_id' => $organization->id,
            // 'organization_unit_id' => $organizationUnit->id
        ]);
        $permissions = \App\Roles\OrganizationRoles::getPermissionsForRole($roles[0]);

        // Assign permissions to user for the specific organization
        $user->givePermissionTo($permissions, $organization);

        // dd($user->organizations);
        // dd("asduser");
        // dd([$permissions, $organization, $user->organizations]);

        $this->$user = $user;
        $this->organization = $organization;
        $this->organizationUnit = $organizationUnit;
        // dd([
        //     'organization' => $organization,
        //     'organization_unit' => $unit,
        //     'user' => $user,
        //     'unit_users' => $unit->users()->first(),
        //     // 'user_org' => $user->organizations()->first()
        // ]);
        // return [$organization, $user];
        return [
            'organization' => $organization,
            'organization_unit' => $organizationUnit,
            'user' => $user
        ];
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
                'roles' => json_encode([InventoryRoles::INVENTORY_ADMIN]),
                'organization_id' => $organization->id
            ]);
        }
        $this->actingAs($user);
        return [$organizations, $user];
    }
    protected function attachUserToOrganization(User $user, Organization $org, array $roles = []): void
    {
        $user->organizations()->attach($org, [
            'roles' => json_encode($roles),
            'organization_id' => $org->id,
        ]);

        // Automatically set current organization if not set
        if (is_null($user->current_organization_id)) {
            $user->current_organization_id = $org->id;
            $user->save();
        }
    }
}

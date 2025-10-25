<?php

namespace Tests\Traits;

use App\Models\Organization;
use App\Models\User;

trait TenancyTestSetup
{
    /**
     * Define the array of models that should be tested for tenancy scope.
     * Each model MUST use the BelongsToOrganization trait and have a factory.
     *
     * @var array<class-string<\Illuminate\Database\Eloquent\Model>>
     */
    abstract protected function tenantModels(): array;

    protected Organization $orgA;
    protected Organization $orgB;
    protected User $userA;
    protected User $userB;

    /**
     * Executes the setup required for tenancy testing.
     * Should be called from the parent setUp() method.
     */
    protected function setUpTenancy(): void
    {
        // 1. Create two distinct organizations
        $this->orgA = Organization::factory()->create(['name' => 'Org A']);
        $this->orgB = Organization::factory()->create(['name' => 'Org B']);

        // 2. Create users associated with those organizations
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();

        // $organization = Organization::factory()->create();
        // $organization_unit = OrganizationUnit::factory()->create([
        //     'organization_id' => $organization->id
        // ]);
        // $userA = User::factory()->create();

        $this->orgA->users()->attach($this->userA, [
            'roles' => json_encode(["admin"]),
            'organization_id' => $this->orgA->id,
            // 'organization_unit_id' => $organization_unit->id

        ]);
        $this->orgB->users()->attach($this->userB, [
            'roles' => json_encode(["admin"]),
            'organization_id' => $this->orgB->id,
            // 'organization_unit_id' => $organization_unit->id

        ]);



        // 3. Populate test data for each registered model
        $this->populateTenantData();
    }

    /**
     * Creates specific test data for all models listed in $tenantModels.
     */
    protected function populateTenantData(): void
    {
        // FIX: Access the model list via the required method, not the static property.
        foreach ($this->tenantModels() as $modelClass) {
            // Create 2 records for Org A
            $this->actingAs($this->userA);
            $modelClass::factory()->count(2)->create([
                'organization_id' => $this->orgA->id,
            ]);

            $this->actingAs($this->userB);
            // Create 1 record for Org B (Used as a 'foreign' record for isolation checks)
            $modelClass::factory()->create([
                'organization_id' => $this->orgB->id,
            ]);
        }
    }
}

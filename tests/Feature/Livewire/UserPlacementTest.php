<?php

use App\Livewire\UserPlacement;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserPlacementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_assign_a_user_to_a_unit()
    {
        // Arrange
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $organization->users()->attach($user);
        $unit = OrganizationUnit::factory()->create(['organization_id' => $organization->id]);

        // Act
        Livewire::test(UserPlacement::class, ['organizationId' => $organization->id])
            ->call('assignUserToUnit', $user->id, $unit->id);

        // Assert
        $this->assertDatabaseHas('organization_user', [
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'organization_unit_id' => $unit->id
        ]);
    }

    /** @test */
    public function it_can_unassign_a_user_by_dropping_them_onto_the_root()
    {
        // Arrange
        $organization = Organization::factory()->create();
        $unit = OrganizationUnit::factory()->create(['organization_id' => $organization->id]);
        $user = User::factory()->create();
        $organization->users()->attach($user, ['organization_unit_id' => $unit->id]);

        // Act
        Livewire::test(UserPlacement::class, ['organizationId' => $organization->id])
            ->call('assignUserToUnit', $user->id, null);

        // Assert
        $this->assertDatabaseHas('organization_user', [
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'organization_unit_id' => null
        ]);
    }
}

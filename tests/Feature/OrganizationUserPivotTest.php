<?php
// tests/Feature/OrganizationUserPivotTest.php (or wherever you put feature tests)

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationUserPivotTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_all_roles_with_the_new_pivot_model()
    {
        // 1. Setup Data
        $user = User::factory()->create();
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        $user->organizations()->attach($org1->id, [
            'roles' => json_encode(['admin', 'editor']),
            'permissions' => json_encode(['view', 'edit']),
            'position' => 'Manager',
        ]);
        $user->organizations()->attach($org2->id, [
            'roles' => json_encode(['editor', 'viewer']),
            'permissions' => json_encode(['view']),
            'position' => 'Staff',
        ]);

        // 2. Initial Assertion (Using old logic or new refactored logic)
        // This must pass with BOTH to prove the refactor is safe.
        $expectedRoles = ['admin', 'editor', 'viewer'];
        $actualRoles = $user->getAllRoles();

        $this->assertEqualsCanonicalizing($expectedRoles, $actualRoles);

        // 3. Test Pivot Model features
        $pivot = $user->organizations()->first()->pivot;
        $this->assertTrue($pivot instanceof \App\Models\OrganizationUser);
        $this->assertTrue(is_array($pivot->roles), 'Pivot roles should be an array due to casting.');
        $this->assertTrue($pivot->hasRole('admin'), 'Pivot model helper should work.');
    }
}

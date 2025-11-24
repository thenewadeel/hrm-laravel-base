<?php

namespace Tests\Feature\Portal;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\SetupEmployee;

class OrganizationUserIntegrationTest extends TestCase
{
    use RefreshDatabase, SetupEmployee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEmployeeManagement();

        $this->organization = Organization::factory()->create();

        // Create OrganizationUser records with different roles
        OrganizationUser::create([
            'user_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'roles' => json_encode(['employee']),
            'position' => 'Software Developer',
        ]);

        OrganizationUser::create([
            'user_id' => $this->manager->id,
            'organization_id' => $this->organization->id,
            'roles' => json_encode(['manager', 'employee']),
            'position' => 'Engineering Manager',
        ]);

        OrganizationUser::create([
            'user_id' => $this->hrUser->id,
            'organization_id' => $this->organization->id,
            'roles' => json_encode(['hr']),
            'position' => 'HR Manager',
        ]);
    }

    #[Test]
    public function organization_user_roles_are_properly_cast()
    {
        $orgUser = OrganizationUser::where('user_id', $this->employee->id)->first();

        $this->assertIsArray($orgUser->roles);
        $this->assertContains('employee', $orgUser->roles);
    }

    #[Test]
    public function employee_can_access_employee_portal_with_employee_role()
    {
        $this->actingAsRegularEmployee();

        $response = $this->get(route('portal.employee.dashboard'));

        $response->assertStatus(200);
    }

    #[Test]
    public function manager_can_access_both_employee_and_manager_portals()
    {
        $this->actingAsRegularEmployee();

        // Can access employee portal
        $response1 = $this->get(route('portal.employee.dashboard'));
        $response1->assertStatus(200);

        // Can access manager portal
        $response2 = $this->get(route('portal.manager.dashboard'));
        $response2->assertStatus(200);
    }

    #[Test]
    public function user_without_organization_access_denied()
    {
        $user = User::factory()->create(); // No organization association

        $response = $this->actingAs($user)
            ->get(route('portal.employee.dashboard'));

        $response->assertRedirect(route('portal.employee.setup'));
    }

    #[Test]
    public function employee_cannot_access_manager_portal()
    {
        $this->actingAsRegularEmployee();

        $response = $this->get(route('portal.manager.dashboard'));

        $response->assertStatus(403);
    }

    #[Test]
    public function has_role_method_works_correctly()
    {
        $managerUser = OrganizationUser::where('user_id', $this->manager->id)
            ->where('organization_id', $this->organization->id)
            ->first();

        $this->assertNotNull($managerUser, 'OrganizationUser should exist for manager');
        $this->assertIsArray($managerUser->roles, 'Roles should be cast to array');
        $this->assertContains('manager', $managerUser->roles, 'Roles should contain manager');
        $this->assertContains('employee', $managerUser->roles, 'Roles should contain employee');
        $this->assertFalse($managerUser->hasRole('hr'));

        $this->assertTrue($managerUser->hasRole('manager'));
        $this->assertTrue($managerUser->hasRole('employee'));
    }

    #[Test]
    public function has_any_role_method_works_correctly()
    {
        $hrUser = OrganizationUser::where('user_id', $this->hrUser->id)->first();

        $roles = $hrUser->roles;
        if (is_string($roles)) {
            $roles = json_decode($roles, true);
        }

        $this->assertTrue($hrUser->hasAnyRole(['hr', 'admin']));
        $this->assertTrue($hrUser->hasAnyRole(['manager', 'employee']));
        $this->assertFalse($hrUser->hasAnyRole(['admin', 'super_admin']));
    }
}

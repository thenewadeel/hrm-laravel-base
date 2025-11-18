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

    // protected $organization;
    // protected $employee;
    // protected $manager;
    // protected $hrUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEmployeeManagement();

        $this->organization = Organization::factory()->create();

        // $this->employee = User::factory()->create();
        // $this->manager = User::factory()->create();
        // $this->hrUser = User::factory()->create();

        // // Create OrganizationUser records with different roles
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
        //     'roles' => ['hr', 'manager'],
        //     'position' => 'HR Manager'
        // ]);
    }

    #[Test]
    public function organization_user_roles_are_properly_cast()
    {
        $orgUser = OrganizationUser::first();

        $this->assertIsArray($orgUser->roles);
        $this->assertContains('employee', $orgUser->roles);
    }

    #[Test]
    public function employee_can_access_employee_portal_with_employee_role()
    {
        // $this->employee->current_organization_id = $this->organization->id;
        // $this->employee->save();
        $this->actingAsRegularEmployee();

        $response = $this // ->actingAs($this->employee)
            ->get(route('portal.employee.dashboard'));

        $response->assertStatus(200);
    }

    #[Test]
    public function manager_can_access_both_employee_and_manager_portals()
    {
        // $this->manager->current_organization_id = $this->organization->id;
        // $this->manager->save();
        $this->actingAsRegularEmployee();
        // Can access employee portal
        $response1 = $this // ->actingAs($this->manager)
            ->get(route('portal.employee.dashboard'));
        $response1->assertStatus(200);

        // Can access manager portal
        $response2 = $this // ->actingAs($this->manager)
            ->get(route('portal.manager.dashboard'));
        $response2->assertStatus(200);
    }

    #[Test]
    public function user_without_organization_access_denied()
    {
        $user = User::factory()->create(); // No organization association

        $response = $this->actingAs($user)
            ->get(route('portal.employee.dashboard'));

        $response->assertStatus(403);
    }

    #[Test]
    public function employee_cannot_access_manager_portal()
    {
        // $this->employee->current_organization_id = $this->organization->id;
        // $this->employee->save();
        $this->actingAsRegularEmployee();

        $response = $this // ->actingAs($this->employee)
            ->get(route('portal.manager.dashboard'));

        $response->assertStatus(403);
    }

    #[Test]
    public function has_role_method_works_correctly()
    {
        $orgUser = OrganizationUser::where('user_id', $this->manager->id)->first();

        $this->assertTrue($orgUser->hasRole('manager'));
        $this->assertTrue($orgUser->hasRole('employee'));
        $this->assertFalse($orgUser->hasRole('hr'));
    }

    #[Test]
    public function has_any_role_method_works_correctly()
    {
        $orgUser = OrganizationUser::where('user_id', $this->hrUser->id)->first();

        $this->assertTrue($orgUser->hasAnyRole(['hr', 'admin']));
        $this->assertTrue($orgUser->hasAnyRole(['manager', 'employee']));
        $this->assertFalse($orgUser->hasAnyRole(['admin', 'super_admin']));
    }
}

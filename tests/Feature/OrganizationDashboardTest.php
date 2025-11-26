<?php

namespace Tests\Feature\Organization;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\SetupOrganization;

class OrganizationDashboardTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;

    // protected $organization;
    // protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        // $this->organization = Organization::factory()->create();
        // $this->user = User::factory()->create([
        //     'current_organization_id' => $this->organization->id
        // ]);
    }

    #[Test]
    public function authenticated_user_can_access_organization_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('organization.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Organization Management');
    }

    #[Test]
    public function dashboard_displays_correct_organization_metrics()
    {
        // Create test data
        $engineering = OrganizationUnit::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Engineering',
        ]);

        User::factory()->count(5)->create([
            'current_organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('organization.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Employees');
        $response->assertSee('Departments');
    }

    #[Test]
    public function user_can_view_organization_structure()
    {
        $parentUnit = OrganizationUnit::factory()->create([
            'organization_id' => $this->organization->id,
            'parent_id' => null,
        ]);

        $childUnit = OrganizationUnit::factory()->create([
            'organization_id' => $this->organization->id,
            'parent_id' => $parentUnit->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('organization.structure'));

        $response->assertStatus(200);
        $response->assertSee($parentUnit->name);
        $response->assertSee($childUnit->name);
    }

    #[Test]
    public function dashboard_includes_department_distribution_data()
    {
        // Create multiple departments with employees
        $departments = ['Engineering', 'Sales', 'Marketing'];

        foreach ($departments as $dept) {
            $unit = OrganizationUnit::factory()->create([
                'organization_id' => $this->organization->id,
                'name' => $dept,
            ]);

            User::factory()->count(rand(3, 8))->create([
                'current_organization_id' => $this->organization->id,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('organization.dashboard'));

        $response->assertStatus(200);
        foreach ($departments as $dept) {
            $response->assertSee($dept);
        }
    }

    #[Test]
    public function analytics_page_displays_trend_data()
    {
        $response = $this->actingAs($this->user)
            ->get(route('organization.analytics'));

        $response->assertStatus(200);
        $response->assertSee('Analytics');
        $response->assertSee('Headcount Trend');
        $response->assertSee('Performance Metrics');
    }
}

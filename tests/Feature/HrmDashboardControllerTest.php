<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Employee;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class HrmDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;
    protected OrganizationUnit $organizationUnit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->organizationUnit = OrganizationUnit::factory()->create([
            'organization_id' => $this->organization->id
        ]);

        $this->user->organizations()->attach($this->organization, [
            'roles' => json_encode(['hrm_admin'])
        ]);

        $this->actingAs($this->user);
    }

    #[Test]
    public function it_displays_hrm_dashboard_to_authenticated_users()
    {
        $response = $this->get(route('hrm.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('hrm.dashboard');
    }

    #[Test]
    public function it_redirects_guests_to_login_page()
    {
        auth()->logout();

        $response = $this->get(route('hrm.dashboard'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function it_passes_hrm_dashboard_data_to_view()
    {
        $response = $this->get(route('hrm.dashboard'));

        $response->assertViewHas('organization');
        $response->assertViewHas('employeeSummary');
        $response->assertViewHas('attendanceOverview');
        $response->assertViewHas('leaveManagement');
        $response->assertViewHas('performanceKpis');
        $response->assertViewHas('recentActivities');
        $response->assertViewHas('upcomingEvents');
        $response->assertViewHas('organizationUnitStats');
        $response->assertViewHas('trainingDevelopment');
    }

    #[Test]
    public function it_includes_employee_summary_data()
    {
        $response = $this->get(route('hrm.dashboard'));

        $summary = $response->viewData('employeeSummary');

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_employees', $summary);
        $this->assertArrayHasKey('active_employees', $summary);
        $this->assertArrayHasKey('new_hires', $summary);
        $this->assertArrayHasKey('turnover_rate', $summary);
        $this->assertArrayHasKey('organizationUnit_distribution', $summary);

        // Test data types
        $this->assertIsInt($summary['total_employees']);
        $this->assertIsInt($summary['active_employees']);
        $this->assertIsInt($summary['new_hires']);
        $this->assertIsFloat($summary['turnover_rate']);
        $this->assertIsArray($summary['organizationUnit_distribution']);
    }

    #[Test]
    public function it_includes_attendance_overview_data()
    {
        $response = $this->get(route('hrm.dashboard'));

        $attendance = $response->viewData('attendanceOverview');

        $this->assertIsArray($attendance);
        $this->assertArrayHasKey('present_today', $attendance);
        $this->assertArrayHasKey('absent_today', $attendance);
        $this->assertArrayHasKey('late_today', $attendance);
        $this->assertArrayHasKey('attendance_rate', $attendance);
        $this->assertArrayHasKey('weekly_trend', $attendance);

        // Test weekly trend structure
        $this->assertIsArray($attendance['weekly_trend']);
        $this->assertArrayHasKey('labels', $attendance['weekly_trend']);
        $this->assertArrayHasKey('present', $attendance['weekly_trend']);
        $this->assertArrayHasKey('absent', $attendance['weekly_trend']);
    }

    #[Test]
    public function it_includes_leave_management_data()
    {
        $response = $this->get(route('hrm.dashboard'));

        $leave = $response->viewData('leaveManagement');

        $this->assertIsArray($leave);
        $this->assertArrayHasKey('pending_requests', $leave);
        $this->assertArrayHasKey('approved_this_month', $leave);
        $this->assertArrayHasKey('leave_balance_summary', $leave);
        $this->assertArrayHasKey('upcoming_leaves', $leave);

        // Test upcoming leaves structure
        $this->assertIsArray($leave['upcoming_leaves']);
        if (!empty($leave['upcoming_leaves'])) {
            $firstLeave = $leave['upcoming_leaves'][0];
            $this->assertArrayHasKey('employee_name', $firstLeave);
            $this->assertArrayHasKey('leave_type', $firstLeave);
            $this->assertArrayHasKey('start_date', $firstLeave);
            $this->assertArrayHasKey('end_date', $firstLeave);
            $this->assertArrayHasKey('status', $firstLeave);
        }
    }

    #[Test]
    public function it_includes_performance_kpis_data()
    {
        $response = $this->get(route('hrm.dashboard'));

        $kpis = $response->viewData('performanceKpis');

        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('average_productivity', $kpis);
        $this->assertArrayHasKey('goal_completion_rate', $kpis);
        $this->assertArrayHasKey('employee_engagement', $kpis);
        $this->assertArrayHasKey('top_performers', $kpis);
        $this->assertArrayHasKey('improvement_areas', $kpis);

        // Test top performers structure
        $this->assertIsArray($kpis['top_performers']);
        if (!empty($kpis['top_performers'])) {
            $firstPerformer = $kpis['top_performers'][0];
            $this->assertArrayHasKey('name', $firstPerformer);
            $this->assertArrayHasKey('organizationUnit', $firstPerformer);
            $this->assertArrayHasKey('performance_score', $firstPerformer);
            $this->assertArrayHasKey('avatar', $firstPerformer);
        }
    }

    #[Test]
    public function it_includes_recent_activities_data()
    {
        $response = $this->get(route('hrm.dashboard'));

        $activities = $response->viewData('recentActivities');

        $this->assertIsArray($activities);

        if (!empty($activities)) {
            $firstActivity = $activities[0];
            $this->assertArrayHasKey('type', $firstActivity);
            $this->assertArrayHasKey('description', $firstActivity);
            $this->assertArrayHasKey('employee_name', $firstActivity);
            $this->assertArrayHasKey('timestamp', $firstActivity);
            $this->assertArrayHasKey('icon', $firstActivity);
        }
    }

    #[Test]
    public function it_includes_upcoming_events_data()
    {
        $response = $this->get(route('hrm.dashboard'));

        $events = $response->viewData('upcomingEvents');

        $this->assertIsArray($events);

        if (!empty($events)) {
            $firstEvent = $events[0];
            $this->assertArrayHasKey('title', $firstEvent);
            $this->assertArrayHasKey('date', $firstEvent);
            $this->assertArrayHasKey('type', $firstEvent);
            $this->assertArrayHasKey('attendees_count', $firstEvent);
        }
    }

    #[Test]
    public function it_includes_organizationUnit_stats_data()
    {
        $response = $this->get(route('hrm.dashboard'));

        $organizationUnitStats = $response->viewData('organizationUnitStats');

        $this->assertIsArray($organizationUnitStats);

        if (!empty($organizationUnitStats)) {
            $firstDept = $organizationUnitStats[0];
            $this->assertArrayHasKey('name', $firstDept);
            $this->assertArrayHasKey('employee_count', $firstDept);
            $this->assertArrayHasKey('attendance_rate', $firstDept);
            $this->assertArrayHasKey('vacancy_count', $firstDept);
        }
    }

    #[Test]
    public function it_includes_training_development_data()
    {
        $response = $this->get(route('hrm.dashboard'));

        $training = $response->viewData('trainingDevelopment');

        $this->assertIsArray($training);
        $this->assertArrayHasKey('ongoing_trainings', $training);
        $this->assertArrayHasKey('upcoming_sessions', $training);
        $this->assertArrayHasKey('completion_rate', $training);
        $this->assertArrayHasKey('skill_gaps', $training);

        // Test upcoming sessions structure
        $this->assertIsArray($training['upcoming_sessions']);
        if (!empty($training['upcoming_sessions'])) {
            $firstSession = $training['upcoming_sessions'][0];
            $this->assertArrayHasKey('title', $firstSession);
            $this->assertArrayHasKey('date', $firstSession);
            $this->assertArrayHasKey('participants', $firstSession);
            $this->assertArrayHasKey('trainer', $firstSession);
        }
    }

    #[Test]
    public function dashboard_view_contains_expected_hrm_elements()
    {
        $response = $this->get(route('hrm.dashboard'));

        $response->assertSee('HRM Dashboard');
        $response->assertSee('Employee Summary');
        $response->assertSee('Attendance Overview');
        $response->assertSee('Leave Management');
        $response->assertSee('Performance KPIs');
        $response->assertSee('Recent Activities');
        $response->assertSee('OrganizationUnit Stats');
        $response->assertSee('Training & Development');
        $response->assertSee('Upcoming Events');
    }

    #[Test]
    public function it_provides_realistic_hrm_data_values()
    {
        $response = $this->get(route('hrm.dashboard'));

        $summary = $response->viewData('employeeSummary');
        $attendance = $response->viewData('attendanceOverview');
        $kpis = $response->viewData('performanceKpis');

        // Test realistic value ranges
        $this->assertGreaterThan(0, $summary['total_employees']);
        $this->assertGreaterThanOrEqual(0, $summary['turnover_rate']);
        $this->assertLessThanOrEqual(100, $summary['turnover_rate']);

        $this->assertGreaterThanOrEqual(0, $attendance['attendance_rate']);
        $this->assertLessThanOrEqual(100, $attendance['attendance_rate']);

        $this->assertGreaterThanOrEqual(0, $kpis['average_productivity']);
        $this->assertLessThanOrEqual(100, $kpis['average_productivity']);
        $this->assertGreaterThanOrEqual(0, $kpis['employee_engagement']);
        $this->assertLessThanOrEqual(100, $kpis['employee_engagement']);
    }

    #[Test]
    public function recent_activities_are_sorted_by_timestamp()
    {
        $response = $this->get(route('hrm.dashboard'));

        $activities = $response->viewData('recentActivities');

        // Test that activities are sorted by timestamp (newest first)
        for ($i = 0; $i < count($activities) - 1; $i++) {
            $this->assertTrue(
                $activities[$i]['timestamp']->greaterThanOrEqualTo(
                    $activities[$i + 1]['timestamp']
                ),
                "Activities are not sorted correctly by timestamp"
            );
        }
    }

    #[Test]
    public function upcoming_events_are_sorted_by_date()
    {
        $response = $this->get(route('hrm.dashboard'));

        $events = $response->viewData('upcomingEvents');

        // Test that events are sorted by date (soonest first)
        for ($i = 0; $i < count($events) - 1; $i++) {
            $this->assertTrue(
                $events[$i]['date']->lessThanOrEqualTo(
                    $events[$i + 1]['date']
                ),
                "Events are not sorted correctly by date"
            );
        }
    }

    #[Test]
    public function organizationUnit_stats_have_consistent_structure()
    {
        $response = $this->get(route('hrm.dashboard'));

        $organizationUnitStats = $response->viewData('organizationUnitStats');

        foreach ($organizationUnitStats as $dept) {
            $this->assertArrayHasKey('name', $dept);
            $this->assertArrayHasKey('employee_count', $dept);
            $this->assertArrayHasKey('attendance_rate', $dept);
            $this->assertArrayHasKey('vacancy_count', $dept);

            // Test value validity
            $this->assertGreaterThanOrEqual(0, $dept['employee_count']);
            $this->assertGreaterThanOrEqual(0, $dept['attendance_rate']);
            $this->assertLessThanOrEqual(100, $dept['attendance_rate']);
            $this->assertGreaterThanOrEqual(0, $dept['vacancy_count']);
        }
    }

    #[Test]
    public function it_handles_users_with_hrm_permissions()
    {
        // Test that users with HRM permissions can access the dashboard
        $response = $this->get(route('hrm.dashboard'));
        $response->assertStatus(200);
    }

    #[Test]
    public function it_shows_organization_specific_data()
    {
        $response = $this->get(route('hrm.dashboard'));

        $organization = $response->viewData('organization');
        $this->assertEquals($this->organization->id, $organization->id);
    }
}

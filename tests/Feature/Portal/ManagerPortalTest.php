<?php

namespace Tests\Feature\Portal;

use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupEmployee;

class ManagerPortalTest extends TestCase
{
    use RefreshDatabase, SetupEmployee;

    // protected $manager;
    protected $teamMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEmployeeManagement();

        // $this->manager = User::factory()->create();
        $this->teamMember = User::factory()->create();

        // Setup manager-team relationship (you'll need to implement this)
        // $this->actingAs($this->manager);
        $this->actingAsManager();
    }
    #[Test]    public function manager_can_access_their_portal_dashboard()
    {
        $response = $this //->actingAs($this->manager)
            ->get(route('portal.manager.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Manager Portal');
        $response->assertSee('Team management');
    }
    #[Test]
    public function manager_can_view_team_attendance_page()
    {
        AttendanceRecord::factory()->create([
            'employee_id' => $this->teamMember->id,
            'record_date' => now(),
            'status' => 'present'
        ]);

        $response = $this->get(route('portal.manager.team-attendance'));

        $response->assertStatus(200);
        $response->assertSee('Team Attendance');
        $response->assertSee('Team Size');
    }
    #[Test]    public function manager_can_approve_leave_requests()
    {
        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'employee_id' => $this->teamMember->id,
            'status' => 'pending'
        ]);

        $response = $this //->actingAs($this->manager)
            ->post(route('portal.manager.leave.approve', $leaveRequest->id));

        $response->assertStatus(200);
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved'
        ]);
    }
    #[Test]
    public function manager_can_filter_team_attendance_by_date()
    {
        $response = $this->get(route('portal.manager.team-attendance', ['date' => '2024-01-15']));

        $response->assertStatus(200);
        $response->assertSee('January 15, 2024');
    }
    #[Test]    public function manager_can_generate_team_reports()
    {
        $response = $this //->actingAs($this->manager)
            ->get(route('portal.manager.team-report'));

        $response->assertStatus(200);
        $response->assertSee('Team Report');
    }
}

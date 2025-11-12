<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationDashboardController extends Controller
{
    public function index(): View
    {
        $organization = Organization::find(auth()->user()->operating_organization_id);

        return view('organizations.dashboard', [
            'organization' => $organization,
            'summary' => $this->getSummaryStats(),
            'recentEnrollments' => $this->getRecentEnrollments(),
            'attendanceData' => $this->getAttendanceData(),
            'unitPerformance' => $this->getUnitPerformance(),
            'quickStats' => $this->getQuickStats(),
            'upcomingEvents' => $this->getUpcomingEvents(),
        ]);
    }

    private function getSummaryStats(): array
    {
        return [
            'total_units' => 8,
            'total_employees' => 142,
            'new_enrollments' => 12,
            'attendance_rate' => 87.5,
            'active_projects' => 6,
            'completion_rate' => 72.3,
        ];
    }

    private function getRecentEnrollments(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'position' => 'Software Developer',
                'unit' => 'IT Department',
                'enrollment_date' => now()->subDays(2),
                'avatar' => 'https://ui-avatars.com/api/?name=John+Smith&background=0D8ABC&color=fff',
            ],
            [
                'id' => 2,
                'name' => 'Sarah Johnson',
                'email' => 'sarah.j@example.com',
                'position' => 'HR Manager',
                'unit' => 'Human Resources',
                'enrollment_date' => now()->subDays(1),
                'avatar' => 'https://ui-avatars.com/api/?name=Sarah+Johnson&background=00B894&color=fff',
            ],
            [
                'id' => 3,
                'name' => 'Mike Chen',
                'email' => 'mike.chen@example.com',
                'position' => 'Sales Executive',
                'unit' => 'Sales Department',
                'enrollment_date' => now()->subDays(1),
                'avatar' => 'https://ui-avatars.com/api/?name=Mike+Chen&background=FD79A8&color=fff',
            ],
            [
                'id' => 4,
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'position' => 'Marketing Specialist',
                'unit' => 'Marketing',
                'enrollment_date' => today(),
                'avatar' => 'https://ui-avatars.com/api/?name=Emily+Davis&background=6C5CE7&color=fff',
            ],
        ];
    }

    private function getAttendanceData(): array
    {
        return [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'present' => [132, 138, 140, 135, 142, 45, 32],
            'absent' => [10, 4, 2, 7, 0, 97, 110],
            'late' => [5, 3, 4, 2, 3, 1, 2],
        ];
    }

    private function getUnitPerformance(): array
    {
        return [
            [
                'name' => 'IT Department',
                'employees' => 25,
                'attendance' => 95.2,
                'productivity' => 88.7,
                'projects' => 3,
            ],
            [
                'name' => 'Sales Department',
                'employees' => 42,
                'attendance' => 82.1,
                'productivity' => 92.3,
                'projects' => 2,
            ],
            [
                'name' => 'Human Resources',
                'employees' => 12,
                'attendance' => 91.8,
                'productivity' => 85.4,
                'projects' => 1,
            ],
            [
                'name' => 'Marketing',
                'employees' => 18,
                'attendance' => 88.5,
                'productivity' => 90.1,
                'projects' => 2,
            ],
            [
                'name' => 'Operations',
                'employees' => 35,
                'attendance' => 94.2,
                'productivity' => 87.9,
                'projects' => 4,
            ],
        ];
    }

    private function getQuickStats(): array
    {
        return [
            'pending_requests' => 8,
            'upcoming_leaves' => 5,
            'training_sessions' => 3,
            'active_recruitments' => 4,
        ];
    }

    private function getUpcomingEvents(): array
    {
        return [
            [
                'title' => 'Monthly All-Hands Meeting',
                'date' => now()->addDays(2),
                'time' => '10:00 AM',
                'type' => 'meeting',
                'attendees' => 120,
            ],
            [
                'title' => 'Team Building Workshop',
                'date' => now()->addDays(5),
                'time' => '9:00 AM',
                'type' => 'training',
                'attendees' => 45,
            ],
            [
                'title' => 'Project Alpha Review',
                'date' => now()->addDays(3),
                'time' => '2:00 PM',
                'type' => 'review',
                'attendees' => 15,
            ],
            [
                'title' => 'New Hire Orientation',
                'date' => now()->addDays(7),
                'time' => '9:30 AM',
                'type' => 'orientation',
                'attendees' => 12,
            ],
        ];
    }
}

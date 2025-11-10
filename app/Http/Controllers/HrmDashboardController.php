<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HrmDashboardController extends Controller
{
    public function index(): View
    {
        $organization = auth()->user()->organizations()->first();

        return view('hrm.dashboard', [
            'organization' => $organization,
            'employeeSummary' => $this->getEmployeeSummary(),
            'attendanceOverview' => $this->getAttendanceOverview(),
            'leaveManagement' => $this->getLeaveManagement(),
            'performanceKpis' => $this->getPerformanceKpis(),
            'recentActivities' => $this->getRecentActivities(),
            'upcomingEvents' => $this->getUpcomingEvents(),
            'organizationUnitStats' => $this->getorganizationUnitStats(),
            'trainingDevelopment' => $this->getTrainingDevelopment(),
        ]);
    }

    private function getEmployeeSummary(): array
    {
        return [
            'total_employees' => 142,
            'active_employees' => 138,
            'new_hires' => 8,
            'turnover_rate' => 2.8,
            'organizationUnit_distribution' => [
                'IT' => 25,
                'Sales' => 42,
                'HR' => 12,
                'Marketing' => 18,
                'Operations' => 35,
                'Finance' => 10,
            ]
        ];
    }

    private function getAttendanceOverview(): array
    {
        return [
            'present_today' => 132,
            'absent_today' => 6,
            'late_today' => 4,
            'attendance_rate' => 95.7,
            'weekly_trend' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'present' => [130, 135, 132, 140, 132],
                'absent' => [8, 5, 8, 2, 6],
                'late' => [4, 2, 5, 3, 4],
            ]
        ];
    }

    private function getLeaveManagement(): array
    {
        return [
            'pending_requests' => 5,
            'approved_this_month' => 12,
            'leave_balance_summary' => [
                'annual' => 342,
                'sick' => 156,
                'personal' => 89,
            ],
            'upcoming_leaves' => [
                [
                    'employee_name' => 'John Smith',
                    'leave_type' => 'Annual',
                    'start_date' => now()->addDays(3),
                    'end_date' => now()->addDays(10),
                    'status' => 'approved',
                ],
                [
                    'employee_name' => 'Sarah Johnson',
                    'leave_type' => 'Sick',
                    'start_date' => now()->addDays(1),
                    'end_date' => now()->addDays(2),
                    'status' => 'pending',
                ],
            ]
        ];
    }

    private function getPerformanceKpis(): array
    {
        return [
            'average_productivity' => 87.5,
            'goal_completion_rate' => 82.3,
            'employee_engagement' => 78.9,
            'top_performers' => [
                [
                    'name' => 'Mike Chen',
                    'organizationUnit' => 'Sales',
                    'performance_score' => 96,
                    'avatar' => 'https://ui-avatars.com/api/?name=Mike+Chen&background=00B894&color=fff',
                ],
                [
                    'name' => 'Emily Davis',
                    'organizationUnit' => 'Marketing',
                    'performance_score' => 94,
                    'avatar' => 'https://ui-avatars.com/api/?name=Emily+Davis&background=6C5CE7&color=fff',
                ],
            ],
            'improvement_areas' => ['Customer Service', 'Project Management', 'Technical Skills']
        ];
    }

    private function getRecentActivities(): array
    {
        return [
            [
                'type' => 'new_hire',
                'description' => 'New employee onboarded',
                'employee_name' => 'Alex Thompson',
                'timestamp' => now()->subHours(2),
                'icon' => '👤',
            ],
            [
                'type' => 'leave_approved',
                'description' => 'Leave request approved',
                'employee_name' => 'Maria Garcia',
                'timestamp' => now()->subHours(5),
                'icon' => '🏖️',
            ],
            [
                'type' => 'training_completed',
                'description' => 'Completed safety training',
                'employee_name' => 'David Wilson',
                'timestamp' => now()->subDays(1),
                'icon' => '🎓',
            ],
        ];
    }

    private function getUpcomingEvents(): array
    {
        return [
            [
                'title' => 'Monthly Performance Review',
                'date' => now()->addDays(2),
                'type' => 'review',
                'attendees_count' => 45,
            ],
            [
                'title' => 'Team Building Workshop',
                'date' => now()->addDays(5),
                'type' => 'training',
                'attendees_count' => 60,
            ],
            [
                'title' => 'HR Policy Update Meeting',
                'date' => now()->addDays(7),
                'type' => 'meeting',
                'attendees_count' => 12,
            ],
        ];
    }

    private function getorganizationUnitStats(): array
    {
        return [
            [
                'name' => 'IT organizationUnit',
                'employee_count' => 25,
                'attendance_rate' => 97.2,
                'vacancy_count' => 2,
            ],
            [
                'name' => 'Sales',
                'employee_count' => 42,
                'attendance_rate' => 92.8,
                'vacancy_count' => 3,
            ],
            [
                'name' => 'Human Resources',
                'employee_count' => 12,
                'attendance_rate' => 96.5,
                'vacancy_count' => 1,
            ],
        ];
    }

    private function getTrainingDevelopment(): array
    {
        return [
            'ongoing_trainings' => 4,
            'upcoming_sessions' => [
                [
                    'title' => 'Leadership Skills Workshop',
                    'date' => now()->addDays(3),
                    'participants' => 15,
                    'trainer' => 'Dr. Sarah Johnson',
                ],
                [
                    'title' => 'Advanced Excel Training',
                    'date' => now()->addDays(7),
                    'participants' => 22,
                    'trainer' => 'Mike Chen',
                ],
            ],
            'completion_rate' => 78.5,
            'skill_gaps' => ['Data Analysis', 'Project Management', 'Communication'],
        ];
    }
}

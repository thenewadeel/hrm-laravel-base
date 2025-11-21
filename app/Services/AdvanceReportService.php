<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\SalaryAdvance;
use Carbon\Carbon;

class AdvanceReportService
{
    /**
     * Generate employee-wise advance statements
     */
    public function generateEmployeeStatement(Organization $organization, ?int $employeeId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = SalaryAdvance::with(['employee', 'approver'])
            ->where('organization_id', $organization->id);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($startDate) {
            $query->where('request_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('request_date', '<=', $endDate);
        }

        $advances = $query->orderBy('request_date', 'desc')->get();

        $summary = [
            'total_advances' => $advances->count(),
            'total_amount' => $advances->sum('amount'),
            'total_balance' => $advances->sum('balance_amount'),
            'total_repaid' => $advances->sum('amount') - $advances->sum('balance_amount'),
            'pending_count' => $advances->where('status', 'pending')->count(),
            'active_count' => $advances->where('status', 'active')->count(),
            'completed_count' => $advances->where('status', 'completed')->count(),
        ];

        return [
            'advances' => $advances,
            'summary' => $summary,
            'employee' => $employeeId ? Employee::find($employeeId) : null,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];
    }

    /**
     * Generate advance aging analysis
     */
    public function generateAgingAnalysis(Organization $organization): array
    {
        $advances = SalaryAdvance::with('employee')
            ->where('organization_id', $organization->id)
            ->where('status', 'active')
            ->get();

        $agingBuckets = [
            '0-30' => ['advances' => collect(), 'total' => 0],
            '31-60' => ['advances' => collect(), 'total' => 0],
            '61-90' => ['advances' => collect(), 'total' => 0],
            '91-180' => ['advances' => collect(), 'total' => 0],
            '180+' => ['advances' => collect(), 'total' => 0],
        ];

        foreach ($advances as $advance) {
            $daysSinceFirstDeduction = Carbon::parse($advance->first_deduction_month)->diffInDays(now());

            if ($daysSinceFirstDeduction <= 30) {
                $bucket = '0-30';
            } elseif ($daysSinceFirstDeduction <= 60) {
                $bucket = '31-60';
            } elseif ($daysSinceFirstDeduction <= 90) {
                $bucket = '61-90';
            } elseif ($daysSinceFirstDeduction <= 180) {
                $bucket = '91-180';
            } else {
                $bucket = '180+';
            }

            $agingBuckets[$bucket]['advances']->push($advance);
            $agingBuckets[$bucket]['total'] += $advance->balance_amount;
        }

        return [
            'aging_buckets' => $agingBuckets,
            'total_outstanding' => $advances->sum('balance_amount'),
            'total_active_advances' => $advances->count(),
        ];
    }

    /**
     * Generate monthly advance summaries
     */
    public function generateMonthlySummary(Organization $organization, int $months = 12): array
    {
        $monthlyData = collect();

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $advancesRequested = SalaryAdvance::where('organization_id', $organization->id)
                ->whereBetween('request_date', [$monthStart, $monthEnd])
                ->get();

            $advancesApproved = SalaryAdvance::where('organization_id', $organization->id)
                ->whereBetween('approval_date', [$monthStart, $monthEnd])
                ->get();

            $monthlyData->push([
                'month' => $month->format('Y-m'),
                'month_name' => $month->format('F Y'),
                'advances_requested' => $advancesRequested->count(),
                'advances_approved' => $advancesApproved->count(),
                'amount_requested' => $advancesRequested->sum('amount'),
                'amount_approved' => $advancesApproved->sum('amount'),
                'pending_approval' => $advancesRequested->where('status', 'pending')->count(),
            ]);
        }

        return [
            'monthly_data' => $monthlyData,
            'summary' => [
                'total_requested' => $monthlyData->sum('advances_requested'),
                'total_approved' => $monthlyData->sum('advances_approved'),
                'total_amount_requested' => $monthlyData->sum('amount_requested'),
                'total_amount_approved' => $monthlyData->sum('amount_approved'),
            ],
        ];
    }

    /**
     * Generate department-wise advance reports
     */
    public function generateDepartmentReport(Organization $organization): array
    {
        $departments = Employee::where('organization_id', $organization->id)
            ->whereNotNull('department')
            ->select('department')
            ->distinct()
            ->pluck('department');

        $departmentData = collect();

        foreach ($departments as $department) {
            $departmentEmployees = Employee::where('organization_id', $organization->id)
                ->where('department', $department)
                ->pluck('id');

            $advances = SalaryAdvance::with('employee')
                ->whereIn('employee_id', $departmentEmployees)
                ->get();

            $departmentData->push([
                'department' => $department,
                'employee_count' => $departmentEmployees->count(),
                'total_advances' => $advances->count(),
                'total_amount' => $advances->sum('amount'),
                'total_balance' => $advances->sum('balance_amount'),
                'active_advances' => $advances->where('status', 'active')->count(),
                'pending_advances' => $advances->where('status', 'pending')->count(),
                'completed_advances' => $advances->where('status', 'completed')->count(),
                'avg_advance_amount' => $advances->count() > 0 ? $advances->sum('amount') / $advances->count() : 0,
            ]);
        }

        return [
            'departments' => $departmentData,
            'summary' => [
                'total_departments' => $departments->count(),
                'total_advances' => $departmentData->sum('total_advances'),
                'total_amount' => $departmentData->sum('total_amount'),
                'total_balance' => $departmentData->sum('total_balance'),
            ],
        ];
    }

    /**
     * Generate advance vs salary analysis
     */
    public function generateAdvanceVsSalaryAnalysis(Organization $organization, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subMonths(6);
        $endDate = $endDate ?? Carbon::now();

        $employees = Employee::with(['salaryAdvances' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('request_date', [$startDate, $endDate]);
        }])
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->get();

        $analysis = collect();

        foreach ($employees as $employee) {
            $monthlySalary = $employee->basic_salary ?? 0;
            $totalAdvances = $employee->salaryAdvances->sum('amount');
            $totalBalance = $employee->salaryAdvances->sum('balance_amount');
            $advanceCount = $employee->salaryAdvances->count();

            $analysis->push([
                'employee' => $employee,
                'monthly_salary' => $monthlySalary,
                'total_advances' => $totalAdvances,
                'total_balance' => $totalBalance,
                'advance_count' => $advanceCount,
                'advance_to_salary_ratio' => $monthlySalary > 0 ? ($totalAdvances / $monthlySalary) : 0,
                'balance_to_salary_ratio' => $monthlySalary > 0 ? ($totalBalance / $monthlySalary) : 0,
                'avg_advance_amount' => $advanceCount > 0 ? $totalAdvances / $advanceCount : 0,
            ]);
        }

        return [
            'employees' => $analysis,
            'summary' => [
                'total_employees' => $employees->count(),
                'employees_with_advances' => $analysis->where('advance_count', '>', 0)->count(),
                'total_monthly_salary' => $analysis->sum('monthly_salary'),
                'total_advances' => $analysis->sum('total_advances'),
                'total_balance' => $analysis->sum('total_balance'),
                'avg_advance_to_salary_ratio' => $analysis->avg('advance_to_salary_ratio'),
            ],
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];
    }

    /**
     * Generate outstanding advance tracking
     */
    public function generateOutstandingAdvances(Organization $organization): array
    {
        $outstandingAdvances = SalaryAdvance::with(['employee', 'approver'])
            ->where('organization_id', $organization->id)
            ->where('status', 'active')
            ->where('balance_amount', '>', 0)
            ->orderBy('balance_amount', 'desc')
            ->get();

        $riskCategories = [
            'low' => collect(), // < 1 month salary
            'medium' => collect(), // 1-2 months salary
            'high' => collect(), // 2-3 months salary
            'critical' => collect(), // > 3 months salary
        ];

        foreach ($outstandingAdvances as $advance) {
            $monthlySalary = $advance->employee->basic_salary ?? 0;

            if ($monthlySalary > 0) {
                $ratio = $advance->balance_amount / $monthlySalary;

                if ($ratio < 1) {
                    $riskCategories['low']->push($advance);
                } elseif ($ratio < 2) {
                    $riskCategories['medium']->push($advance);
                } elseif ($ratio < 3) {
                    $riskCategories['high']->push($advance);
                } else {
                    $riskCategories['critical']->push($advance);
                }
            } else {
                $riskCategories['medium']->push($advance);
            }
        }

        return [
            'outstanding_advances' => $outstandingAdvances,
            'risk_categories' => $riskCategories,
            'summary' => [
                'total_outstanding' => $outstandingAdvances->sum('balance_amount'),
                'total_count' => $outstandingAdvances->count(),
                'low_risk_count' => $riskCategories['low']->count(),
                'medium_risk_count' => $riskCategories['medium']->count(),
                'high_risk_count' => $riskCategories['high']->count(),
                'critical_risk_count' => $riskCategories['critical']->count(),
            ],
        ];
    }

    /**
     * Get comprehensive advance analytics
     */
    public function getAdvanceAnalytics(Organization $organization): array
    {
        $allAdvances = SalaryAdvance::where('organization_id', $organization->id)->get();
        $activeAdvances = $allAdvances->where('status', 'active');
        $completedAdvances = $allAdvances->where('status', 'completed');

        return [
            'overview' => [
                'total_advances' => $allAdvances->count(),
                'total_amount_disbursed' => $allAdvances->sum('amount'),
                'total_amount_repaid' => $completedAdvances->sum('amount'),
                'total_outstanding' => $activeAdvances->sum('balance_amount'),
                'active_advances' => $activeAdvances->count(),
                'completed_advances' => $completedAdvances->count(),
                'pending_advances' => $allAdvances->where('status', 'pending')->count(),
            ],
            'performance_metrics' => [
                'avg_advance_amount' => $allAdvances->count() > 0 ? $allAdvances->sum('amount') / $allAdvances->count() : 0,
                'avg_repayment_period' => $completedAdvances->count() > 0 ?
                    $completedAdvances->avg('repayment_months') : 0,
                'completion_rate' => $allAdvances->count() > 0 ?
                    ($completedAdvances->count() / $allAdvances->count()) * 100 : 0,
                'avg_monthly_deduction' => $activeAdvances->count() > 0 ?
                    $activeAdvances->avg('monthly_deduction') : 0,
            ],
            'trends' => $this->generateMonthlySummary($organization, 6),
        ];
    }
}

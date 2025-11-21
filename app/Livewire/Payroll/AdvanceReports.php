<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Services\AdvancePdfService;
use App\Services\AdvanceReportService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdvanceReports extends Component
{
    use WithPagination;

    public $reportType = 'overview';

    public $employeeId = null;

    public $department = null;

    public $startDate = null;

    public $endDate = null;

    public $status = null;

    public $search = '';

    protected $queryString = [
        'reportType',
        'employeeId',
        'department',
        'startDate',
        'endDate',
        'status',
        'search',
    ];

    protected $listeners = [
        'refreshReports' => '$refresh',
    ];

    public function mount()
    {
        $this->startDate = now()->subMonths(6)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.payroll.advance-reports');
    }

    #[Computed]
    public function reportData()
    {
        $organization = auth()->user()?->currentOrganization;
        if (! $organization) {
            return [
                'overview' => [
                    'total_advances' => 0,
                    'total_amount_disbursed' => 0,
                    'total_outstanding' => 0,
                    'active_advances' => 0,
                    'completed_advances' => 0,
                    'pending_advances' => 0,
                ],
                'performance_metrics' => [
                    'avg_advance_amount' => 0,
                    'avg_repayment_period' => 0,
                    'completion_rate' => 0,
                    'avg_monthly_deduction' => 0,
                ],
                'trends' => [
                    'monthly_data' => collect(),
                ],
            ];
        }

        $reportService = app(AdvanceReportService::class);

        return match ($this->reportType) {
            'employee-statement' => $reportService->generateEmployeeStatement(
                $organization,
                $this->employeeId,
                $this->startDate ? Carbon::parse($this->startDate) : null,
                $this->endDate ? Carbon::parse($this->endDate) : null
            ),
            'aging-analysis' => $reportService->generateAgingAnalysis($organization),
            'monthly-summary' => $reportService->generateMonthlySummary($organization),
            'department-report' => $reportService->generateDepartmentReport($organization),
            'advance-vs-salary' => $reportService->generateAdvanceVsSalaryAnalysis(
                $organization,
                $this->startDate ? Carbon::parse($this->startDate) : null,
                $this->endDate ? Carbon::parse($this->endDate) : null
            ),
            'outstanding' => $reportService->generateOutstandingAdvances($organization),
            'analytics' => $reportService->getAdvanceAnalytics($organization),
            default => $reportService->getAdvanceAnalytics($organization),
        };
    }

    #[Computed]
    public function employees()
    {
        return Employee::where('organization_id', auth()->user()->currentOrganization->id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
    }

    #[Computed]
    public function departments()
    {
        return Employee::where('organization_id', auth()->user()->currentOrganization->id)
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
    }

    public function exportPdf()
    {
        $organization = auth()->user()->currentOrganization;
        $pdfService = app(AdvancePdfService::class);

        try {
            $filename = match ($this->reportType) {
                'employee-statement' => 'advance-statement.pdf',
                'aging-analysis' => 'advance-aging-analysis.pdf',
                'monthly-summary' => 'advance-monthly-summary.pdf',
                'department-report' => 'advance-department-report.pdf',
                'advance-vs-salary' => 'advance-vs-salary-analysis.pdf',
                'outstanding' => 'outstanding-advances.pdf',
                'analytics' => 'advance-analytics.pdf',
                default => 'advance-report.pdf',
            };

            $content = match ($this->reportType) {
                'employee-statement' => $pdfService->generateEmployeeStatementPdf(
                    $organization,
                    $this->employeeId,
                    $this->startDate ? Carbon::parse($this->startDate) : null,
                    $this->endDate ? Carbon::parse($this->endDate) : null
                ),
                'aging-analysis' => $pdfService->generateAgingAnalysisPdf($organization),
                'monthly-summary' => $pdfService->generateMonthlySummaryPdf($organization),
                'department-report' => $pdfService->generateDepartmentReportPdf($organization),
                'advance-vs-salary' => $pdfService->generateAdvanceVsSalaryPdf(
                    $organization,
                    $this->startDate ? Carbon::parse($this->startDate) : null,
                    $this->endDate ? Carbon::parse($this->endDate) : null
                ),
                'outstanding' => $pdfService->generateOutstandingAdvancesPdf($organization),
                'analytics' => $pdfService->generateAnalyticsPdf($organization),
                default => $pdfService->generateAnalyticsPdf($organization),
            };

            return response()->streamDownload(
                fn () => print ($content),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to generate PDF: '.$e->getMessage());

            return null;
        }
    }

    public function resetFilters()
    {
        $this->reset(['employeeId', 'department', 'startDate', 'endDate', 'status', 'search']);
        $this->startDate = now()->subMonths(6)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->reportType = 'overview';
    }

    public function getReportTypesProperty()
    {
        return [
            'overview' => 'Analytics Overview',
            'employee-statement' => 'Employee Statement',
            'aging-analysis' => 'Aging Analysis',
            'monthly-summary' => 'Monthly Summary',
            'department-report' => 'Department Report',
            'advance-vs-salary' => 'Advance vs Salary',
            'outstanding' => 'Outstanding Advances',
        ];
    }
}

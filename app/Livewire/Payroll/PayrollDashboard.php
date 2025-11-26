<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Models\EmployeeIncrement;
use App\Models\EmployeeLoan;
use App\Models\SalaryAdvance;
use App\Services\PayrollCalculationService;
use Livewire\Component;

class PayrollDashboard extends Component
{
    public $selected_period;

    public $payroll_summary;

    public $pending_increments;

    public $pending_loans;

    public $pending_advances;

    protected $payrollService;

    public function boot()
    {
        $this->payrollService = new PayrollCalculationService;
    }

    public function mount()
    {
        $this->selected_period = now()->format('Y-m');
        $this->loadDashboardData();
    }

    public function render()
    {
        return view('livewire.payroll.payroll-dashboard', [
            'summary' => $this->payroll_summary,
            'pendingIncrements' => $this->pending_increments,
            'pendingLoans' => $this->pending_loans,
            'pendingAdvances' => $this->pending_advances,
        ]);
    }

    public function updatedSelectedPeriod()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $organizationId = auth()->user()->currentOrganization->id;

        // Load payroll summary
        $this->payroll_summary = $this->payrollService->generatePayrollSummary($organizationId, $this->selected_period);

        // Load pending items
        $this->pending_increments = EmployeeIncrement::where('organization_id', $organizationId)
            ->pending()
            ->with('employee')
            ->count();

        $this->pending_loans = EmployeeLoan::where('organization_id', $organizationId)
            ->pending()
            ->with('employee')
            ->count();

        $this->pending_advances = SalaryAdvance::where('organization_id', $organizationId)
            ->pending()
            ->with('employee')
            ->count();
    }

    public function getQuickStats()
    {
        $organizationId = auth()->user()->currentOrganization->id;

        return [
            'total_employees' => Employee::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->count(),
            'active_loans' => EmployeeLoan::where('organization_id', $organizationId)
                ->active()
                ->count(),
            'active_advances' => SalaryAdvance::where('organization_id', $organizationId)
                ->active()
                ->count(),
            'pending_approvals' => $this->pending_increments + $this->pending_loans + $this->pending_advances,
        ];
    }

    public function processPayroll()
    {
        $organizationId = auth()->user()->currentOrganization->id;
        $employees = Employee::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $results = $this->payrollService->processBatchPayroll($employees, $this->selected_period);

        $this->dispatch('payroll-processed', count($results));
    }
}

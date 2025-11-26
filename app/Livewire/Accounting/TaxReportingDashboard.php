<?php

namespace App\Livewire\Accounting;

use App\Services\TaxComplianceService;
use App\Services\TaxReportingService;
use Livewire\Component;

class TaxReportingDashboard extends Component
{
    public $reportType = 'summary';

    public $startDate;

    public $endDate;

    public $taxType = '';

    public $taxReport;

    public $liabilityReport;

    public $filingSchedule;

    public $complianceDashboard;

    protected $taxReportingService;

    protected $taxComplianceService;

    public function boot(TaxReportingService $taxReportingService, TaxComplianceService $taxComplianceService): void
    {
        $this->taxReportingService = $taxReportingService;
        $this->taxComplianceService = $taxComplianceService;
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->loadReports();
    }

    public function loadReports(): void
    {
        $organizationId = auth()->user()->current_organization_id;

        $this->taxReport = $this->taxReportingService->generateTaxReport(
            $organizationId,
            $this->startDate,
            $this->endDate,
            $this->taxType ?: null
        );

        $this->liabilityReport = $this->taxReportingService->generateTaxLiabilityReport(
            $organizationId,
            $this->endDate
        );

        $this->filingSchedule = $this->taxReportingService->generateFilingScheduleReport(
            $organizationId,
            12
        );

        $this->complianceDashboard = $this->taxComplianceService->getComplianceDashboard(
            $organizationId
        );
    }

    public function updatedReportType(): void
    {
        // Refresh data when report type changes
        $this->loadReports();
    }

    public function updatedStartDate(): void
    {
        $this->loadReports();
    }

    public function updatedEndDate(): void
    {
        $this->loadReports();
    }

    public function updatedTaxType(): void
    {
        $this->loadReports();
    }

    public function exportReport(): void
    {
        // Implementation for exporting reports
        $this->dispatch('show-message', 'Report export feature coming soon.', 'info');
    }

    public function render()
    {
        return view('livewire.accounting.tax-reporting-dashboard', [
            'taxTypes' => [
                '' => 'All Tax Types',
                'sales' => 'Sales Tax',
                'purchase' => 'Purchase Tax',
                'withholding' => 'Withholding Tax',
                'income' => 'Income Tax',
                'vat' => 'VAT/GST',
                'service' => 'Service Tax',
            ],
        ]);
    }
}

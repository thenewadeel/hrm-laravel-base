<?php

namespace App\Livewire\Accounting;

use App\Services\AccountingReportService;
use Livewire\Component;

class Dashboard extends Component
{
    protected AccountingReportService $reportService;

    // Placeholder data for the dashboard.
    // In a real application, you would fetch this from your database
    // using your accounting services.
    public $summary;

    public function mount(AccountingReportService $reportService)
    {
        $this->reportService = $reportService;
        // This will now populate the public properties on initial load
        $this->generateSummary();
    }

    public function generateSummary()
    {
        $this->summary =  $this->reportService->getDashboardSummary();
    }
    public function render()
    {
        return view('livewire.accounting.dashboard');
    }
}

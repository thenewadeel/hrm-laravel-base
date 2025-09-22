<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Services\AccountingReportService;
use Carbon\Carbon;

class Reports extends Component
{
    public string $activeTab = 'trial-balance';

    // Initialize public properties with a default, empty data structure
    public array $trialBalanceReport = [];
    public array $balanceSheetReport = [];
    public array $incomeStatementReport = [];

    protected AccountingReportService $reportService;

    // Use the mount method to initialize the data.
    public function mount(AccountingReportService $reportService)
    {
        $this->reportService = $reportService;
        // This will now populate the public properties on initial load
        $this->generateReports();
    }

    // A single method to generate all reports
    public function generateReports()
    {
        $this->trialBalanceReport = $this->reportService->generateTrialBalance();
        $this->balanceSheetReport = $this->reportService->generateBalanceSheet(Carbon::now());
        $this->incomeStatementReport = $this->reportService->generateIncomeStatement(Carbon::now()->startOfYear(), Carbon::now());
    }

    public function render()
    {
        return view('livewire.accounting.reports');
    }
}

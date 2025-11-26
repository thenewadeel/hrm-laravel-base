<?php

namespace App\Http\Controllers;

use App\Services\TaxComplianceService;
use App\Services\TaxReportingService;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function __construct(
        private TaxReportingService $taxReportingService,
        private TaxComplianceService $taxComplianceService
    ) {}

    public function downloadTaxReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'tax_type' => 'nullable|string',
        ]);

        $organizationId = auth()->user()->current_organization_id;
        $report = $this->taxReportingService->generateTaxReport(
            $organizationId,
            $request->start_date,
            $request->end_date,
            $request->tax_type
        );

        // Generate PDF or Excel download
        // Implementation would depend on your preferred export method
        return response()->json($report);
    }

    public function downloadTaxLiability(Request $request)
    {
        $request->validate([
            'as_of_date' => 'required|date',
        ]);

        $organizationId = auth()->user()->current_organization_id;
        $report = $this->taxReportingService->generateTaxLiabilityReport(
            $organizationId,
            $request->as_of_date
        );

        return response()->json($report);
    }

    public function downloadFilingSchedule(Request $request)
    {
        $request->validate([
            'months_ahead' => 'nullable|integer|min:1|max:24',
        ]);

        $organizationId = auth()->user()->current_organization_id;
        $monthsAhead = $request->months_ahead ?? 12;
        $schedule = $this->taxReportingService->generateFilingScheduleReport(
            $organizationId,
            $monthsAhead
        );

        return response()->json($schedule);
    }
}

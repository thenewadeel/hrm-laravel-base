<?php

namespace App\Services;

use Dompdf\Dompdf;

class AccountingPdfService
{
    private AccountingReportService $reportService;

    private OutstandingStatementsService $outstandingService;

    private PdfThemeManager $themeManager;

    public function __construct(
        AccountingReportService $reportService,
        OutstandingStatementsService $outstandingService,
        PdfThemeManager $themeManager
    ) {
        $this->reportService = $reportService;
        $this->outstandingService = $outstandingService;
        $this->themeManager = $themeManager;
    }

    /**
     * Generate Trial Balance PDF
     */
    public function generateTrialBalancePdf(?\DateTimeInterface $asOfDate = null): string
    {
        $asOfDate = $asOfDate ?? now();
        $data = $this->reportService->generateTrialBalance($asOfDate);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getTrialBalanceHtml($data, $asOfDate);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate Income Statement PDF
     */
    public function generateIncomeStatementPdf(\DateTimeInterface $startDate, \DateTimeInterface $endDate): string
    {
        $data = $this->reportService->generateIncomeStatement($startDate, $endDate);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getIncomeStatementHtml($data, $startDate, $endDate);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate Balance Sheet PDF
     */
    public function generateBalanceSheetPdf(\DateTimeInterface $asOfDate): string
    {
        $data = $this->reportService->generateBalanceSheet($asOfDate);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getBalanceSheetHtml($data, $asOfDate);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate HTML for Trial Balance
     */
    private function getTrialBalanceHtml(array $data, \DateTimeInterface $asOfDate): string
    {
        $theme = $this->themeManager->getTheme();
        $brand = $this->themeManager->getBrand();

        return view('accounting.pdf.trial-balance', [
            'data' => $data,
            'asOfDate' => $asOfDate,
            'theme' => $theme,
            'brand' => $brand,
        ])->render();
    }

    /**
     * Generate HTML for Income Statement
     */
    private function getIncomeStatementHtml(array $data, \DateTimeInterface $startDate, \DateTimeInterface $endDate): string
    {
        $theme = $this->themeManager->getTheme();
        $brand = $this->themeManager->getBrand();

        return view('accounting.pdf.income-statement', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'theme' => $theme,
            'brand' => $brand,
        ])->render();
    }

    /**
     * Generate HTML for Balance Sheet
     */
    private function getBalanceSheetHtml(array $data, \DateTimeInterface $asOfDate): string
    {
        $theme = $this->themeManager->getTheme();
        $brand = $this->themeManager->getBrand();

        return view('accounting.pdf.balance-sheet', [
            'data' => $data,
            'asOfDate' => $asOfDate,
            'theme' => $theme,
            'brand' => $brand,
        ])->render();
    }

    /**
     * Generate HTML for Receivables Outstanding
     */
    private function getReceivablesOutstandingHtml(array $data): string
    {
        $theme = $this->themeManager->getTheme();
        $brand = $this->themeManager->getBrand();

        return view('accounting.pdf.receivables-outstanding', [
            'data' => $data,
            'theme' => $theme,
            'brand' => $brand,
        ])->render();
    }

    /**
     * Generate HTML for Payables Outstanding
     */
    private function getPayablesOutstandingHtml(array $data): string
    {
        $theme = $this->themeManager->getTheme();
        $brand = $this->themeManager->getBrand();

        return view('accounting.pdf.payables-outstanding', [
            'data' => $data,
            'theme' => $theme,
            'brand' => $brand,
        ])->render();
    }

    /**
     * Download Trial Balance PDF
     */
    public function downloadTrialBalance(?\DateTimeInterface $asOfDate = null)
    {
        $asOfDate = $asOfDate ?? now();
        $pdfContent = $this->generateTrialBalancePdf($asOfDate);
        $filename = 'trial-balance-' . $asOfDate->format('Y-m-d') . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    /**
     * Download Income Statement PDF
     */
    public function downloadIncomeStatement(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        $pdfContent = $this->generateIncomeStatementPdf($startDate, $endDate);
        $filename = 'income-statement-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    /**
     * Generate Receivables Outstanding PDF
     */
    public function generateReceivablesOutstandingPdf(
        ?int $customerId = null,
        ?\DateTimeInterface $asOfDate = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): string {
        $data = $this->outstandingService->generateReceivablesStatement(
            customerId: $customerId,
            asOfDate: $asOfDate,
            startDate: $startDate,
            endDate: $endDate
        );

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getReceivablesOutstandingHtml($data);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate Payables Outstanding PDF
     */
    public function generatePayablesOutstandingPdf(
        ?int $vendorId = null,
        ?\DateTimeInterface $asOfDate = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): string {
        $data = $this->outstandingService->generatePayablesStatement(
            vendorId: $vendorId,
            asOfDate: $asOfDate,
            startDate: $startDate,
            endDate: $endDate
        );

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getPayablesOutstandingHtml($data);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Download Balance Sheet PDF
     */
    public function downloadBalanceSheet(\DateTimeInterface $asOfDate)
    {
        $pdfContent = $this->generateBalanceSheetPdf($asOfDate);
        $filename = 'balance-sheet-' . $asOfDate->format('Y-m-d') . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    /**
     * Download Receivables Outstanding PDF
     */
    public function downloadReceivablesOutstanding(
        ?int $customerId = null,
        ?\DateTimeInterface $asOfDate = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ) {
        $pdfContent = $this->generateReceivablesOutstandingPdf(
            customerId: $customerId,
            asOfDate: $asOfDate,
            startDate: $startDate,
            endDate: $endDate
        );

        $asOfDate = $asOfDate ?? now();
        $filename = 'receivables-outstanding-' . $asOfDate->format('Y-m-d') . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    /**
     * Download Payables Outstanding PDF
     */
    public function downloadPayablesOutstanding(
        ?int $vendorId = null,
        ?\DateTimeInterface $asOfDate = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ) {
        $pdfContent = $this->generatePayablesOutstandingPdf(
            vendorId: $vendorId,
            asOfDate: $asOfDate,
            startDate: $startDate,
            endDate: $endDate
        );

        $asOfDate = $asOfDate ?? now();
        $filename = 'payables-outstanding-' . $asOfDate->format('Y-m-d') . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    /**
     * Generate Asset Register PDF
     */
    public function generateAssetRegister($assets): string
    {
        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getAssetRegisterHtml($assets);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate Depreciation Schedule PDF
     */
    public function generateDepreciationSchedule($assets): string
    {
        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getDepreciationScheduleHtml($assets);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Download Asset Register PDF
     */
    public function downloadAssetRegister($assets)
    {
        $pdfContent = $this->generateAssetRegister($assets);
        $filename = 'asset-register-' . now()->format('Y-m-d') . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    /**
     * Download Depreciation Schedule PDF
     */
    public function downloadDepreciationSchedule($assets)
    {
        $pdfContent = $this->generateDepreciationSchedule($assets);
        $filename = 'depreciation-schedule-' . now()->format('Y-m-d') . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    /**
     * Get Asset Register HTML
     */
    private function getAssetRegisterHtml($assets): string
    {
        $totalCost = $assets->sum('purchase_cost');
        $totalAccumulatedDepreciation = $assets->sum('accumulated_depreciation');
        $totalBookValue = $assets->sum('current_book_value');

        return view('accounting.pdf.asset-register', compact(
            'assets',
            'totalCost',
            'totalAccumulatedDepreciation',
            'totalBookValue'
        ))->render();
    }

    /**
     * Get Depreciation Schedule HTML
     */
    private function getDepreciationScheduleHtml($assets): string
    {
        $scheduleData = [];

        foreach ($assets as $asset) {
            $annualDepreciation = $asset->calculateAnnualDepreciation();
            $remainingLife = $asset->useful_life_years - $asset->getCurrentDepreciationYear() + 1;

            $scheduleData[] = [
                'asset' => $asset,
                'annual_depreciation' => $annualDepreciation,
                'remaining_life' => max(0, $remainingLife),
                'projected_depreciation' => $this->calculateProjectedDepreciation($asset),
            ];
        }

        return view('accounting.pdf.depreciation-schedule', compact('scheduleData'))->render();
    }

    /**
     * Calculate projected depreciation for remaining years
     */
    private function calculateProjectedDepreciation($asset): array
    {
        $projected = [];
        $currentBookValue = $asset->current_book_value;
        $currentYear = $asset->getCurrentDepreciationYear();

        for ($year = $currentYear + 1; $year <= $asset->useful_life_years; $year++) {
            $depreciation = match ($asset->depreciation_method) {
                'straight_line' => $asset->calculateStraightLineDepreciation(),
                'declining_balance' => min(
                    $currentBookValue * ($asset->category?->default_depreciation_rate / 100 ?? 20),
                    $currentBookValue - $asset->salvage_value
                ),
                'sum_of_years' => $this->calculateSumOfYearsForYear($asset, $year),
                default => 0,
            };

            $currentBookValue -= $depreciation;
            $projected[] = [
                'year' => $year,
                'depreciation' => $depreciation,
                'book_value' => max($currentBookValue, $asset->salvage_value),
            ];
        }

        return $projected;
    }

    /**
     * Calculate sum of years depreciation for specific year
     */
    private function calculateSumOfYearsForYear($asset, $year): float
    {
        $years = range(1, $asset->useful_life_years);
        $sumOfYears = array_sum($years);
        $remainingYears = $asset->useful_life_years - $year + 1;
        $depreciableAmount = $asset->purchase_cost - $asset->salvage_value;

        return ($depreciableAmount * $remainingYears) / $sumOfYears;
    }
}

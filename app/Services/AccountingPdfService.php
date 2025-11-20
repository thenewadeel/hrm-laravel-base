<?php

namespace App\Services;

use App\Models\Accounting\ChartOfAccount;
use App\Services\PdfThemeManager;
use Dompdf\Dompdf;
use Dompdf\Options;

class AccountingPdfService
{
    private AccountingReportService $reportService;
    private PdfThemeManager $themeManager;

    public function __construct(AccountingReportService $reportService, PdfThemeManager $themeManager)
    {
        $this->reportService = $reportService;
        $this->themeManager = $themeManager;
    }

    /**
     * Generate Trial Balance PDF
     */
    public function generateTrialBalancePdf(?\DateTimeInterface $asOfDate = null): string
    {
        $asOfDate = $asOfDate ?? now();
        $data = $this->reportService->generateTrialBalance($asOfDate);
        
        $pdf = new Dompdf();
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
        
        $pdf = new Dompdf();
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
        
        $pdf = new Dompdf();
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
            'brand' => $brand
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
            'brand' => $brand
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
            'brand' => $brand
        ])->render();
    }

    /**
     * Download Trial Balance PDF
     */
    public function downloadTrialBalance(\DateTimeInterface $asOfDate = null)
    {
        $asOfDate = $asOfDate ?? now();
        $pdfContent = $this->generateTrialBalancePdf($asOfDate);
        $filename = "trial-balance-" . $asOfDate->format('Y-m-d') . ".pdf";
        
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
        $filename = "income-statement-" . $startDate->format('Y-m-d') . "-to-" . $endDate->format('Y-m-d') . ".pdf";
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    /**
     * Download Balance Sheet PDF
     */
    public function downloadBalanceSheet(\DateTimeInterface $asOfDate)
    {
        $pdfContent = $this->generateBalanceSheetPdf($asOfDate);
        $filename = "balance-sheet-" . $asOfDate->format('Y-m-d') . ".pdf";
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }
}
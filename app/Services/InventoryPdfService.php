<?php

namespace App\Services;

use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\TransactionItem;
use App\Services\PdfThemeManager;
use Dompdf\Dompdf;

class InventoryPdfService
{
    private PdfThemeManager $themeManager;

    public function __construct(PdfThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * Generate Low Stock Report PDF
     */
    public function generateLowStockPdf(array $outOfStockItems, array $lowStockItems, array $reorderSuggestions, array $filters = []): string
    {
        $pdf = new Dompdf();
        $pdf->setPaper('A4', 'portrait');
        
        $html = $this->getLowStockHtml($outOfStockItems, $lowStockItems, $reorderSuggestions, $filters);
        $pdf->loadHtml($html);
        $pdf->render();
        
        return $pdf->output();
    }

    /**
     * Generate Stock Levels Report PDF
     */
    public function generateStockLevelsPdf(array $items, array $summary, array $filters = []): string
    {
        $pdf = new Dompdf();
        $pdf->setPaper('A4', 'portrait');
        
        $html = $this->getStockLevelsHtml($items, $summary, $filters);
        $pdf->loadHtml($html);
        $pdf->render();
        
        return $pdf->output();
    }

    /**
     * Generate Movement Report PDF
     */
    public function generateMovementPdf(object $movements, array $summary, array $topReceived, array $topIssued, array $filters = []): string
    {
        $pdf = new Dompdf();
        $pdf->setPaper('A4', 'portrait');
        
        $html = $this->getMovementHtml($movements, $summary, $topReceived, $topIssued, $filters);
        $pdf->loadHtml($html);
        $pdf->render();
        
        return $pdf->output();
    }

    /**
     * Generate HTML for Low Stock Report
     */
    private function getLowStockHtml(array $outOfStockItems, array $lowStockItems, array $reorderSuggestions, array $filters): string
    {
        $theme = $this->themeManager->getTheme();
        $brand = $this->themeManager->getBrand();
        
        return view('inventory.pdf.low-stock', [
            'outOfStockItems' => $outOfStockItems,
            'lowStockItems' => $lowStockItems,
            'reorderSuggestions' => $reorderSuggestions,
            'filters' => $filters,
            'theme' => $theme,
            'brand' => $brand
        ])->render();
    }

    /**
     * Generate HTML for Stock Levels Report
     */
    private function getStockLevelsHtml(array $items, array $summary, array $filters): string
    {
        $theme = $this->themeManager->getTheme();
        $brand = $this->themeManager->getBrand();
        
        return view('inventory.pdf.stock-levels', [
            'items' => $items,
            'summary' => $summary,
            'filters' => $filters,
            'theme' => $theme,
            'brand' => $brand
        ])->render();
    }

    /**
     * Generate HTML for Movement Report
     */
    private function getMovementHtml(object $movements, array $summary, array $topReceived, array $topIssued, array $filters): string
    {
        $theme = $this->themeManager->getTheme();
        $brand = $this->themeManager->getBrand();
        
        return view('inventory.pdf.movement', [
            'movements' => $movements,
            'summary' => $summary,
            'topReceived' => $topReceived,
            'topIssued' => $topIssued,
            'filters' => $filters,
            'theme' => $theme,
            'brand' => $brand
        ])->render();
    }

    /**
     * Download Low Stock Report PDF
     */
    public function downloadLowStock(array $outOfStockItems, array $lowStockItems, array $reorderSuggestions, array $filters = [])
    {
        $pdfContent = $this->generateLowStockPdf($outOfStockItems, $lowStockItems, $reorderSuggestions, $filters);
        $filename = "low-stock-report-" . now()->format('Y-m-d') . ".pdf";
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    /**
     * Download Stock Levels Report PDF
     */
    public function downloadStockLevels(array $items, array $summary, array $filters = [])
    {
        $pdfContent = $this->generateStockLevelsPdf($items, $summary, $filters);
        $filename = "stock-levels-report-" . now()->format('Y-m-d') . ".pdf";
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    /**
     * Download Movement Report PDF
     */
    public function downloadMovement(object $movements, array $summary, array $topReceived, array $topIssued, array $filters = [])
    {
        $pdfContent = $this->generateMovementPdf($movements, $summary, $topReceived, $topIssued, $filters);
        $filename = "movement-report-" . now()->format('Y-m-d') . ".pdf";
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }
}
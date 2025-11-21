<?php

namespace App\Services;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankStatement;
use Dompdf\Dompdf;

class BankStatementPdfService
{
    private AccountingPdfService $accountingPdfService;

    public function __construct(AccountingPdfService $accountingPdfService)
    {
        $this->accountingPdfService = $accountingPdfService;
    }

    public function generateBankStatementPdf(BankStatement $bankStatement): string
    {
        $bankStatement->load(['bankAccount', 'bankTransactions' => function ($query) {
            $query->orderBy('transaction_date');
        }]);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getBankStatementHtml($bankStatement);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    public function generateBankTransactionsPdf(BankAccount $bankAccount, $startDate = null, $endDate = null): string
    {
        $query = $bankAccount->bankTransactions()->with('bankStatement');

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        $transactions = $query->orderBy('transaction_date')->get();

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getBankTransactionsHtml($bankAccount, $transactions, $startDate, $endDate);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    public function generateReconciliationReportPdf(BankAccount $bankAccount, $reconciliationData): string
    {
        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getReconciliationReportHtml($bankAccount, $reconciliationData);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    private function getBankStatementHtml(BankStatement $bankStatement): string
    {
        $theme = $this->accountingPdfService->themeManager->getTheme();
        $brand = $this->accountingPdfService->themeManager->getBrand();

        return view('accounting.pdf.bank-statement', [
            'bankStatement' => $bankStatement,
            'theme' => $theme,
            'brand' => $brand,
        ])->render();
    }

    private function getBankTransactionsHtml(BankAccount $bankAccount, $transactions, $startDate, $endDate): string
    {
        $theme = $this->accountingPdfService->themeManager->getTheme();
        $brand = $this->accountingPdfService->themeManager->getBrand();

        return view('accounting.pdf.bank-transactions', [
            'bankAccount' => $bankAccount,
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'theme' => $theme,
            'brand' => $brand,
        ])->render();
    }

    private function getReconciliationReportHtml(BankAccount $bankAccount, $reconciliationData): string
    {
        $theme = $this->accountingPdfService->themeManager->getTheme();
        $brand = $this->accountingPdfService->themeManager->getBrand();

        return view('accounting.pdf.bank-reconciliation', [
            'bankAccount' => $bankAccount,
            'reconciliationData' => $reconciliationData,
            'theme' => $theme,
            'brand' => $brand,
        ])->render();
    }

    public function downloadBankStatement(BankStatement $bankStatement)
    {
        $pdfContent = $this->generateBankStatementPdf($bankStatement);
        $filename = 'bank-statement-'.$bankStatement->statement_number.'.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    public function downloadBankTransactions(BankAccount $bankAccount, $startDate = null, $endDate = null)
    {
        $pdfContent = $this->generateBankTransactionsPdf($bankAccount, $startDate, $endDate);

        $dateRange = ($startDate && $endDate) ?
            $startDate->format('Y-m-d').'-to-'.$endDate->format('Y-m-d') :
            'all-time';
        $filename = 'bank-transactions-'.$bankAccount->account_number.'-'.$dateRange.'.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    public function downloadReconciliationReport(BankAccount $bankAccount, $reconciliationData)
    {
        $pdfContent = $this->generateReconciliationReportPdf($bankAccount, $reconciliationData);
        $filename = 'bank-reconciliation-'.$bankAccount->account_number.'-'.now()->format('Y-m-d').'.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }
}

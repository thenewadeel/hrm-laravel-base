<?php

namespace App\Http\Controllers;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankStatement;
use App\Models\Accounting\ChartOfAccount;
use App\Services\AccountingPdfService;
use App\Services\BankReconciliationService;
use App\Services\BankStatementPdfService;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    private AccountingPdfService $pdfService;

    private BankStatementPdfService $bankStatementPdfService;

    private BankReconciliationService $bankReconciliationService;

    public function __construct(
        AccountingPdfService $pdfService,
        BankStatementPdfService $bankStatementPdfService,
        BankReconciliationService $bankReconciliationService
    ) {
        $this->pdfService = $pdfService;
        $this->bankStatementPdfService = $bankStatementPdfService;
        $this->bankReconciliationService = $bankReconciliationService;
    }

    public function index()
    {
        $accounts = ChartOfAccount::with('ledgerEntries')->get();

        return view('accounts.index', compact('accounts'));
    }

    /**
     * Download Trial Balance PDF
     */
    public function downloadTrialBalance(Request $request)
    {
        $asOfDate = $request->get('as_of_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('as_of_date'))
            : now();

        return $this->pdfService->downloadTrialBalance($asOfDate);
    }

    /**
     * Download Income Statement PDF
     */
    public function downloadIncomeStatement(Request $request)
    {
        $startDate = $request->get('start_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('start_date'))
            : now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('end_date'))
            : now();

        return $this->pdfService->downloadIncomeStatement($startDate, $endDate);
    }

    /**
     * Download Balance Sheet PDF
     */
    public function downloadBalanceSheet(Request $request)
    {
        $asOfDate = $request->get('as_of_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('as_of_date'))
            : now();

        return $this->pdfService->downloadBalanceSheet($asOfDate);
    }

    /**
     * Download Receivables Outstanding PDF
     */
    public function downloadReceivablesOutstanding(Request $request)
    {
        $customerId = $request->get('customer_id') ? (int) $request->get('customer_id') : null;
        $asOfDate = $request->get('as_of_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('as_of_date'))
            : null;
        $startDate = $request->get('start_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('start_date'))
            : null;
        $endDate = $request->get('end_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('end_date'))
            : null;

        return $this->pdfService->downloadReceivablesOutstanding(
            customerId: $customerId,
            asOfDate: $asOfDate,
            startDate: $startDate,
            endDate: $endDate
        );
    }

    /**
     * Download Payables Outstanding PDF
     */
    public function downloadPayablesOutstanding(Request $request)
    {
        $vendorId = $request->get('vendor_id') ? (int) $request->get('vendor_id') : null;
        $asOfDate = $request->get('as_of_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('as_of_date'))
            : null;
        $startDate = $request->get('start_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('start_date'))
            : null;
        $endDate = $request->get('end_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('end_date'))
            : null;

        return $this->pdfService->downloadPayablesOutstanding(
            vendorId: $vendorId,
            asOfDate: $asOfDate,
            startDate: $startDate,
            endDate: $endDate
        );
    }

    /**
     * Download Bank Statement PDF
     */
    public function downloadBankStatement(Request $request, BankStatement $bankStatement)
    {
        return $this->bankStatementPdfService->downloadBankStatement($bankStatement);
    }

    /**
     * Download Bank Transactions PDF
     */
    public function downloadBankTransactions(Request $request, BankAccount $bankAccount)
    {
        $startDate = $request->get('start_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('start_date'))
            : null;
        $endDate = $request->get('end_date')
            ? \DateTime::createFromFormat('Y-m-d', $request->get('end_date'))
            : null;

        return $this->bankStatementPdfService->downloadBankTransactions($bankAccount, $startDate, $endDate);
    }

    /**
     * Download Bank Reconciliation Report PDF
     */
    public function downloadBankReconciliation(Request $request, BankAccount $bankAccount)
    {
        $reconciliationData = $this->bankReconciliationService->getReconciliationSummary($bankAccount);

        return $this->bankStatementPdfService->downloadReconciliationReport($bankAccount, $reconciliationData);
    }
}

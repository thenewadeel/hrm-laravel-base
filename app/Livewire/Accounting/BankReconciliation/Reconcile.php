<?php

namespace App\Livewire\Accounting\BankReconciliation;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankStatement;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\LedgerEntry;
use App\Services\BankReconciliationService;
use Livewire\Component;
use Livewire\WithPagination;

class Reconcile extends Component
{
    use WithPagination;

    public $bankAccountId;

    public $bankStatementId;

    public $statementBalance;

    public $bookBalance;

    public $difference;

    public $outstandingDeposits = 0;

    public $outstandingWithdrawals = 0;

    public $reconciliationNotes = '';

    public $selectedTransactions = [];

    public $showMatchModal = false;

    public $currentBankTransaction;

    public $availableLedgerEntries = [];

    public $selectedLedgerEntryId;

    protected $reconciliationService;

    public function boot(BankReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    public function mount($bankAccountId = null, $bankStatementId = null)
    {
        $this->bankAccountId = $bankAccountId;
        $this->bankStatementId = $bankStatementId;

        if ($bankAccountId) {
            $this->loadReconciliationData();
        }
    }

    public function render()
    {
        $bankAccounts = BankAccount::active()->get();
        $bankStatements = collect();

        if ($this->bankAccountId) {
            $bankStatements = BankStatement::where('bank_account_id', $this->bankAccountId)
                ->where('status', '!=', 'reconciled')
                ->orderBy('statement_date', 'desc')
                ->get();
        }

        $bankTransactions = collect();
        $unmatchedTransactions = collect();
        $pendingTransactions = collect();

        if ($this->bankAccountId) {
            $bankTransactions = BankTransaction::where('bank_account_id', $this->bankAccountId)
                ->with(['bankStatement', 'matchedLedgerEntry'])
                ->latest('transaction_date')
                ->paginate(20);

            $unmatchedTransactions = BankTransaction::where('bank_account_id', $this->bankAccountId)
                ->where('reconciliation_status', 'unmatched')
                ->latest('transaction_date')
                ->get();

            $pendingTransactions = BankTransaction::where('bank_account_id', $this->bankAccountId)
                ->where('status', 'pending')
                ->latest('transaction_date')
                ->get();
        }

        return view('livewire.accounting.bank-reconciliation.reconcile', [
            'bankAccounts' => $bankAccounts,
            'bankStatements' => $bankStatements,
            'bankTransactions' => $bankTransactions,
            'unmatchedTransactions' => $unmatchedTransactions,
            'pendingTransactions' => $pendingTransactions,
        ]);
    }

    public function loadReconciliationData()
    {
        $bankAccount = BankAccount::find($this->bankAccountId);
        if (! $bankAccount) {
            return;
        }

        $this->bookBalance = $this->reconciliationService->calculateBookBalance($bankAccount);

        if ($this->bankStatementId) {
            $bankStatement = BankStatement::find($this->bankStatementId);
            $this->statementBalance = $bankStatement->closing_balance;
        } else {
            $this->statementBalance = $bankAccount->current_balance;
        }

        $this->difference = $this->statementBalance - $this->bookBalance;

        $this->calculateOutstandingItems();
    }

    public function calculateOutstandingItems()
    {
        if (! $this->bankAccountId) {
            return;
        }

        $this->outstandingDeposits = BankTransaction::where('bank_account_id', $this->bankAccountId)
            ->where('transaction_type', 'credit')
            ->where('status', 'pending')
            ->sum('amount');

        $this->outstandingWithdrawals = BankTransaction::where('bank_account_id', $this->bankAccountId)
            ->where('transaction_type', 'debit')
            ->where('status', 'pending')
            ->sum('amount');
    }

    public function startReconciliation()
    {
        if (! $this->bankAccountId) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Please select a bank account.',
            ]);

            return;
        }

        $bankAccount = BankAccount::find($this->bankAccountId);
        $bankStatement = $this->bankStatementId ? BankStatement::find($this->bankStatementId) : null;

        $reconciliation = $this->reconciliationService->startReconciliation($bankAccount, $bankStatement);

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Reconciliation started successfully.',
        ]);
    }

    public function autoMatchTransactions()
    {
        if (! $this->bankAccountId) {
            return;
        }

        $bankAccount = BankAccount::find($this->bankAccountId);
        $result = $this->reconciliationService->autoMatchTransactions($bankAccount);

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => "Auto-matched {$result['matched']} transactions. {$result['unmatched']} remain unmatched.",
        ]);

        $this->loadReconciliationData();
    }

    public function openMatchModal(BankTransaction $bankTransaction)
    {
        $this->currentBankTransaction = $bankTransaction;
        $this->availableLedgerEntries = $this->reconciliationService->findMatchingTransactions($bankTransaction);
        $this->selectedLedgerEntryId = null;
        $this->showMatchModal = true;
    }

    public function matchTransaction()
    {
        if (! $this->currentBankTransaction || ! $this->selectedLedgerEntryId) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Please select a ledger entry to match.',
            ]);

            return;
        }

        $ledgerEntry = LedgerEntry::find($this->selectedLedgerEntryId);
        $success = $this->reconciliationService->matchTransaction($this->currentBankTransaction, $ledgerEntry);

        if ($success) {
            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => 'Transaction matched successfully.',
            ]);
            $this->showMatchModal = false;
            $this->loadReconciliationData();
        } else {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Failed to match transaction.',
            ]);
        }
    }

    public function unmatchTransaction(BankTransaction $bankTransaction)
    {
        $success = $this->reconciliationService->unmatchTransaction($bankTransaction);

        if ($success) {
            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => 'Transaction unmatched successfully.',
            ]);
            $this->loadReconciliationData();
        } else {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Failed to unmatch transaction.',
            ]);
        }
    }

    public function completeReconciliation()
    {
        if (! $this->bankAccountId) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Please select a bank account.',
            ]);

            return;
        }

        if (abs($this->difference) > 0.01) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Cannot complete reconciliation. Please resolve the difference first.',
            ]);

            return;
        }

        $bankAccount = BankAccount::find($this->bankAccountId);
        $bankStatement = $this->bankStatementId ? BankStatement::find($this->bankStatementId) : null;

        $reconciliation = $this->reconciliationService->startReconciliation($bankAccount, $bankStatement);
        $reconciliation->update(['notes' => $this->reconciliationNotes]);

        $success = $this->reconciliationService->completeReconciliation($reconciliation, auth()->user());

        if ($success) {
            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => 'Bank reconciliation completed successfully.',
            ]);

            $this->reconciliationNotes = '';
            $this->loadReconciliationData();
        } else {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Failed to complete reconciliation.',
            ]);
        }
    }

    public function updatedBankAccountId()
    {
        $this->bankStatementId = null;
        $this->loadReconciliationData();
    }

    public function updatedBankStatementId()
    {
        $this->loadReconciliationData();
    }

    public function closeModal()
    {
        $this->showMatchModal = false;
        $this->currentBankTransaction = null;
        $this->availableLedgerEntries = [];
        $this->selectedLedgerEntryId = null;
    }
}

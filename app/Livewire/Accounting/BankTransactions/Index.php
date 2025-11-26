<?php

namespace App\Livewire\Accounting\BankTransactions;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $filterBankAccount = '';

    public $filterType = '';

    public $filterStatus = '';

    public $filterReconciliationStatus = '';

    public $filterDateRange = '';

    public $startDate = '';

    public $endDate = '';

    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterBankAccount' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterReconciliationStatus' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function render()
    {
        $query = BankTransaction::query()
            ->with(['bankAccount', 'bankStatement', 'matchedLedgerEntry'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%'.$this->search.'%')
                        ->orWhere('transaction_number', 'like', '%'.$this->search.'%')
                        ->orWhere('reference_number', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterBankAccount, function ($query) {
                $query->where('bank_account_id', $this->filterBankAccount);
            })
            ->when($this->filterType, function ($query) {
                $query->where('transaction_type', $this->filterType);
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterReconciliationStatus, function ($query) {
                $query->where('reconciliation_status', $this->filterReconciliationStatus);
            })
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('transaction_date', [$this->startDate, $this->endDate]);
            })
            ->latest('transaction_date');

        $bankTransactions = $query->paginate($this->perPage);

        return view('livewire.accounting.bank-transactions.index', [
            'bankTransactions' => $bankTransactions,
            'bankAccounts' => BankAccount::active()->get(),
            'transactionTypes' => [
                'debit' => 'Debit',
                'credit' => 'Credit',
            ],
            'statuses' => [
                'pending' => 'Pending',
                'cleared' => 'Cleared',
                'reconciled' => 'Reconciled',
            ],
            'reconciliationStatuses' => [
                'unmatched' => 'Unmatched',
                'matched' => 'Matched',
                'partially_matched' => 'Partially Matched',
            ],
        ]);
    }

    public function deleteBankTransaction(BankTransaction $bankTransaction)
    {
        if ($bankTransaction->status === 'reconciled') {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Cannot delete reconciled transaction.',
            ]);

            return;
        }

        $bankTransaction->delete();

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Bank transaction deleted successfully.',
        ]);
    }

    public function markAsCleared(BankTransaction $bankTransaction)
    {
        $bankTransaction->update(['status' => 'cleared']);

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Transaction marked as cleared.',
        ]);
    }

    public function markAsPending(BankTransaction $bankTransaction)
    {
        $bankTransaction->update(['status' => 'pending']);

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Transaction marked as pending.',
        ]);
    }

    public function resetFilters()
    {
        $this->reset([
            'search', 'filterBankAccount', 'filterType', 'filterStatus',
            'filterReconciliationStatus', 'startDate', 'endDate',
        ]);
    }

    public function updatedFilterDateRange($value)
    {
        if ($value === 'current_month') {
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
            $this->endDate = now()->endOfMonth()->format('Y-m-d');
        } elseif ($value === 'last_month') {
            $this->startDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
            $this->endDate = now()->subMonth()->endOfMonth()->format('Y-m-d');
        } elseif ($value === 'current_year') {
            $this->startDate = now()->startOfYear()->format('Y-m-d');
            $this->endDate = now()->endOfYear()->format('Y-m-d');
        } elseif ($value === 'last_30_days') {
            $this->startDate = now()->subDays(30)->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
        }
    }
}

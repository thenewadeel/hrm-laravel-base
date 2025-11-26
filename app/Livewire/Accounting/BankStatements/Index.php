<?php

namespace App\Livewire\Accounting\BankStatements;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankStatement;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $filterBankAccount = '';

    public $filterStatus = '';

    public $filterDateRange = '';

    public $startDate = '';

    public $endDate = '';

    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterBankAccount' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function render()
    {
        $query = BankStatement::query()
            ->with(['bankAccount', 'bankTransactions' => function ($query) {
                $query->latest()->take(5);
            }])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('statement_number', 'like', '%'.$this->search.'%')
                        ->orWhere('notes', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterBankAccount, function ($query) {
                $query->where('bank_account_id', $this->filterBankAccount);
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('statement_date', [$this->startDate, $this->endDate]);
            })
            ->latest('statement_date');

        $bankStatements = $query->paginate($this->perPage);

        return view('livewire.accounting.bank-statements.index', [
            'bankStatements' => $bankStatements,
            'bankAccounts' => BankAccount::active()->get(),
            'statuses' => [
                'imported' => 'Imported',
                'reconciled' => 'Reconciled',
                'partial' => 'Partially Reconciled',
            ],
        ]);
    }

    public function deleteBankStatement(BankStatement $bankStatement)
    {
        if ($bankStatement->status === 'reconciled') {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Cannot delete reconciled bank statement.',
            ]);

            return;
        }

        $bankStatement->delete();

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Bank statement deleted successfully.',
        ]);
    }

    public function markAsReconciled(BankStatement $bankStatement)
    {
        $bankStatement->update(['status' => 'reconciled']);

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Bank statement marked as reconciled.',
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterBankAccount', 'filterStatus', 'startDate', 'endDate']);
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

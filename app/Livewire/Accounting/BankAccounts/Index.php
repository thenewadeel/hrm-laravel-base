<?php

namespace App\Livewire\Accounting\BankAccounts;

use App\Models\Accounting\BankAccount;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $filterBank = '';

    public $filterStatus = '';

    public $filterType = '';

    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterBank' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterType' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function render()
    {
        $query = BankAccount::query()
            ->with(['chartOfAccount', 'bankTransactions' => function ($query) {
                $query->latest()->take(5);
            }])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('account_name', 'like', '%'.$this->search.'%')
                        ->orWhere('account_number', 'like', '%'.$this->search.'%')
                        ->orWhere('bank_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterBank, function ($query) {
                $query->byBank($this->filterBank);
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterType, function ($query) {
                $query->where('account_type', $this->filterType);
            })
            ->latest();

        $bankAccounts = $query->paginate($this->perPage);

        return view('livewire.accounting.bank-accounts.index', [
            'bankAccounts' => $bankAccounts,
            'bankNames' => BankAccount::distinct()->pluck('bank_name')->filter(),
            'accountTypes' => [
                'checking' => 'Checking Account',
                'savings' => 'Savings Account',
                'money_market' => 'Money Market Account',
                'cd' => 'Certificate of Deposit',
            ],
            'statuses' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'closed' => 'Closed',
            ],
        ]);
    }

    public function deleteBankAccount(BankAccount $bankAccount)
    {
        if ($bankAccount->bankTransactions()->exists()) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Cannot delete bank account with existing transactions.',
            ]);

            return;
        }

        $bankAccount->delete();

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Bank account deleted successfully.',
        ]);
    }

    public function toggleStatus(BankAccount $bankAccount)
    {
        $newStatus = $bankAccount->status === 'active' ? 'inactive' : 'active';
        $bankAccount->update(['status' => $newStatus]);

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => "Bank account status changed to {$newStatus}.",
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterBank', 'filterStatus', 'filterType']);
    }
}

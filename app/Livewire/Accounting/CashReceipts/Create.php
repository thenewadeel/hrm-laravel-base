<?php

namespace App\Livewire\Accounting\CashReceipts;

use App\Models\Accounting\ChartOfAccount;
use App\Permissions\AccountingPermissions;
use App\Services\CashReceiptService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Create extends Component
{
    #[Validate('required|date')]
    public $date = '';

    #[Validate('required|string|max:255')]
    public $received_from = '';

    #[Validate('required|numeric|min:0.01')]
    public $amount = '';

    #[Validate('required|exists:chart_of_accounts,id')]
    public $cash_account_id = '';

    #[Validate('required|exists:chart_of_accounts,id')]
    public $credit_account_id = '';

    #[Validate('nullable|string|max:500')]
    public $description = '';

    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    public function mount(): void
    {
        $this->authorize(AccountingPermissions::CREATE_CASH_RECEIPTS);
        $this->date = now()->format('Y-m-d');
    }

    public function createReceipt(): void
    {
        $this->authorize(AccountingPermissions::CREATE_CASH_RECEIPTS);
        $this->validate();

        $data = [
            'date' => $this->date,
            'received_from' => $this->received_from,
            'amount' => $this->amount,
            'cash_account_id' => $this->cash_account_id,
            'credit_account_id' => $this->credit_account_id,
            'description' => $this->description,
            'notes' => $this->notes,
        ];

        try {
            $receipt = app(CashReceiptService::class)->createReceipt($data, auth()->user()->current_organization_id);

            $this->dispatch('cash-receipt-created', receipt: $receipt);
            $this->dispatch('show-message', message: 'Cash receipt created successfully!', type: 'success');

            // Reset form
            $this->reset(['received_from', 'amount', 'cash_account_id', 'credit_account_id', 'description', 'notes']);
            $this->date = now()->format('Y-m-d');
        } catch (\Exception $e) {
            $this->dispatch('show-message', message: 'Error creating cash receipt: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $organizationId = auth()->user()->current_organization_id;

        return view('livewire.accounting.cash-receipts.create', [
            'cashAccounts' => ChartOfAccount::where('organization_id', $organizationId)
                ->where('type', 'asset')
                ->orderBy('name')
                ->get(),
            'creditAccounts' => ChartOfAccount::where('organization_id', $organizationId)
                ->whereIn('type', ['revenue', 'liability', 'equity'])
                ->orderBy('name')
                ->get(),
        ]);
    }
}

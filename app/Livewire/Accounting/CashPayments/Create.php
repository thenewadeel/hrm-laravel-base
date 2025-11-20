<?php

namespace App\Livewire\Accounting\CashPayments;

use App\Models\Accounting\ChartOfAccount;
use App\Permissions\AccountingPermissions;
use App\Services\CashPaymentService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Create extends Component
{
    #[Validate('required|date')]
    public $date = '';

    #[Validate('required|string|max:255')]
    public $paid_to = '';

    #[Validate('required|numeric|min:0.01')]
    public $amount = '';

    #[Validate('required|exists:chart_of_accounts,id')]
    public $cash_account_id = '';

    #[Validate('required|exists:chart_of_accounts,id')]
    public $debit_account_id = '';

    #[Validate('nullable|string|max:500')]
    public $purpose = '';

    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    public function mount(): void
    {
        $this->authorize(AccountingPermissions::CREATE_CASH_PAYMENTS);
        $this->date = now()->format('Y-m-d');
    }

    public function createPayment(): void
    {
        $this->authorize(AccountingPermissions::CREATE_CASH_PAYMENTS);
        $this->validate();

        $data = [
            'date' => $this->date,
            'paid_to' => $this->paid_to,
            'amount' => $this->amount,
            'cash_account_id' => $this->cash_account_id,
            'debit_account_id' => $this->debit_account_id,
            'purpose' => $this->purpose,
            'notes' => $this->notes,
        ];

        try {
            $payment = app(CashPaymentService::class)->createPayment($data, auth()->user()->current_organization_id);

            $this->dispatch('cash-payment-created', payment: $payment);
            $this->dispatch('show-message', message: 'Cash payment created successfully!', type: 'success');

            // Reset form
            $this->reset(['paid_to', 'amount', 'cash_account_id', 'debit_account_id', 'purpose', 'notes']);
            $this->date = now()->format('Y-m-d');
        } catch (\Exception $e) {
            $this->dispatch('show-message', message: 'Error creating cash payment: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $organizationId = auth()->user()->current_organization_id;

        return view('livewire.accounting.cash-payments.create', [
            'cashAccounts' => ChartOfAccount::where('organization_id', $organizationId)
                ->where('type', 'asset')
                ->orderBy('name')
                ->get(),
            'debitAccounts' => ChartOfAccount::where('organization_id', $organizationId)
                ->whereIn('type', ['expense', 'asset'])
                ->orderBy('name')
                ->get(),
        ]);
    }
}

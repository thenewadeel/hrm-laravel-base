<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Services\ExpenseVoucherService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ExpenseVoucherForm extends Component
{
    #[Validate('required|date')]
    public $entry_date = '';

    #[Validate('required|string|max:255')]
    public $description = '';

    #[Validate('required|exists:chart_of_accounts,code')]
    public $expense_account_code = '';

    #[Validate('required|numeric|min:0.01')]
    public $amount = 0;

    #[Validate('nullable|string|max:255')]
    public $reference = '';

    #[Validate('nullable|string')]
    public $notes = '';

    public function mount(): void
    {
        $this->entry_date = now()->format('Y-m-d');
    }

    public function createExpenseVoucher(): void
    {
        $this->validate();

        $data = [
            'entry_date' => $this->entry_date,
            'description' => $this->description,
            'expense_account_code' => $this->expense_account_code,
            'amount' => $this->amount,
            'reference' => $this->reference,
            'notes' => $this->notes,
        ];

        try {
            $voucher = app(ExpenseVoucherService::class)->createExpenseVoucher($data);

            $this->dispatch('expense-voucher-created', voucher: $voucher);
            $this->dispatch('show-message', message: 'Expense voucher created successfully!', type: 'success');

            // Reset form
            $this->mount();
        } catch (\Exception $e) {
            $this->dispatch('show-message', message: 'Error creating expense voucher: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.accounting.expense-voucher-form', [
            'expenseAccounts' => ChartOfAccount::where('organization_id', auth()->user()->current_organization_id)
                ->where('type', 'expense')
                ->orderBy('code')
                ->get(),
        ]);
    }
}

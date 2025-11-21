<?php

namespace App\Livewire\Accounting\Vouchers;

use App\Models\Accounting\ChartOfAccount;
use App\Permissions\AccountingPermissions;
use App\Services\GeneralVoucherService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Create extends Component
{
    #[Validate('required|date')]
    public $date = '';

    #[Validate('required|string|in:sales,sales_return,purchase,purchase_return,salary,expense,fixed_asset,depreciation')]
    public $type = '';

    #[Validate('required|numeric|min:0.01')]
    public $amount = '';

    #[Validate('required|string|max:255')]
    public $description = '';

    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    public function mount(): void
    {
        $this->authorize(AccountingPermissions::CREATE_VOUCHERS);
        $this->date = now()->format('Y-m-d');
    }

    public function createVoucher(): void
    {
        $this->authorize(AccountingPermissions::CREATE_VOUCHERS);
        $this->validate();

        $data = [
            'date' => $this->date,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'notes' => $this->notes,
        ];

        try {
            $voucher = app(GeneralVoucherService::class)->createVoucher(
                $data,
                auth()->user()->current_organization_id,
                auth()->id()
            );

            $this->dispatch('voucher-created', voucher: $voucher);
            $this->dispatch('show-message', message: 'Voucher created successfully!', type: 'success');

            // Reset form
            $this->reset(['type', 'amount', 'description', 'notes']);
            $this->date = now()->format('Y-m-d');
        } catch (\Exception $e) {
            $this->dispatch('show-message', message: 'Error creating voucher: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $organizationId = auth()->user()->current_organization_id;

        return view('livewire.accounting.vouchers.create', [
            'accounts' => ChartOfAccount::where('organization_id', $organizationId)
                ->orderBy('name')
                ->get(),
            'voucherTypes' => [
                'sales' => 'Sales',
                'sales_return' => 'Sales Return',
                'purchase' => 'Purchase',
                'purchase_return' => 'Purchase Return',
                'salary' => 'Salary',
                'expense' => 'Expense',
                'fixed_asset' => 'Fixed Asset',
                'depreciation' => 'Depreciation',
            ],
        ]);
    }
}

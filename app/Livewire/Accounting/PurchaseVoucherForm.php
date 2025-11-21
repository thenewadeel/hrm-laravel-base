<?php

namespace App\Livewire\Accounting;

use App\Models\Vendor;
use App\Services\PurchaseVoucherService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PurchaseVoucherForm extends Component
{
    #[Validate('required|date')]
    public $entry_date = '';

    #[Validate('required|string|max:255')]
    public $description = '';

    #[Validate('required|exists:vendors,id')]
    public $vendor_id = '';

    #[Validate('nullable|string|max:50')]
    public $invoice_number = '';

    #[Validate('nullable|date|after_or_equal:entry_date')]
    public $due_date = '';

    #[Validate('nullable|numeric|min:0')]
    public $tax_amount = 0;

    public Collection $line_items;

    public function mount(): void
    {
        $this->entry_date = now()->format('Y-m-d');
        $this->line_items = collect([
            [
                'description' => '',
                'quantity' => 1,
                'unit_price' => 0,
            ],
        ]);
    }

    public function addLineItem(): void
    {
        $this->line_items->push([
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ]);
    }

    public function removeLineItem(int $index): void
    {
        $this->line_items->forget($index);
        $this->line_items = $this->line_items->values();
    }

    public function calculateSubtotal(): float
    {
        return $this->line_items->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });
    }

    public function calculateTotal(): float
    {
        return $this->calculateSubtotal() + $this->tax_amount;
    }

    public function createPurchaseVoucher(): void
    {
        $this->validate();

        $data = [
            'entry_date' => $this->entry_date,
            'description' => $this->description,
            'vendor_id' => $this->vendor_id,
            'invoice_number' => $this->invoice_number,
            'due_date' => $this->due_date,
            'tax_amount' => $this->tax_amount,
            'line_items' => $this->line_items->toArray(),
        ];

        try {
            $voucher = app(PurchaseVoucherService::class)->createPurchaseVoucher($data);

            $this->dispatch('purchase-voucher-created', voucher: $voucher);
            $this->dispatch('show-message', message: 'Purchase voucher created successfully!', type: 'success');

            // Reset form
            $this->mount();
        } catch (\Exception $e) {
            $this->dispatch('show-message', message: 'Error creating purchase voucher: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.accounting.purchase-voucher-form', [
            'vendors' => Vendor::where('organization_id', auth()->user()->current_organization_id)
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get(),
        ]);
    }
}

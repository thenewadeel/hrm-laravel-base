<?php

// app/Services/PurchaseVoucherService.php

namespace App\Services;

use App\Models\Accounting\JournalEntry;
use App\Models\Vendor;
use Illuminate\Validation\ValidationException;

class PurchaseVoucherService extends VoucherService
{
    protected function getVoucherType(): string
    {
        return 'PURCHASE';
    }

    protected function generateReferenceNumber(): string
    {
        $latest = JournalEntry::where('voucher_type', 'PURCHASE')
            ->where('organization_id', auth()->user()->current_organization_id)
            ->orderBy('reference_number', 'desc')
            ->first();

        $nextNumber = $latest ? (int) str_replace('PURCHASE-', '', $latest->reference_number) + 1 : 1;

        return 'PURCHASE-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    protected function validateVoucherData(array $data): void
    {
        if (empty($data['vendor_id'])) {
            throw ValidationException::withMessages(['vendor_id' => 'Vendor is required for purchase voucher']);
        }

        if (empty($data['line_items']) || ! is_array($data['line_items'])) {
            throw ValidationException::withMessages(['line_items' => 'Line items are required']);
        }
    }

    protected function prepareLedgerEntries(array $data): array
    {
        $entries = [];
        $vendor = $this->validateVendor($data['vendor_id']);

        // Get purchase expense account (default: 5000)
        $purchaseAccount = $this->getAccountByCode('5000');

        // Get payable account (default: 2000)
        $payableAccount = $this->getAccountByCode('2000');

        // Get tax account (default: 1200)
        $taxAccount = $this->getAccountByCode('1200');

        $totalExpense = 0;
        $totalTax = 0;

        // Process line items
        foreach ($data['line_items'] as $item) {
            $amount = $item['quantity'] * $item['unit_price'];
            $totalExpense += $amount;

            // Debit purchase expense
            $entries[] = [
                'account' => $purchaseAccount,
                'type' => 'debit',
                'amount' => $amount,
                'description' => $item['description'] ?? 'Purchase item',
            ];
        }

        // Handle tax
        if (! empty($data['tax_amount']) && $data['tax_amount'] > 0) {
            $totalTax = $data['tax_amount'];

            // Debit input tax
            $entries[] = [
                'account' => $taxAccount,
                'type' => 'debit',
                'amount' => $totalTax,
                'description' => 'Input tax on purchases',
            ];
        }

        $totalAmount = $totalExpense + $totalTax;

        // Credit vendor payable
        $entries[] = [
            'account' => $payableAccount,
            'type' => 'credit',
            'amount' => $totalAmount,
            'description' => "Purchase from {$vendor->name}",
        ];

        return $entries;
    }

    public function createPurchaseVoucher(array $data): JournalEntry
    {
        // Calculate totals
        $totalAmount = 0;
        foreach ($data['line_items'] as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $data['total_amount'] = $totalAmount + ($data['tax_amount'] ?? 0);

        return $this->createVoucher($data);
    }
}

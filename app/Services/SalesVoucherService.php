<?php

// app/Services/SalesVoucherService.php

namespace App\Services;

use App\Models\Accounting\JournalEntry;
use App\Models\Customer;
use Illuminate\Validation\ValidationException;

class SalesVoucherService extends VoucherService
{
    protected function getVoucherType(): string
    {
        return 'SALES';
    }

    protected function generateReferenceNumber(): string
    {
        $latest = JournalEntry::where('voucher_type', 'SALES')
            ->where('organization_id', auth()->user()->organization_id)
            ->orderBy('reference_number', 'desc')
            ->first();

        $nextNumber = $latest ? (int) str_replace('SALES-', '', $latest->reference_number) + 1 : 1;

        return 'SALES-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    protected function validateVoucherData(array $data): void
    {
        if (empty($data['customer_id'])) {
            throw ValidationException::withMessages(['customer_id' => 'Customer is required for sales voucher']);
        }

        if (empty($data['line_items']) || ! is_array($data['line_items'])) {
            throw ValidationException::withMessages(['line_items' => 'Line items are required']);
        }
    }

    protected function prepareLedgerEntries(array $data): array
    {
        $entries = [];
        $customer = $this->validateCustomer($data['customer_id']);

        // Get sales revenue account (default: 4000)
        $salesAccount = $this->getAccountByCode('4000');

        // Get receivable account (default: 1200)
        $receivableAccount = $this->getAccountByCode('1200');

        // Get tax account (default: 2000)
        $taxAccount = $this->getAccountByCode('2000');

        $totalRevenue = 0;
        $totalTax = 0;

        // Process line items
        foreach ($data['line_items'] as $item) {
            $amount = $item['quantity'] * $item['unit_price'];
            $totalRevenue += $amount;

            // Credit sales revenue
            $entries[] = [
                'account' => $salesAccount,
                'type' => 'credit',
                'amount' => $amount,
                'description' => $item['description'] ?? 'Sales item',
            ];
        }

        // Handle tax
        if (! empty($data['tax_amount']) && $data['tax_amount'] > 0) {
            $totalTax = $data['tax_amount'];

            // Credit tax payable
            $entries[] = [
                'account' => $taxAccount,
                'type' => 'credit',
                'amount' => $totalTax,
                'description' => 'Output tax on sales',
            ];
        }

        $totalAmount = $totalRevenue + $totalTax;

        // Debit customer receivable
        $entries[] = [
            'account' => $receivableAccount,
            'type' => 'debit',
            'amount' => $totalAmount,
            'description' => "Invoice to {$customer->name}",
        ];

        return $entries;
    }

    public function createSalesVoucher(array $data): JournalEntry
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

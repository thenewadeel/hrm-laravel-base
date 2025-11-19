<?php

// app/Services/VoucherService.php

namespace App\Services;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

abstract class VoucherService
{
    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    abstract protected function getVoucherType(): string;

    abstract protected function generateReferenceNumber(): string;

    abstract protected function validateVoucherData(array $data): void;

    abstract protected function prepareLedgerEntries(array $data): array;

    public function createVoucher(array $data): JournalEntry
    {
        $this->validateVoucherData($data);

        return DB::transaction(function () use ($data) {
            $journalEntry = JournalEntry::create([
                'organization_id' => auth()->user()->current_organization_id,
                'reference_number' => $this->generateReferenceNumber(),
                'entry_date' => $data['entry_date'],
                'description' => $data['description'],
                'voucher_type' => $this->getVoucherType(),
                'customer_id' => $data['customer_id'] ?? null,
                'vendor_id' => $data['vendor_id'] ?? null,
                'total_amount' => $data['total_amount'] ?? null,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'invoice_number' => $data['invoice_number'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $ledgerEntries = $this->prepareLedgerEntries($data);
            $journalEntry->post($ledgerEntries);

            return $journalEntry;
        });
    }

    protected function getAccountByCode(string $code): ChartOfAccount
    {
        return ChartOfAccount::where('code', $code)
            ->where('organization_id', auth()->user()->current_organization_id)
            ->firstOrFail();
    }

    protected function validateCustomer(int $customerId): Customer
    {
        return Customer::where('id', $customerId)
            ->where('organization_id', auth()->user()->current_organization_id)
            ->firstOrFail();
    }

    protected function validateVendor(int $vendorId): Vendor
    {
        return Vendor::where('id', $vendorId)
            ->where('organization_id', auth()->user()->current_organization_id)
            ->firstOrFail();
    }
}

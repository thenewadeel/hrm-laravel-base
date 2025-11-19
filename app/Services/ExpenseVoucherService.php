<?php

// app/Services/ExpenseVoucherService.php

namespace App\Services;

use App\Models\Accounting\JournalEntry;
use Illuminate\Validation\ValidationException;

class ExpenseVoucherService extends VoucherService
{
    protected function getVoucherType(): string
    {
        return 'EXPENSE';
    }

    protected function generateReferenceNumber(): string
    {
        $latest = JournalEntry::where('voucher_type', 'EXPENSE')
            ->where('organization_id', auth()->user()->organization_id)
            ->orderBy('reference_number', 'desc')
            ->first();

        $nextNumber = $latest ? (int) str_replace('EXPENSE-', '', $latest->reference_number) + 1 : 1;

        return 'EXPENSE-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    protected function validateVoucherData(array $data): void
    {
        if (empty($data['expense_account_code'])) {
            throw ValidationException::withMessages(['expense_account_code' => 'Expense account is required']);
        }

        if (empty($data['amount']) || $data['amount'] <= 0) {
            throw ValidationException::withMessages(['amount' => 'Valid amount is required']);
        }
    }

    protected function prepareLedgerEntries(array $data): array
    {
        $entries = [];

        // Get the specific expense account from data
        $expenseAccount = $this->getAccountByCode($data['expense_account_code']);

        // Get cash/bank account (default: 1000)
        $cashAccount = $this->getAccountByCode('1000');

        $amount = $data['amount'];
        $description = $data['description'] ?? 'Expense payment';

        // Debit expense account
        $entries[] = [
            'account' => $expenseAccount,
            'type' => 'debit',
            'amount' => $amount,
            'description' => $description,
        ];

        // Credit cash/bank
        $entries[] = [
            'account' => $cashAccount,
            'type' => 'credit',
            'amount' => $amount,
            'description' => $description,
        ];

        return $entries;
    }

    public function createExpenseVoucher(array $data): JournalEntry
    {
        $data['total_amount'] = $data['amount'];

        return $this->createVoucher($data);
    }
}

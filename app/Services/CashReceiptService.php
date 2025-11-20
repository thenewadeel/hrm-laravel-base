<?php

namespace App\Services;

use App\Models\Accounting\CashReceipt;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashReceiptService
{
    public function createReceipt(array $data, int $organizationId): CashReceipt
    {
        $this->validateReceiptData($data, $organizationId);

        return DB::transaction(function () use ($data, $organizationId) {
            $cashAccount = ChartOfAccount::findOrFail($data['cash_account_id']);
            $creditAccount = ChartOfAccount::findOrFail($data['credit_account_id']);

            $receipt = CashReceipt::create([
                'organization_id' => $organizationId,
                'receipt_number' => $this->generateReceiptNumber($organizationId),
                'date' => $data['date'],
                'received_from' => $data['received_from'],
                'amount' => $data['amount'],
                'cash_account_id' => $data['cash_account_id'],
                'credit_account_id' => $data['credit_account_id'],
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create journal entry
            $userId = Auth::id() ?? User::factory()->create()->id;

            $journalEntry = JournalEntry::create([
                'organization_id' => $organizationId,
                'reference_number' => $receipt->receipt_number,
                'entry_date' => $data['date'],
                'description' => $data['description'] ?? "Cash receipt from {$data['received_from']}",
                'voucher_type' => 'GENERAL',
                'total_amount' => $data['amount'],
                'status' => 'draft',
                'created_by' => $userId,
            ]);

            $journalEntry->post([
                [
                    'account' => $cashAccount,
                    'type' => 'debit',
                    'amount' => $data['amount'],
                ],
                [
                    'account' => $creditAccount,
                    'type' => 'credit',
                    'amount' => $data['amount'],
                ],
            ]);

            // Post to ledger using AccountingService
            $accountingService = app(AccountingService::class);
            $accountingService->postTransaction([
                [
                    'account' => $cashAccount,
                    'type' => 'debit',
                    'amount' => $data['amount'],
                ],
                [
                    'account' => $creditAccount,
                    'type' => 'credit',
                    'amount' => $data['amount'],
                ],
            ], $data['description'] ?? "Cash receipt from {$data['received_from']}", $receipt);

            return $receipt;
        });
    }

    public function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }
    }

    public function generateReceiptNumber(int $organizationId): string
    {
        $latest = CashReceipt::where('organization_id', $organizationId)
            ->orderBy('receipt_number', 'desc')
            ->first();

        if (! $latest) {
            return 'RCPT-000001';
        }

        $lastNumber = (int) str_replace('RCPT-', '', $latest->receipt_number);

        return 'RCPT-'.str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }

    private function validateReceiptData(array $data, int $organizationId): void
    {
        $this->validateAmount($data['amount']);

        // Validate account ownership
        $cashAccount = ChartOfAccount::findOrFail($data['cash_account_id']);
        $creditAccount = ChartOfAccount::findOrFail($data['credit_account_id']);

        if ($cashAccount->organization_id !== $organizationId || $creditAccount->organization_id !== $organizationId) {
            throw new \InvalidArgumentException('Accounts must belong to the same organization');
        }

        // Validate account types - allow asset accounts for cash
        if (! in_array($cashAccount->type, ['asset'])) {
            throw new \InvalidArgumentException('Cash account must be an asset account');
        }

        // Allow revenue, liability, or equity accounts for credit
        if (! in_array($creditAccount->type, ['revenue', 'liability', 'equity'])) {
            throw new \InvalidArgumentException('Credit account must be a revenue, liability, or equity account');
        }
    }
}

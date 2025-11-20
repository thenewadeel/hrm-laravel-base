<?php

namespace App\Services;

use App\Models\Accounting\CashPayment;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashPaymentService
{
    public function createPayment(array $data, int $organizationId): CashPayment
    {
        $this->validatePaymentData($data, $organizationId);

        return DB::transaction(function () use ($data, $organizationId) {
            $cashAccount = ChartOfAccount::findOrFail($data['cash_account_id']);
            $debitAccount = ChartOfAccount::findOrFail($data['debit_account_id']);

            $payment = CashPayment::create([
                'organization_id' => $organizationId,
                'voucher_number' => $this->generateVoucherNumber($organizationId),
                'date' => $data['date'],
                'paid_to' => $data['paid_to'],
                'amount' => $data['amount'],
                'cash_account_id' => $data['cash_account_id'],
                'debit_account_id' => $data['debit_account_id'],
                'purpose' => $data['purpose'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create journal entry
            $userId = Auth::id() ?? User::factory()->create()->id;

            $journalEntry = JournalEntry::create([
                'organization_id' => $organizationId,
                'reference_number' => $payment->voucher_number,
                'entry_date' => $data['date'],
                'description' => $data['purpose'] ?? "Cash payment to {$data['paid_to']}",
                'voucher_type' => 'GENERAL',
                'total_amount' => $data['amount'],
                'status' => 'draft',
                'created_by' => $userId,
            ]);

            // Post ledger entries directly for cash payments
            \App\Models\Accounting\LedgerEntry::create([
                'entry_date' => $data['date'],
                'chart_of_account_id' => $debitAccount->id,
                'type' => 'debit',
                'amount' => $data['amount'],
                'description' => $data['purpose'] ?? "Cash payment to {$data['paid_to']}",
                'transactionable_type' => CashPayment::class,
                'transactionable_id' => $payment->id,
            ]);

            \App\Models\Accounting\LedgerEntry::create([
                'entry_date' => $data['date'],
                'chart_of_account_id' => $cashAccount->id,
                'type' => 'credit',
                'amount' => $data['amount'],
                'description' => $data['purpose'] ?? "Cash payment to {$data['paid_to']}",
                'transactionable_type' => CashPayment::class,
                'transactionable_id' => $payment->id,
            ]);

            $journalEntry->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            return $payment;
        });
    }

    public function validatePayment(array $data): void
    {
        $this->validateAmount($data['amount']);

        // Additional payment-specific validation can be added here
    }

    public function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }
    }

    public function generateVoucherNumber(int $organizationId): string
    {
        $latest = CashPayment::where('organization_id', $organizationId)
            ->orderBy('voucher_number', 'desc')
            ->first();

        if (! $latest) {
            return 'VCH-000001';
        }

        $lastNumber = (int) str_replace('VCH-', '', $latest->voucher_number);

        return 'VCH-'.str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }

    private function validatePaymentData(array $data, int $organizationId): void
    {
        $this->validateAmount($data['amount']);

        // Validate account ownership
        $cashAccount = ChartOfAccount::findOrFail($data['cash_account_id']);
        $debitAccount = ChartOfAccount::findOrFail($data['debit_account_id']);

        if ($cashAccount->organization_id !== $organizationId || $debitAccount->organization_id !== $organizationId) {
            throw new \InvalidArgumentException('Accounts must belong to the same organization');
        }

        // Validate account types
        if ($cashAccount->type !== 'asset') {
            throw new \InvalidArgumentException('Cash account must be an asset account');
        }

        if (! in_array($debitAccount->type, ['expense', 'asset'])) {
            throw new \InvalidArgumentException('Debit account must be an expense or asset account');
        }

        // Validate sufficient cash balance
        $this->validateCashBalance($cashAccount, $data['amount']);
    }

    private function validateCashBalance(ChartOfAccount $cashAccount, float $amount): void
    {
        $currentBalance = $cashAccount->getBalanceAttribute();

        if ($currentBalance < $amount) {
            throw new \InvalidArgumentException('Insufficient cash balance');
        }
    }
}

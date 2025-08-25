<?php
// app/Services/AccountingService.php

namespace App\Services;

use App\Exceptions\InvalidAccountTypeException;
use App\Exceptions\UnbalancedTransactionException;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\LedgerEntry;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Post a transaction to the general ledger
     */
    public function postTransaction(array $entries, string $description, $transactionable = null): void
    {
        $totalDebit = 0;
        $totalCredit = 0;

        // First validate all account types
        foreach ($entries as $entry) {
            $this->validateAccountType($entry['account'], $entry['type']);

            if ($entry['type'] === 'debit') {
                $totalDebit += $entry['amount'];
            } elseif ($entry['type'] === 'credit') {
                $totalCredit += $entry['amount'];
            } else {
                throw new \InvalidArgumentException("Entry type must be 'debit' or 'credit'");
            }
        }

        // Validate the fundamental rule of accounting
        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new UnbalancedTransactionException(
                "Transaction is unbalanced. Debits: {$totalDebit}, Credits: {$totalCredit}. Description: {$description}"
            );
        }

        DB::transaction(function () use ($entries, $description, $transactionable) {
            foreach ($entries as $entryData) {
                LedgerEntry::create([
                    'entry_date' => now(),
                    'chart_of_account_id' => $entryData['account']->id,
                    'type' => $entryData['type'],
                    'amount' => $entryData['amount'],
                    'description' => $description,
                    'transactionable_type' => $transactionable ? get_class($transactionable) : null,
                    'transactionable_id' => $transactionable ? $transactionable->id : null,
                ]);
            }
        });
    }

    /**
     * Validate that the account type can receive the given entry type
     *
     * @throws InvalidAccountTypeException
     */

    // In app/Services/AccountingService.php - validateAccountType method

    private function validateAccountType(ChartOfAccount $account, string $entryType): void
    {
        $validDebitAccounts = ['asset', 'expense'];
        $validCreditAccounts = ['liability', 'equity', 'revenue'];

        if ($entryType === 'debit' && !in_array($account->type, $validDebitAccounts)) {
            throw new InvalidAccountTypeException($account, $entryType);
        }

        if ($entryType === 'credit' && !in_array($account->type, $validCreditAccounts)) {
            throw new InvalidAccountTypeException($account, $entryType);
        }
    }
}

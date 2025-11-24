<?php

// app/Services/AccountingService.php

namespace App\Services;

use App\Exceptions\InvalidAccountTypeException;
use App\Exceptions\UnbalancedTransactionException;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
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
     * Validate that account type can receive given entry type
     *
     * @throws InvalidAccountTypeException
     */
    private function validateAccountType(ChartOfAccount $account, string $entryType): void
    {
        // In accounting, all account types can receive both debits and credits
        // The validation should only ensure the entry type is valid
        // Normal balance rules are:
        // Assets: Normal debit balance (debit increases, credit decreases)
        // Liabilities: Normal credit balance (credit increases, debit decreases)
        // Equity: Normal credit balance (credit increases, debit decreases)
        // Revenue: Normal credit balance (credit increases, debit decreases)
        // Expenses: Normal debit balance (debit increases, credit decreases)

        // All account types can have both debits and credits in proper accounting
        // We only need to validate that the entry type is valid
        if (! in_array($entryType, ['debit', 'credit'])) {
            throw new InvalidAccountTypeException($account, $entryType, "Entry type must be 'debit' or 'credit'");
        }
    }

    public function createPayrollJournalEntry($payrollRun)
    {
        return JournalEntry::create([
            'organization_id' => $payrollRun->organization_id,
            'reference_number' => 'PAY-'.$payrollRun->period,
            'entry_date' => now(),
            'description' => 'Payroll for '.$payrollRun->period,
            'status' => 'posted',
            // Debit Salary Expense, Credit Payroll Payable
        ]);
    }
}

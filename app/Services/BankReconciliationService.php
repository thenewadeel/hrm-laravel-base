<?php

namespace App\Services;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankReconciliation;
use App\Models\Accounting\BankStatement;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\LedgerEntry;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankReconciliationService
{
    public function startReconciliation(BankAccount $bankAccount, ?BankStatement $bankStatement = null): BankReconciliation
    {
        $statementBalance = $bankStatement?->closing_balance ?? $bankAccount->current_balance;
        $bookBalance = $this->calculateBookBalance($bankAccount);

        return BankReconciliation::create([
            'organization_id' => $bankAccount->organization_id,
            'bank_account_id' => $bankAccount->id,
            'bank_statement_id' => $bankStatement?->id,
            'reconciliation_date' => now(),
            'statement_balance' => $statementBalance,
            'book_balance' => $bookBalance,
            'difference' => $statementBalance - $bookBalance,
            'outstanding_deposits' => 0,
            'outstanding_withdrawals' => 0,
            'transactions_reconciled' => 0,
            'total_transactions' => $bankAccount->bankTransactions()->count(),
            'status' => 'in_progress',
        ]);
    }

    public function findMatchingTransactions(BankTransaction $bankTransaction): Collection
    {
        $query = LedgerEntry::query()
            ->where('entry_date', $bankTransaction->transaction_date)
            ->where('amount', abs($bankTransaction->amount));

        if ($bankTransaction->description) {
            $query->where('description', 'like', '%'.substr($bankTransaction->description, 0, 20).'%');
        }

        return $query->get();
    }

    public function matchTransaction(BankTransaction $bankTransaction, LedgerEntry $ledgerEntry): bool
    {
        try {
            DB::transaction(function () use ($bankTransaction, $ledgerEntry) {
                $bankTransaction->matchWithLedgerEntry($ledgerEntry);

                Log::info("Bank transaction {$bankTransaction->id} matched with ledger entry {$ledgerEntry->id}");
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to match bank transaction {$bankTransaction->id}: ".$e->getMessage());

            return false;
        }
    }

    public function unmatchTransaction(BankTransaction $bankTransaction): bool
    {
        try {
            DB::transaction(function () use ($bankTransaction) {
                $bankTransaction->unmatchFromLedgerEntry();

                Log::info("Bank transaction {$bankTransaction->id} unmatched from ledger entry");
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to unmatch bank transaction {$bankTransaction->id}: ".$e->getMessage());

            return false;
        }
    }

    public function autoMatchTransactions(BankAccount $bankAccount): array
    {
        $matched = 0;
        $unmatched = 0;

        $unmatchedTransactions = $bankAccount->bankTransactions()
            ->where('reconciliation_status', 'unmatched')
            ->get();

        foreach ($unmatchedTransactions as $bankTransaction) {
            $matches = $this->findMatchingTransactions($bankTransaction);

            if ($matches->count() === 1) {
                if ($this->matchTransaction($bankTransaction, $matches->first())) {
                    $matched++;
                } else {
                    $unmatched++;
                }
            } else {
                $unmatched++;
            }
        }

        return [
            'matched' => $matched,
            'unmatched' => $unmatched,
            'total' => $matched + $unmatched,
        ];
    }

    public function completeReconciliation(BankReconciliation $reconciliation, User $user): bool
    {
        try {
            DB::transaction(function () use ($reconciliation, $user) {
                $bankAccount = $reconciliation->bankAccount;

                $outstandingDeposits = $bankAccount->bankTransactions()
                    ->where('transaction_type', 'credit')
                    ->where('status', 'pending')
                    ->sum('amount');

                $outstandingWithdrawals = $bankAccount->bankTransactions()
                    ->where('transaction_type', 'debit')
                    ->where('status', 'pending')
                    ->sum('amount');

                $reconciledCount = $bankAccount->bankTransactions()
                    ->where('reconciliation_status', 'matched')
                    ->count();

                $reconciliation->update([
                    'outstanding_deposits' => $outstandingDeposits,
                    'outstanding_withdrawals' => $outstandingWithdrawals,
                    'transactions_reconciled' => $reconciledCount,
                    'difference' => $reconciliation->statement_balance - $reconciliation->book_balance,
                ]);

                $reconciliation->completeReconciliation($user);

                if ($reconciliation->bankStatement) {
                    $reconciliation->bankStatement->update(['status' => 'reconciled']);
                }

                Log::info("Bank reconciliation {$reconciliation->id} completed by user {$user->id}");
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to complete bank reconciliation {$reconciliation->id}: ".$e->getMessage());

            return false;
        }
    }

    public function calculateBookBalance(BankAccount $bankAccount): float
    {
        $bankAccountId = $bankAccount->chart_of_account_id;

        $debits = LedgerEntry::where('chart_of_account_id', $bankAccountId)
            ->where('type', 'debit')
            ->sum('amount');

        $credits = LedgerEntry::where('chart_of_account_id', $bankAccountId)
            ->where('type', 'credit')
            ->sum('amount');

        return $debits - $credits;
    }

    public function getReconciliationSummary(BankAccount $bankAccount): array
    {
        $totalTransactions = $bankAccount->bankTransactions()->count();
        $matchedTransactions = $bankAccount->bankTransactions()
            ->where('reconciliation_status', 'matched')
            ->count();
        $unmatchedTransactions = $bankAccount->bankTransactions()
            ->where('reconciliation_status', 'unmatched')
            ->count();

        $pendingDeposits = $bankAccount->bankTransactions()
            ->where('transaction_type', 'credit')
            ->where('status', 'pending')
            ->sum('amount');

        $pendingWithdrawals = $bankAccount->bankTransactions()
            ->where('transaction_type', 'debit')
            ->where('status', 'pending')
            ->sum('amount');

        $lastReconciliation = $bankAccount->bankReconciliations()
            ->where('status', 'completed')
            ->latest('reconciliation_date')
            ->first();

        return [
            'total_transactions' => $totalTransactions,
            'matched_transactions' => $matchedTransactions,
            'unmatched_transactions' => $unmatchedTransactions,
            'match_rate' => $totalTransactions > 0 ? ($matchedTransactions / $totalTransactions) * 100 : 0,
            'pending_deposits' => $pendingDeposits,
            'pending_withdrawals' => $pendingWithdrawals,
            'last_reconciliation_date' => $lastReconciliation?->reconciliation_date,
            'last_reconciliation_balance' => $lastReconciliation?->statement_balance,
        ];
    }

    public function getUnmatchedTransactions(BankAccount $bankAccount): Collection
    {
        return $bankAccount->bankTransactions()
            ->where('reconciliation_status', 'unmatched')
            ->with('bankStatement')
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function getPendingTransactions(BankAccount $bankAccount): Collection
    {
        return $bankAccount->bankTransactions()
            ->where('status', 'pending')
            ->with('bankStatement')
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function importBankStatement(BankAccount $bankAccount, array $statementData): BankStatement
    {
        try {
            return DB::transaction(function () use ($bankAccount, $statementData) {
                $statement = BankStatement::create([
                    'organization_id' => $bankAccount->organization_id,
                    'bank_account_id' => $bankAccount->id,
                    'statement_number' => $statementData['statement_number'],
                    'statement_date' => $statementData['statement_date'],
                    'period_start_date' => $statementData['period_start_date'],
                    'period_end_date' => $statementData['period_end_date'],
                    'opening_balance' => $statementData['opening_balance'],
                    'closing_balance' => $statementData['closing_balance'],
                    'total_debits' => $statementData['total_debits'] ?? 0,
                    'total_credits' => $statementData['total_credits'] ?? 0,
                    'transaction_count' => count($statementData['transactions'] ?? []),
                    'status' => 'imported',
                    'notes' => $statementData['notes'] ?? null,
                    'file_path' => $statementData['file_path'] ?? null,
                ]);

                foreach ($statementData['transactions'] ?? [] as $transactionData) {
                    BankTransaction::create([
                        'organization_id' => $bankAccount->organization_id,
                        'bank_account_id' => $bankAccount->id,
                        'bank_statement_id' => $statement->id,
                        'transaction_date' => $transactionData['transaction_date'],
                        'transaction_number' => $transactionData['transaction_number'] ?? null,
                        'reference_number' => $transactionData['reference_number'] ?? null,
                        'description' => $transactionData['description'],
                        'transaction_type' => $transactionData['transaction_type'],
                        'amount' => $transactionData['amount'],
                        'balance_after' => $transactionData['balance_after'] ?? null,
                        'status' => 'cleared',
                        'reconciliation_status' => 'unmatched',
                        'notes' => $transactionData['notes'] ?? null,
                        'metadata' => $transactionData['metadata'] ?? null,
                    ]);
                }

                $statement->calculateTotals();

                Log::info("Bank statement {$statement->id} imported for account {$bankAccount->id}");

                return $statement;
            });
        } catch (\Exception $e) {
            Log::error('Failed to import bank statement: '.$e->getMessage());
            throw $e;
        }
    }
}

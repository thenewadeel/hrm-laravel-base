<?php

namespace App\Models\Accounting;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\Accounting\BankTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'bank_account_id',
        'bank_statement_id',
        'transaction_date',
        'transaction_number',
        'reference_number',
        'description',
        'transaction_type',
        'amount',
        'balance_after',
        'status',
        'reconciliation_status',
        'matched_ledger_entry_id',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): BankTransactionFactory
    {
        return BankTransactionFactory::new();
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function bankStatement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class);
    }

    public function matchedLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(LedgerEntry::class);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUnmatched($query)
    {
        return $query->where('reconciliation_status', 'unmatched');
    }

    public function scopeMatched($query)
    {
        return $query->where('reconciliation_status', 'matched');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'cleared' => 'Cleared',
            'reconciled' => 'Reconciled',
            default => ucfirst($this->status),
        };
    }

    public function getReconciliationStatusLabelAttribute(): string
    {
        return match ($this->reconciliation_status) {
            'unmatched' => 'Unmatched',
            'matched' => 'Matched',
            'partially_matched' => 'Partially Matched',
            default => ucfirst($this->reconciliation_status),
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getTransactionTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'debit' => 'Debit',
            'credit' => 'Credit',
            default => ucfirst($this->transaction_type),
        };
    }

    public function matchWithLedgerEntry(LedgerEntry $ledgerEntry): void
    {
        $this->update([
            'matched_ledger_entry_id' => $ledgerEntry->id,
            'reconciliation_status' => 'matched',
            'status' => 'reconciled',
        ]);
    }

    public function unmatchFromLedgerEntry(): void
    {
        $this->update([
            'matched_ledger_entry_id' => null,
            'reconciliation_status' => 'unmatched',
            'status' => 'cleared',
        ]);
    }
}

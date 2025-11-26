<?php

namespace App\Models\Accounting;

use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Database\Factories\Accounting\BankReconciliationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankReconciliation extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'bank_account_id',
        'bank_statement_id',
        'reconciliation_date',
        'statement_balance',
        'book_balance',
        'difference',
        'outstanding_deposits',
        'outstanding_withdrawals',
        'transactions_reconciled',
        'total_transactions',
        'status',
        'notes',
        'reconciled_by',
        'reconciled_at',
    ];

    protected function casts(): array
    {
        return [
            'reconciliation_date' => 'date',
            'statement_balance' => 'decimal:2',
            'book_balance' => 'decimal:2',
            'difference' => 'decimal:2',
            'outstanding_deposits' => 'decimal:2',
            'outstanding_withdrawals' => 'decimal:2',
            'reconciled_at' => 'datetime',
        ];
    }

    protected static function newFactory(): BankReconciliationFactory
    {
        return BankReconciliationFactory::new();
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function bankStatement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('reconciliation_date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'failed' => 'Failed',
            default => ucfirst($this->status),
        };
    }

    public function getFormattedStatementBalanceAttribute(): string
    {
        return number_format($this->statement_balance, 2);
    }

    public function getFormattedBookBalanceAttribute(): string
    {
        return number_format($this->book_balance, 2);
    }

    public function getFormattedDifferenceAttribute(): string
    {
        return number_format($this->difference, 2);
    }

    public function isBalanced(): bool
    {
        return abs($this->difference) < 0.01;
    }

    public function completeReconciliation(User $user): void
    {
        $this->update([
            'status' => 'completed',
            'reconciled_by' => $user->id,
            'reconciled_at' => now(),
        ]);
    }

    public function calculateDifference(): void
    {
        $this->difference = $this->statement_balance - $this->book_balance;
        $this->save();
    }
}

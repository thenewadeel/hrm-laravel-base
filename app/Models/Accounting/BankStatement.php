<?php

namespace App\Models\Accounting;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\Accounting\BankStatementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankStatement extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'bank_account_id',
        'statement_number',
        'statement_date',
        'period_start_date',
        'period_end_date',
        'opening_balance',
        'closing_balance',
        'total_debits',
        'total_credits',
        'transaction_count',
        'status',
        'notes',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'statement_date' => 'date',
            'period_start_date' => 'date',
            'period_end_date' => 'date',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'total_debits' => 'decimal:2',
            'total_credits' => 'decimal:2',
        ];
    }

    protected static function newFactory(): BankStatementFactory
    {
        return BankStatementFactory::new();
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function bankTransactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function bankReconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('statement_date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'imported' => 'Imported',
            'reconciled' => 'Reconciled',
            'partial' => 'Partially Reconciled',
            default => ucfirst($this->status),
        };
    }

    public function getFormattedOpeningBalanceAttribute(): string
    {
        return number_format($this->opening_balance, 2);
    }

    public function getFormattedClosingBalanceAttribute(): string
    {
        return number_format($this->closing_balance, 2);
    }

    public function calculateTotals(): void
    {
        $this->transaction_count = $this->bankTransactions()->count();
        $this->total_debits = $this->bankTransactions()->where('transaction_type', 'debit')->sum('amount');
        $this->total_credits = $this->bankTransactions()->where('transaction_type', 'credit')->sum('amount');
        $this->save();
    }
}

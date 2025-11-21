<?php

namespace App\Models\Accounting;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\Accounting\BankAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'chart_of_account_id',
        'account_number',
        'account_name',
        'bank_name',
        'branch_name',
        'routing_number',
        'swift_code',
        'currency',
        'opening_balance',
        'current_balance',
        'opening_balance_date',
        'account_type',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'opening_balance_date' => 'date',
        ];
    }

    protected static function newFactory(): BankAccountFactory
    {
        return BankAccountFactory::new();
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function bankStatements(): HasMany
    {
        return $this->hasMany(BankStatement::class);
    }

    public function bankTransactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function bankReconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByBank($query, string $bankName)
    {
        return $query->where('bank_name', 'like', "%{$bankName}%");
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->current_balance, 2);
    }

    public function getAccountTypeLabelAttribute(): string
    {
        return match ($this->account_type) {
            'checking' => 'Checking Account',
            'savings' => 'Savings Account',
            'money_market' => 'Money Market Account',
            'cd' => 'Certificate of Deposit',
            default => ucfirst($this->account_type),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'closed' => 'Closed',
            default => ucfirst($this->status),
        };
    }

    public function updateBalance(): void
    {
        $this->current_balance = $this->bankTransactions()
            ->where('status', 'cleared')
            ->sum('amount') * ($this->account_type === 'asset' ? 1 : -1);
        $this->save();
    }
}

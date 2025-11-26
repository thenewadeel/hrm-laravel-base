<?php

namespace App\Models\Accounting;

use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Database\Factories\Accounting\OpeningBalanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningBalance extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'financial_year_id',
        'chart_of_account_id',
        'debit_amount',
        'credit_amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

    protected static function newFactory(): OpeningBalanceFactory
    {
        return OpeningBalanceFactory::new();
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getBalanceAttribute(): float
    {
        $account = $this->account;

        if (in_array($account->type, ['asset', 'expense'])) {
            return (float) $this->debit_amount - (float) $this->credit_amount;
        }

        return (float) $this->credit_amount - (float) $this->debit_amount;
    }

    public function getBalanceTypeAttribute(): string
    {
        return $this->debit_amount > $this->credit_amount ? 'debit' : 'credit';
    }
}

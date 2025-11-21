<?php

// app/Models/Accounting/ChartOfAccount.php

namespace App\Models\Accounting;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use Database\Factories\Accounting\ChartOfAccountFactory; // <-- Add this import
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = ['organization_id', 'code', 'name', 'type', 'description'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ChartOfAccountFactory
    {
        return ChartOfAccountFactory::new();
    }

    // public function organization(): BelongsTo
    // {
    //     return $this->belongsTo(Organization::class);
    // }
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'chart_of_account_id');
    }

    public function scopeAssets($query)
    {
        return $query->where('type', 'asset');
    }

    public function scopeLiabilities($query)
    {
        return $query->where('type', 'liability');
    }

    public function scopeEquity($query)
    {
        return $query->where('type', 'equity');
    }

    public function scopeRevenues($query)
    {
        return $query->where('type', 'revenue');
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    public function openingBalances(): HasMany
    {
        return $this->hasMany(OpeningBalance::class, 'chart_of_account_id');
    }

    public function getBalanceAttribute()
    {
        $debits = $this->ledgerEntries->where('type', 'debit')->sum('amount');
        $credits = $this->ledgerEntries->where('type', 'credit')->sum('amount');

        return in_array($this->type, ['asset', 'expense'])
            ? $debits - $credits
            : $credits - $debits;
    }
}

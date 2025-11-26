<?php

namespace App\Models\Accounting;

use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Database\Factories\Accounting\ClosingEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClosingEntry extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'financial_year_id',
        'journal_entry_id',
        'type',
        'amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected static function newFactory(): ClosingEntryFactory
    {
        return ClosingEntryFactory::new();
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeRevenueClosure($query)
    {
        return $query->where('type', 'revenue_closure');
    }

    public function scopeExpenseClosure($query)
    {
        return $query->where('type', 'expense_closure');
    }

    public function scopeProfitTransfer($query)
    {
        return $query->where('type', 'profit_transfer');
    }
}

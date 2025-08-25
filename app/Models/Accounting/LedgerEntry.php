<?php
// app/Models/Accounting/LedgerEntry.php

namespace App\Models\Accounting;

use App\Models\Dimension;
use Database\Factories\Accounting\LedgerEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class LedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_date',
        'chart_of_account_id',
        'type',
        'amount',
        'description',
        'transactionable_type',
        'transactionable_id'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function newFactory(): LedgerEntryFactory
    {
        return LedgerEntryFactory::new();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function dimensions(): MorphToMany
    {
        return $this->morphToMany(Dimension::class, 'dimensionable');
    }

    // REMOVE THE booted() METHOD - we're handling this at database level
}

<?php

namespace App\Models\Accounting;

use App\Models\JournalEntry;
use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Database\Factories\Accounting\DepreciationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depreciation extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'fixed_asset_id',
        'journal_entry_id',
        'depreciation_date',
        'depreciation_amount',
        'accumulated_depreciation_before',
        'accumulated_depreciation_after',
        'book_value_before',
        'book_value_after',
        'depreciation_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'depreciation_date' => 'date',
        'depreciation_amount' => 'decimal:2',
        'accumulated_depreciation_before' => 'decimal:2',
        'accumulated_depreciation_after' => 'decimal:2',
        'book_value_before' => 'decimal:2',
        'book_value_after' => 'decimal:2',
    ];

    protected static function newFactory(): DepreciationFactory
    {
        return DepreciationFactory::new();
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

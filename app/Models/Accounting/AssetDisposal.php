<?php

namespace App\Models\Accounting;

use App\Models\JournalEntry;
use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Database\Factories\Accounting\AssetDisposalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDisposal extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'fixed_asset_id',
        'journal_entry_id',
        'disposal_date',
        'disposal_type',
        'disposal_value',
        'proceeds',
        'gain_loss',
        'book_value_at_disposal',
        'accumulated_depreciation_at_disposal',
        'disposed_to',
        'reason',
        'notes',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'disposal_value' => 'decimal:2',
        'proceeds' => 'decimal:2',
        'gain_loss' => 'decimal:2',
        'book_value_at_disposal' => 'decimal:2',
        'accumulated_depreciation_at_disposal' => 'decimal:2',
    ];

    protected $attributes = [
        'disposal_value' => 0,
        'proceeds' => 0,
        'gain_loss' => 0,
    ];

    protected static function newFactory(): AssetDisposalFactory
    {
        return AssetDisposalFactory::new();
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::creating(function (AssetDisposal $disposal) {
            // Calculate gain/loss automatically
            $disposal->gain_loss = $disposal->proceeds - $disposal->book_value_at_disposal;
        });
    }
}

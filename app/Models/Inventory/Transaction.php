<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_transactions';

    // Transaction Types
    const TYPE_INCOMING = 'incoming';
    const TYPE_OUTGOING = 'outgoing';
    const TYPE_ADJUSTMENT = 'adjustment';

    // Transaction Statuses
    const STATUS_DRAFT = 'draft';
    const STATUS_FINALIZED = 'finalized';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'store_id',
        'created_by',
        'approved_by',
        'type',
        'status',
        'reference',
        'notes',
        'transaction_date',
        'finalized_at'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'finalized_at' => 'datetime'
    ];

    // Get available types
    public static function getTypes(): array
    {
        return [
            self::TYPE_INCOMING,
            self::TYPE_OUTGOING,
            self::TYPE_ADJUSTMENT,
        ];
    }

    // Get available statuses
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_FINALIZED,
            self::STATUS_CANCELLED,
        ];
    }

    // Validation rules for type and status
    public static function getValidationRules(): array
    {
        return [
            'type' => 'required|string|in:' . implode(',', self::getTypes()),
            'status' => 'sometimes|string|in:' . implode(',', self::getStatuses()),
        ];
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', self::STATUS_FINALIZED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeIncoming($query)
    {
        return $query->where('type', self::TYPE_INCOMING);
    }

    public function scopeOutgoing($query)
    {
        return $query->where('type', self::TYPE_OUTGOING);
    }

    // Business logic methods
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinalized(): bool
    {
        return $this->status === self::STATUS_FINALIZED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeModified(): bool
    {
        return $this->isDraft();
    }

    public function finalize(): bool
    {
        if ($this->isFinalized()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_FINALIZED,
            'finalized_at' => now(),
        ]);
    }

    public function cancel(): bool
    {
        if ($this->isFinalized()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getTotalValueAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }


    protected static function newFactory()
    {
        return \Database\Factories\Inventory\TransactionFactory::new();
    }
}

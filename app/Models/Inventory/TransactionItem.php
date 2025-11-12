<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;

class TransactionItem extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_transaction_items';

    protected $fillable = [
        'transaction_id',
        'item_id',
        'quantity',
        'unit_price',
        'notes'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2'
    ];

    protected $appends = ['total_price'];

    // Relationships
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Accessors
    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    // Business logic methods
    public function getFormattedUnitPrice(): string
    {
        return number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPrice(): string
    {
        return number_format($this->total_price, 2);
    }
    // ----------------------
    // Scopes
    // ----------------------
    /**
     * Scope for transaction items with high quantities (potential bulk transactions)
     */
    public function scopeHighQuantity(Builder $query, int $threshold = 100): Builder
    {
        return $query->where('quantity', '>', $threshold);
    }

    /**
     * Scope for transaction items with zero or negative quantities (adjustments)
     */
    public function scopeAdjustments(Builder $query): Builder
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Scope for transaction items with positive quantities (regular transactions)
     */
    public function scopePositiveQuantity(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope for expensive transaction items (above price threshold)
     */
    public function scopeExpensiveItems(Builder $query, int $priceThreshold = 5000): Builder
    {
        return $query->where('unit_price', '>', $priceThreshold);
    }

    protected static function newFactory()
    {
        return \Database\Factories\Inventory\TransactionItemFactory::new();
    }
}

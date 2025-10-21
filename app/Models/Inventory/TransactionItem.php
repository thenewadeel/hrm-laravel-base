<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return number_format($this->unit_price / 100, 2);
    }

    public function getFormattedTotalPrice(): string
    {
        return number_format($this->total_price / 100, 2);
    }

    protected static function newFactory()
    {
        return \Database\Factories\Inventory\TransactionItemFactory::new();
    }
}

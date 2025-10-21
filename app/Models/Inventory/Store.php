<?php

namespace App\Models\Inventory;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_stores';

    protected $fillable = [
        'organization_unit_id',
        'name',
        'code',
        'location',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected $appends = ['total_quantity', 'total_value'];

    // Relationships
    public function organization_unit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'inventory_store_items')
            ->withPivot('quantity', 'min_stock', 'max_stock')
            ->withTimestamps();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Accessors
    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items->sum('pivot.quantity');
    }

    public function getTotalValueAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->pivot->quantity * ($item->cost_price ?? 0);
        });
    }

    // Business logic methods
    public function getItemQuantity(Item $item): int
    {
        $storeItem = $this->items()->where('item_id', $item->id)->first();
        return $storeItem ? $storeItem->pivot->quantity : 0;
    }

    public function updateItemQuantity(Item $item, int $quantity): bool
    {
        return $this->items()->syncWithoutDetaching([
            $item->id => ['quantity' => max(0, $quantity)]
        ]);
    }

    public function adjustItemQuantity(Item $item, int $adjustment): bool
    {
        $currentQuantity = $this->getItemQuantity($item);
        $newQuantity = max(0, $currentQuantity + $adjustment);

        return $this->updateItemQuantity($item, $newQuantity);
    }

    public function hasSufficientStock(Item $item, int $quantity): bool
    {
        return $this->getItemQuantity($item) >= $quantity;
    }

    public function getLowStockItems()
    {
        return $this->items()
            ->wherePivot('quantity', '<=', \DB::raw('inventory_store_items.min_stock'))
            ->get();
    }

    public function getOutOfStockItems()
    {
        return $this->items()
            ->wherePivot('quantity', '<=', 0)
            ->get();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    protected static function newFactory()
    {
        return \Database\Factories\Inventory\StoreFactory::new();
    }
}

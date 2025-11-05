<?php

namespace App\Models\Inventory;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\Scopes\StoreOrganizationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

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

    // protected $appends = ['total_quantity', 'total_value'];

    protected static function booted()
    {
        static::addGlobalScope(new StoreOrganizationScope);
    }
    // Relationships
    public function organization_unit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }
    public function organization(): HasOneThrough
    {
        return $this->hasOneThrough(Organization::class, OrganizationUnit::class, 'id', 'id', 'organization_unit_id', 'organization_id');
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

    /**
     * Get low stock items for this specific store (but not out of stock)
     */
    public function lowStockItems()
    {
        return $this->belongsToMany(Item::class, 'inventory_store_items')
            ->whereRaw('inventory_store_items.quantity < inventory_store_items.min_stock')
            ->where('inventory_store_items.quantity', '>', 0) // Exclude out of stock
            ->withPivot(['quantity', 'min_stock', 'max_stock']);
    }

    /**
     * Get out of stock items for this specific store
     */
    public function outOfStockItems()
    {
        return $this->belongsToMany(Item::class, 'inventory_store_items')
            ->where('inventory_store_items.quantity', '<=', 0)
            ->withPivot(['quantity', 'min_stock', 'max_stock']);
    }

    /**
     * Get items that are adequately stocked
     */
    public function adequateStockItems()
    {
        return $this->belongsToMany(Item::class, 'inventory_store_items')
            ->whereRaw('inventory_store_items.quantity >= inventory_store_items.min_stock')
            ->where('inventory_store_items.quantity', '>', 0) // Also not out of stock
            ->withPivot(['quantity', 'min_stock', 'max_stock']);
    }



    /**
     * Get current stock level statistics - FIXED VERSION
     */
    public function getStockStatsAttribute()
    {
        $totalItems = $this->items()->count();
        $lowStockItems = $this->lowStockItems()->count();
        $outOfStockItems = $this->outOfStockItems()->count();
        $adequateStockItems = $this->adequateStockItems()->count();

        // Verify the math makes sense
        $calculatedTotal = $lowStockItems + $outOfStockItems + $adequateStockItems;

        return [
            'total_items' => $totalItems,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'adequate_stock_items' => $adequateStockItems,
            'calculated_total' => $calculatedTotal, // For debugging
        ];
    }
    // Scopes
    public function scopeActive($query, bool $active = true)
    {
        return $query->where('is_active', $active);
    }
    /**
     * Scope to filter stores by organization ID
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->whereHas('organization_unit', function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        });
    }
    /**
     * Scope to filter stores by organization unit ID
     */
    public function scopeForOrganizationUnit($query, $organizationUnitId)
    {
        return $query->where('organization_unit_id', $organizationUnitId);
    }

    /**
     * Scope to filter stores by user's organizations
     */
    public function scopeForUser($query,  $user)
    {
        $organizationIds = $user->organizations->pluck('id');

        return $query->whereHas('organization_unit', function ($q) use ($organizationIds) {
            $q->whereIn('organization_id', $organizationIds);
        });
    }

    /**
     * Scope to search stores by name, code, or location
     */
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        });
    }
    /**
     * Scope for stores that have low stock items
     */
    public function scopeHasLowStock(Builder $query): Builder
    {
        return $query->whereHas('items', function ($q) {
            $q->whereRaw('inventory_store_items.quantity < inventory_store_items.min_stock')
                ->where('inventory_store_items.quantity', '>', 0);
        });
    }

    protected static function newFactory()
    {
        return \Database\Factories\Inventory\StoreFactory::new();
    }
    /**
     * Alternative: More reliable statistics using database queries
     */
    public function getStockStatsReliableAttribute()
    {
        $totalItems = $this->items()->count();

        // Use raw queries to avoid relationship overlap issues
        $lowStockCount = $this->items()
            ->whereRaw('inventory_store_items.quantity < inventory_store_items.min_stock')
            ->where('inventory_store_items.quantity', '>', 0)
            ->count();

        $outOfStockCount = $this->items()
            ->where('inventory_store_items.quantity', '<=', 0)
            ->count();

        $adequateStockCount = $this->items()
            ->whereRaw('inventory_store_items.quantity >= inventory_store_items.min_stock')
            ->where('inventory_store_items.quantity', '>', 0)
            ->count();

        return [
            'total_items' => $totalItems,
            'low_stock_items' => $lowStockCount,
            'out_of_stock_items' => $outOfStockCount,
            'adequate_stock_items' => $adequateStockCount,
            'verification_total' => $lowStockCount + $outOfStockCount + $adequateStockCount,
        ];
    }
}

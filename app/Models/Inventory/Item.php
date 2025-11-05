<?php

namespace App\Models\Inventory;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Inventory\Store; // Assuming Store model location
use App\Models\Inventory\Head;   // Assuming Head model location
use App\Models\Inventory\TransactionItem; // Assuming TransactionItem model location
use App\Models\Traits\BelongsToOrganization;

class Item extends Model
{
    use HasFactory, BelongsToOrganization;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_items';

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'unit',
        'cost_price',
        'selling_price',
        'reorder_level',
        'is_active',
        'head_id',
        'organization_id'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // ------------------------------------------------------------------------------------------------
    // APPENDED ATTRIBUTES
    // ------------------------------------------------------------------------------------------------

    // Add 'total_quantity' to be automatically included in array/JSON form
    // protected $appends = ['total_quantity', 'formatted_selling_price'];

    // ------------------------------------------------------------------------------------------------
    // RELATIONS
    // ------------------------------------------------------------------------------------------------

    // public function organization(): BelongsTo
    // {
    //     return $this->belongsTo(Organization::class);
    // }
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'inventory_store_items')
            ->withPivot('quantity', 'min_stock', 'max_stock')
            ->withTimestamps();
    }
    public function head(): BelongsTo
    {
        return $this->belongsTo(Head::class);
    }
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    // ------------------------------------------------------------------------------------------------
    // ACCESSORS (Getters)
    // ------------------------------------------------------------------------------------------------

    /**
     * Accessor for the total quantity across all stores.
     * (Existing one, kept for context, note the modern naming convention: get{Attribute}Attribute)
     *
     * @return int
     */
    public function getTotalQuantityAttribute(): int
    {
        // Ensure you load the stores relation before calling this or use $item->load('stores')->total_quantity
        // It's safer to use a sum query if 'stores' isn't always loaded:
        // return $this->stores()->sum('inventory_store_items.quantity');
        return (int) $this->stores->sum('pivot.quantity');
    }

    /**
     * Accessor to get the selling price formatted as currency.
     *
     * @return string
     */
    public function getFormattedSellingPriceAttribute(): string
    {
        // Assuming US dollar formatting, adjust as needed (e.g., using a localization package)
        return number_format($this->selling_price, 2) . ' PKR';
    }
    public function getFormattedCostPriceAttribute(): string
    {
        // Assuming US dollar formatting, adjust as needed (e.g., using a localization package)
        return number_format($this->cost_price, 2) . ' PKR';
    }

    public function getOverallCostAttribute(): string
    {
        // Assuming US dollar formatting, adjust as needed (e.g., using a localization package)
        return number_format($this->cost_price * $this->total_quantity, 2) . ' PKR';
    }

    // ------------------------------------------------------------------------------------------------
    // MUTATORS (Setters)
    // ------------------------------------------------------------------------------------------------

    /**
     * Mutator to ensure the SKU is always stored in uppercase.
     *
     * @param string $value
     * @return void
     */
    public function setSkuAttribute(string $value): void
    {
        $this->attributes['sku'] = strtoupper($value);
    }

    // ------------------------------------------------------------------------------------------------
    // LOCAL SCOPES
    // ------------------------------------------------------------------------------------------------

    /**
     * Scope a query to only include active items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    public function scopeInActive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to include items that need reordering (quantity is below reorder_level).
     * This requires a JOIN to the pivot table and aggregation, making it a bit complex for a simple scope.
     * A simpler version scopes based on the item's `reorder_level` column.
     *
     * For a simple check based on the model's reorder_level being set:
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNeedsAttention(Builder $query): Builder
    {
        // This targets items where the 'reorder_level' field is set to a positive number.
        return $query->where('reorder_level', '>', 0);

        /*
        // A more complex, accurate scope requires checking total stock against the reorder_level:
        return $query->select('inventory_items.*')
            ->leftJoin('inventory_store_items', 'inventory_items.id', '=', 'inventory_store_items.item_id')
            ->groupBy('inventory_items.id')
            ->havingRaw('COALESCE(SUM(inventory_store_items.quantity), 0) < inventory_items.reorder_level');
        */
    }

    /**
     * Scope a query to filter by a specific category.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeOfHead(Builder $query, string $head): Builder
    {
        return $query->where('head_id', $head);
    }

    // public function scopeOfOrganization(Builder $query, string $organization): Builder
    // {
    //     return $query->where('organization_id', $organization);
    // }
    // ------------------------------------------------------------------------------------------------
    /**
     * Scope for items that are low in stock across all stores (but not out of stock)
     */
    public function scopeLowInStock(Builder $query): Builder
    {
        return $query->whereHas('stores', function ($q) {
            $q->whereRaw('inventory_store_items.quantity < inventory_store_items.min_stock')
                ->where('inventory_store_items.quantity', '>', 0); // Exclude out of stock
        });
    }

    /**
     * Scope for items that are out of stock
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereHas('stores', function ($q) {
            $q->where('inventory_store_items.quantity', '<=', 0);
        });
    }

    /**
     * Scope for items that are adequately stocked
     */
    public function scopeAdequatelyStocked(Builder $query): Builder
    {
        return $query->whereHas('stores', function ($q) {
            $q->whereRaw('inventory_store_items.quantity >= inventory_store_items.min_stock')
                ->where('inventory_store_items.quantity', '>', 0); // Also not out of stock
        });
    }

    /**
     * Scope for items that are low in stock in a specific store
     */
    public function scopeLowInStockInStore(Builder $query, $storeId): Builder
    {
        return $query->whereHas('stores', function ($q) use ($storeId) {
            $q->where('inventory_stores.id', $storeId)
                ->whereRaw('inventory_store_items.quantity < inventory_store_items.min_stock');
        });
    }


    /**
     * Alternative approach using join for low stock items
     */
    public function scopeLowStockJoin(Builder $query): Builder
    {
        return $query->join('inventory_store_items', 'inventory_items.id', '=', 'inventory_store_items.item_id')
            ->whereRaw('inventory_store_items.quantity < inventory_store_items.min_stock')
            ->select('inventory_items.*')
            ->distinct();
    }
    // FACTORY
    // ------------------------------------------------------------------------------------------------

    protected static function newFactory()
    {
        return \Database\Factories\Inventory\ItemFactory::new();
    }
}

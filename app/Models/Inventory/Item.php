<?php

namespace App\Models\Inventory;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function getTotalQuantityAttribute()
    {
        return $this->stores->sum('pivot.quantity');
    }

    protected static function newFactory()
    {
        return \Database\Factories\Inventory\ItemFactory::new();
    }
}

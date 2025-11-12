<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Head extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_heads';
    protected $fillable = [
        'name',
        'description',
        'image',
        // 'sore_id'
    ];

    protected $casts = [];

    // public function store(): BelongsTo
    // {
    //     return $this->belongsTo(Store::class);
    // }
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
    public function store_items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'inventory_store_items')
            ->withPivot('quantity', 'min_stock', 'max_stock')
            ->withTimestamps();
    }
}

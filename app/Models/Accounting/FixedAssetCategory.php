<?php

namespace App\Models\Accounting;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\Accounting\FixedAssetCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAssetCategory extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'description',
        'default_useful_life_years',
        'default_depreciation_method',
        'default_depreciation_rate',
        'is_active',
    ];

    protected $casts = [
        'default_useful_life_years' => 'integer',
        'default_depreciation_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'default_useful_life_years' => 5,
        'default_depreciation_method' => 'straight_line',
        'is_active' => true,
    ];

    protected static function newFactory(): FixedAssetCategoryFactory
    {
        return FixedAssetCategoryFactory::new();
    }

    public function fixedAssets(): HasMany
    {
        return $this->hasMany(FixedAsset::class, 'fixed_asset_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}

<?php

namespace App\Models\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Database\Factories\Accounting\FixedAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'fixed_asset_category_id',
        'chart_of_account_id',
        'accumulated_depreciation_account_id',
        'asset_tag',
        'name',
        'description',
        'serial_number',
        'location',
        'department',
        'assigned_to',
        'purchase_date',
        'purchase_cost',
        'salvage_value',
        'useful_life_years',
        'depreciation_method',
        'current_book_value',
        'accumulated_depreciation',
        'last_depreciation_date',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_depreciation_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'current_book_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'active',
        'depreciation_method' => 'straight_line',
        'accumulated_depreciation' => 0,
        'salvage_value' => 0,
    ];

    protected static function newFactory(): FixedAssetFactory
    {
        return FixedAssetFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FixedAssetCategory::class, 'fixed_asset_category_id');
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function accumulatedDepreciationAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'accumulated_depreciation_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(Depreciation::class);
    }

    public function disposals(): HasMany
    {
        return $this->hasMany(AssetDisposal::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeDisposed($query)
    {
        return $query->where('status', 'disposed');
    }

    public function calculateAnnualDepreciation(): float
    {
        return match ($this->depreciation_method) {
            'straight_line' => $this->calculateStraightLineDepreciation(),
            'declining_balance' => $this->calculateDecliningBalanceDepreciation(),
            'sum_of_years' => $this->calculateSumOfYearsDepreciation(),
            default => $this->calculateStraightLineDepreciation(),
        };
    }

    public function calculateStraightLineDepreciation(): float
    {
        $depreciableAmount = $this->purchase_cost - $this->salvage_value;

        return $depreciableAmount / $this->useful_life_years;
    }

    public function calculateDecliningBalanceDepreciation(): float
    {
        $rate = $this->category?->default_depreciation_rate ?? 20; // Default 20%
        $bookValue = $this->current_book_value;
        $depreciation = $bookValue * ($rate / 100);

        // Ensure we don't depreciate below salvage value
        $maxDepreciation = $bookValue - $this->salvage_value;

        return min($depreciation, $maxDepreciation);
    }

    public function calculateSumOfYearsDepreciation(): float
    {
        $years = range(1, $this->useful_life_years);
        $sumOfYears = array_sum($years);

        $currentYear = $this->getCurrentDepreciationYear();
        if ($currentYear > $this->useful_life_years) {
            return 0;
        }

        $depreciableAmount = $this->purchase_cost - $this->salvage_value;
        $remainingYears = $this->useful_life_years - $currentYear + 1;

        return ($depreciableAmount * $remainingYears) / $sumOfYears;
    }

    public function getCurrentDepreciationYear(): int
    {
        if (! $this->last_depreciation_date) {
            return 1;
        }

        $years = $this->purchase_date->diffInYears($this->last_depreciation_date) + 1;

        return min($years, $this->useful_life_years);
    }

    public function isFullyDepreciated(): bool
    {
        return $this->current_book_value <= $this->salvage_value;
    }

    public function updateBookValue(): void
    {
        $this->current_book_value = $this->purchase_cost - $this->accumulated_depreciation;
        $this->save();
    }

    public function canBeDepreciated(): bool
    {
        return $this->status === 'active'
            && ! $this->isFullyDepreciated()
            && $this->purchase_cost > 0;
    }
}

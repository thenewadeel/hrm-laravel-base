<?php

namespace App\Models\Accounting;

use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Database\Factories\Accounting\AssetMaintenanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'fixed_asset_id',
        'maintenance_date',
        'maintenance_type',
        'description',
        'cost',
        'performed_by',
        'vendor',
        'notes',
        'next_maintenance_date',
        'created_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    protected $attributes = [
        'cost' => 0,
    ];

    protected static function newFactory(): AssetMaintenanceFactory
    {
        return AssetMaintenanceFactory::new();
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

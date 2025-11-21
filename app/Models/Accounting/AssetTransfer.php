<?php

namespace App\Models\Accounting;

use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Database\Factories\Accounting\AssetTransferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransfer extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'fixed_asset_id',
        'transfer_date',
        'from_location',
        'to_location',
        'from_department',
        'to_department',
        'from_assigned_to',
        'to_assigned_to',
        'reason',
        'notes',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    protected static function newFactory(): AssetTransferFactory
    {
        return AssetTransferFactory::new();
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::created(function (AssetTransfer $transfer) {
            // Update the asset's current location and assignment
            $asset = $transfer->fixedAsset;
            $asset->update([
                'location' => $transfer->to_location,
                'department' => $transfer->to_department,
                'assigned_to' => $transfer->to_assigned_to,
            ]);
        });
    }
}

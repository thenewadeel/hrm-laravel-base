<?php

namespace App\Models\Accounting;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxExemption extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'exemptible_type',
        'exemptible_id',
        'tax_rate_id',
        'certificate_number',
        'exemption_type',
        'exemption_percentage',
        'issue_date',
        'expiry_date',
        'is_active',
        'reason',
        'applicable_taxes',
        'issuing_authority',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'exemption_percentage' => 'decimal:2',
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
            'applicable_taxes' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function exemptible(): MorphTo
    {
        return $this->morphTo();
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            });
    }

    public function scopeForEntity($query, $entity)
    {
        return $query->where('exemptible_type', get_class($entity))
            ->where('exemptible_id', $entity->id);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('exemption_type', $type);
    }

    public function isValid(): bool
    {
        return $this->is_active &&
               (! $this->expiry_date || $this->expiry_date >= now());
    }

    public function appliesToTax(string $taxType): bool
    {
        if (empty($this->applicable_taxes)) {
            return true; // Applies to all taxes if not specified
        }

        return in_array($taxType, $this->applicable_taxes);
    }

    public function getExemptionTypeDisplayName(): string
    {
        return match ($this->exemption_type) {
            'resale' => 'Resale Certificate',
            'charitable' => 'Charitable Organization',
            'government' => 'Government Entity',
            'manufacturing' => 'Manufacturing Exemption',
            'educational' => 'Educational Institution',
            'religious' => 'Religious Organization',
            'export' => 'Export Exemption',
            'agricultural' => 'Agricultural Exemption',
            'research' => 'Research & Development',
            'other' => 'Other Exemption',
            default => ucfirst($this->exemption_type),
        };
    }
}

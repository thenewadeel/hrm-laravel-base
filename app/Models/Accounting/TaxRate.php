<?php

namespace App\Models\Accounting;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRate extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'tax_jurisdiction_id',
        'name',
        'code',
        'type',
        'rate',
        'is_compound',
        'is_active',
        'effective_date',
        'end_date',
        'description',
        'applicable_accounts',
        'gl_account_code',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
            'effective_date' => 'date',
            'end_date' => 'date',
            'applicable_accounts' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function jurisdiction(): BelongsTo
    {
        return $this->belongsTo(TaxJurisdiction::class, 'tax_jurisdiction_id');
    }

    public function calculations(): HasMany
    {
        return $this->hasMany(TaxCalculation::class);
    }

    public function filings(): HasMany
    {
        return $this->hasMany(TaxFiling::class);
    }

    public function getTypeDisplayName(): string
    {
        return match ($this->type) {
            'sales' => 'Sales Tax',
            'purchase' => 'Purchase Tax',
            'withholding' => 'Withholding Tax',
            'income' => 'Income Tax',
            'vat' => 'VAT/GST',
            'service' => 'Service Tax',
            'other' => 'Other Tax',
            default => ucfirst($this->type),
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? now();

        return $query->where('effective_date', '<=', $date);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isEffectiveOn(string $date): bool
    {
        return $this->effective_date <= $date &&
               (! $this->end_date || $this->end_date >= $date);
    }

    public function calculateTax(float $baseAmount, float $exemptionPercentage = 0): float
    {
        $taxableAmount = $baseAmount * (1 - ($exemptionPercentage / 100));

        return round($taxableAmount * ($this->rate / 100), 2);
    }
}

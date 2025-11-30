<?php

namespace App\Models\Accounting;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TaxCalculation extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'calculable_type',
        'calculable_id',
        'tax_rate_id',
        'tax_exemption_id',
        'base_amount',
        'taxable_amount',
        'tax_rate',
        'tax_amount',
        'calculation_date',
        'calculation_method',
        'calculation_details',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'float',
            'taxable_amount' => 'float',
            'tax_rate' => 'float',
            'tax_amount' => 'float',
            'calculation_date' => 'date',
            'calculation_details' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function calculable(): MorphTo
    {
        return $this->morphTo();
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function exemption(): BelongsTo
    {
        return $this->belongsTo(TaxExemption::class, 'tax_exemption_id');
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('calculation_date', [$startDate, $endDate]);
    }

    public function scopeByTaxType($query, string $type)
    {
        return $query->whereHas('taxRate', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    public function getEffectiveRate(): float
    {
        if ($this->base_amount > 0) {
            return ($this->tax_amount / $this->base_amount) * 100;
        }

        return 0;
    }

    public function getExemptionAmount(): float
    {
        return $this->base_amount - $this->taxable_amount;
    }
}

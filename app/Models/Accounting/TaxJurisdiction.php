<?php

namespace App\Models\Accounting;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxJurisdiction extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'type',
        'parent_code',
        'tax_id_number',
        'is_active',
        'address',
        'contact_email',
        'contact_phone',
        'filing_requirements',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'filing_requirements' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }

    public function filings(): HasMany
    {
        return $this->hasMany(TaxFiling::class);
    }

    public function getTypeDisplayName(): string
    {
        return match ($this->type) {
            'country' => 'Country',
            'state' => 'State/Province',
            'province' => 'Province',
            'city' => 'City',
            'county' => 'County',
            'municipality' => 'Municipality',
            'other' => 'Other',
            default => ucfirst($this->type),
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getFilingFrequency(): ?string
    {
        return $this->filing_requirements['frequency'] ?? null;
    }

    public function getDueDays(): int
    {
        return $this->filing_requirements['due_days'] ?? 30;
    }
}

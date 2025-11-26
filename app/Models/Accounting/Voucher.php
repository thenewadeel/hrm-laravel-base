<?php

namespace App\Models\Accounting;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'type',
        'number',
        'date',
        'amount',
        'description',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'float',
            'status' => 'string',
        ];
    }

    /**
     * Get the organization that owns the voucher.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who created the voucher.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the voucher.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if voucher is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if voucher is posted.
     */
    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    /**
     * Get voucher type display name.
     */
    public function getTypeDisplayName(): string
    {
        return match ($this->type) {
            'sales' => 'Sales',
            'sales_return' => 'Sales Return',
            'purchase' => 'Purchase',
            'purchase_return' => 'Purchase Return',
            'salary' => 'Salary',
            'expense' => 'Expense',
            'fixed_asset' => 'Fixed Asset',
            'depreciation' => 'Depreciation',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get tax calculations for this voucher.
     */
    public function taxCalculations(): MorphMany
    {
        return $this->morphMany(TaxCalculation::class, 'calculable');
    }

    /**
     * Get total tax amount for this voucher.
     */
    public function getTotalTaxAttribute(): float
    {
        return $this->taxCalculations->sum('tax_amount');
    }

    /**
     * Get total amount including tax.
     */
    public function getTotalWithTaxAttribute(): float
    {
        return $this->amount + $this->total_tax;
    }

    /**
     * Calculate and save taxes for this voucher.
     */
    public function calculateTaxes(): void
    {
        if ($this->isPosted()) {
            return; // Don't recalculate posted vouchers
        }

        $taxService = app(\App\Services\TaxCalculationService::class);
        $taxService->calculateTaxes($this, $this->amount, $this->type);
    }

    /**
     * Recalculate taxes for this voucher.
     */
    public function recalculateTaxes(): void
    {
        if ($this->isPosted()) {
            throw new \Exception('Cannot recalculate taxes for posted vouchers');
        }

        $taxService = app(\App\Services\TaxCalculationService::class);
        $taxService->recalculateTaxes($this);
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayName(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'posted' => 'Posted',
            default => ucfirst($this->status),
        };
    }

    /**
     * Scope to get vouchers by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get drafted vouchers.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get posted vouchers.
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Generate sequential voucher number.
     */
    public static function generateNumber(string $type, int $organizationId): string
    {
        $year = now()->year;
        $prefix = strtoupper(str_replace('_', '-', $type));

        $lastNumber = static::where('organization_id', $organizationId)
            ->where('type', $type)
            ->whereYear('date', $year)
            ->orderBy('number', 'desc')
            ->value('number');

        if ($lastNumber) {
            $sequence = (int) substr($lastNumber, -4) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%d-%04d', $prefix, $year, $sequence);
    }
}

<?php

namespace App\Models\Accounting;

use Database\Factories\Accounting\FeeDistributionRuleItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeDistributionRuleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_distribution_rule_id',
        'chart_of_account_id',
        'distribution_type',
        'percentage',
        'fixed_amount',
        'priority',
        'description',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'priority' => 'integer',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): FeeDistributionRuleItemFactory
    {
        return FeeDistributionRuleItemFactory::new();
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(FeeDistributionRule::class, 'fee_distribution_rule_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    /**
     * Get the organization through the rule relationship
     */
    public function getOrganizationIdAttribute()
    {
        return $this->rule?->organization_id;
    }

    /**
     * Get the calculated amount for a given base amount
     */
    public function calculateAmount(float $baseAmount): float
    {
        if ($this->distribution_type === 'percentage') {
            return ($baseAmount * $this->percentage) / 100;
        }

        return $this->fixed_amount;
    }

    /**
     * Validate the distribution item
     */
    public function validate(): bool
    {
        if ($this->distribution_type === 'percentage') {
            return $this->percentage > 0 && $this->percentage <= 100;
        }

        return $this->fixed_amount > 0;
    }
}

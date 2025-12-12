<?php

namespace App\Models\Accounting;

use App\Models\Membership\MemberFee;
use App\Models\Traits\BelongsToOrganization;
use Database\Factories\Accounting\FeeDistributionRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeDistributionRule extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'fee_type',
        'rule_type',
        'conditions',
        'is_active',
        'priority',
        'description',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): FeeDistributionRuleFactory
    {
        return FeeDistributionRuleFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeDistributionRuleItem::class)
            ->orderBy('priority');
    }

    public function distributionLogs(): HasMany
    {
        return $this->hasMany(FeeDistributionLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByFeeType($query, string $feeType)
    {
        return $query->where('fee_type', $feeType);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority');
    }

    /**
     * Check if this rule applies to the given fee and conditions
     */
    public function appliesTo(MemberFee $fee): bool
    {
        if (! $this->is_active || $this->fee_type !== $fee->fee_type) {
            return false;
        }

        // Check additional conditions if they exist
        if ($this->conditions) {
            return $this->evaluateConditions($fee);
        }

        return true;
    }

    /**
     * Evaluate conditional rules for the fee
     */
    private function evaluateConditions(MemberFee $fee): bool
    {
        $conditions = $this->conditions;

        // Check amount range conditions
        if (isset($conditions['min_amount']) && $fee->amount < $conditions['min_amount']) {
            return false;
        }

        if (isset($conditions['max_amount']) && $fee->amount > $conditions['max_amount']) {
            return false;
        }

        // Check member category conditions
        if (isset($conditions['member_categories']) && $fee->member) {
            $memberCategory = $fee->member->category ?? 'default';
            if (! in_array($memberCategory, $conditions['member_categories'])) {
                return false;
            }
        }

        // Check date range conditions
        if (isset($conditions['date_from']) && $fee->created_at->lt($conditions['date_from'])) {
            return false;
        }

        if (isset($conditions['date_to']) && $fee->created_at->gt($conditions['date_to'])) {
            return false;
        }

        return true;
    }

    /**
     * Calculate distribution for a given amount
     */
    public function calculateDistribution(float $amount): array
    {
        $distribution = [];
        $totalPercentage = 0;
        $totalFixed = 0;

        foreach ($this->items as $item) {
            if ($item->distribution_type === 'percentage') {
                $distribution[$item->id] = [
                    'account_id' => $item->chart_of_account_id,
                    'type' => 'percentage',
                    'value' => $item->percentage,
                    'amount' => round(($amount * $item->percentage) / 100, 2),
                ];
                $totalPercentage += $item->percentage;
            } else {
                $distribution[$item->id] = [
                    'account_id' => $item->chart_of_account_id,
                    'type' => 'fixed',
                    'value' => $item->fixed_amount,
                    'amount' => round((float) $item->fixed_amount, 2),
                ];
                $totalFixed += round((float) $item->fixed_amount, 2);
            }
        }

        // Validate that percentages don't exceed 100%
        if ($totalPercentage > 100) {
            throw new \InvalidArgumentException('Total percentage distribution cannot exceed 100%');
        }

        // For percentage-based rules, ensure full distribution
        if ($this->rule_type === 'percentage' && $totalPercentage < 100) {
            $remainingPercentage = 100 - $totalPercentage;
            // Find first percentage-based item to add remaining percentage
            foreach ($distribution as $key => $item) {
                if ($item['type'] === 'percentage') {
                    $distribution[$key]['value'] += $remainingPercentage;
                    $distribution[$key]['amount'] = round($item['amount'] + ($amount * $remainingPercentage) / 100, 2);
                    break;
                }
            }
        }

        // For priority rules, ensure full distribution by adding remaining to first item
        if ($this->rule_type === 'priority') {
            $totalDistributed = array_sum(array_column($distribution, 'amount'));
            if ($totalDistributed < $amount) {
                $remainingAmount = $amount - $totalDistributed;
                // Add remaining amount to first distribution item
                $firstKey = array_key_first($distribution);
                if ($firstKey !== null) {
                    $distribution[$firstKey]['amount'] = round($distribution[$firstKey]['amount'] + $remainingAmount, 2);
                }
            }
        }

        return $distribution;
    }

    /**
     * Get total distributed amount for a given fee amount
     */
    public function getTotalDistributedAmount(float $amount): float
    {
        $distribution = $this->calculateDistribution($amount);

        return array_sum(array_column($distribution, 'amount'));
    }
}

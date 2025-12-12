<?php

namespace App\Models\Accounting;

use App\Models\Membership\MemberFee;
use App\Models\Traits\BelongsToOrganization;
use Database\Factories\Accounting\FeeDistributionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeDistributionLog extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'member_fee_id',
        'fee_distribution_rule_id',
        'journal_entry_id',
        'total_amount',
        'distribution_breakdown',
        'status',
        'error_message',
        'distributed_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'distribution_breakdown' => 'array',
        'distributed_at' => 'datetime',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): FeeDistributionLogFactory
    {
        return FeeDistributionLogFactory::new();
    }

    public function memberFee(): BelongsTo
    {
        return $this->belongsTo(MemberFee::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(FeeDistributionRule::class, 'fee_distribution_rule_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Scope for successful distributions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed distributions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for distributions within a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('distributed_at', [$startDate, $endDate]);
    }

    /**
     * Get the total distributed amount from the breakdown
     */
    public function getActualDistributedAmountAttribute(): float
    {
        if (! $this->distribution_breakdown) {
            return 0;
        }

        // Handle both array and JSON string cases
        $breakdown = is_string($this->distribution_breakdown)
            ? json_decode($this->distribution_breakdown, true)
            : $this->distribution_breakdown;

        if (! is_array($breakdown)) {
            return 0;
        }

        $total = 0;
        foreach ($breakdown as $item) {
            if (is_array($item) && isset($item['amount'])) {
                $total += (float) $item['amount'];
            }
        }

        return $total;
    }

    /**
     * Check if the distribution was fully successful
     */
    public function getIsFullyDistributedAttribute(): bool
    {
        return $this->status === 'success' &&
               abs($this->total_amount - $this->actual_distributed_amount) < 0.01;
    }
}

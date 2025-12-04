<?php

namespace App\Models\Membership;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberFee extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'member_id',
        'fee_type',
        'description',
        'amount',
        'paid_amount',
        'due_date',
        'paid_date',
        'status',
        'payment_method',
        'payment_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_date' => 'date',
            'status' => 'string',
            'fee_type' => 'string',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (! $this->is_overdue) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    public function markAsPaid(array $paymentData): bool
    {
        $this->status = 'paid';
        $this->paid_date = now();
        $this->paid_amount = $paymentData['amount'] ?? $this->amount;
        $this->payment_method = $paymentData['payment_method'] ?? null;
        $this->payment_reference = $paymentData['payment_reference'] ?? null;

        return $this->save();
    }

    public function markAsWaived(): bool
    {
        $this->status = 'waived';

        return $this->save();
    }

    public function markAsOverdue(): bool
    {
        if ($this->status === 'pending' && $this->due_date->isPast()) {
            $this->status = 'overdue';

            return $this->save();
        }

        return false;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                    ->where('due_date', '<', now());
            });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('fee_type', $type);
    }

    public function scopeByMember($query, int $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeDueBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    public function scopePaidBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_date', [$startDate, $endDate]);
    }
}

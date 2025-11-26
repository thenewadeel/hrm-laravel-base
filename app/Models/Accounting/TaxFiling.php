<?php

namespace App\Models\Accounting;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxFiling extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'tax_jurisdiction_id',
        'tax_rate_id',
        'filing_number',
        'filing_type',
        'period_start',
        'period_end',
        'filing_date',
        'due_date',
        'status',
        'total_tax_collected',
        'total_tax_paid',
        'tax_due',
        'penalty_amount',
        'interest_amount',
        'confirmation_number',
        'filing_notes',
        'filing_data',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'filing_date' => 'date',
            'due_date' => 'date',
            'total_tax_collected' => 'decimal:2',
            'total_tax_paid' => 'decimal:2',
            'tax_due' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'interest_amount' => 'decimal:2',
            'filing_data' => 'array',
            'approved_at' => 'datetime',
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

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusDisplayName(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'filed' => 'Filed',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            default => ucfirst($this->status),
        };
    }

    public function getFilingTypeDisplayName(): string
    {
        return match ($this->filing_type) {
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'annual' => 'Annual',
            'special' => 'Special',
            default => ucfirst($this->filing_type),
        };
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['paid', 'accepted']);
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
            ->where('due_date', '>', now())
            ->whereNotIn('status', ['paid', 'accepted']);
    }

    public function isOverdue(): bool
    {
        return $this->due_date < now() && ! in_array($this->status, ['paid', 'accepted']);
    }

    public function getTotalDue(): float
    {
        return $this->tax_due + $this->penalty_amount + $this->interest_amount;
    }

    public function approve(User $user): void
    {
        $this->update([
            'status' => 'accepted',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }
}

<?php

namespace App\Models\Accounting;

use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Database\Factories\Accounting\FinancialYearFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialYear extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'start_date',
        'end_date',
        'status',
        'is_locked',
        'notes',
        'locked_at',
        'locked_by',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'locked_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    protected static function newFactory(): FinancialYearFactory
    {
        return FinancialYearFactory::new();
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function openingBalances(): HasMany
    {
        return $this->hasMany(OpeningBalance::class);
    }

    public function closingEntries(): HasMany
    {
        return $this->hasMany(ClosingEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->is_locked;
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function canBeModified(): bool
    {
        return $this->isActive() && ! $this->is_locked;
    }

    public function canBeClosed(): bool
    {
        return $this->status === 'active' && ! $this->isClosed();
    }

    public function lock(): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => auth()->id(),
        ]);
    }

    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => auth()->id(),
            'is_locked' => true,
        ]);
    }

    public function getDurationAttribute(): string
    {
        return $this->start_date->format('M d, Y').' - '.$this->end_date->format('M d, Y');
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->isClosed()) {
            return 0;
        }

        return max(0, $this->end_date->diffInDays(now()));
    }
}

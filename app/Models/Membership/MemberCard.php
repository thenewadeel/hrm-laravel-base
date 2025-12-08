<?php

namespace App\Models\Membership;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberCard extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'member_id',
        'card_number',
        'card_type',
        'template',
        'status',
        'issue_date',
        'expiry_date',
        'qr_code_path',
        'barcode_path',
        'design_settings',
        'notes',
        'print_count',
        'last_printed_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'design_settings' => 'array',
            'last_printed_at' => 'datetime',
            'card_type' => 'string',
            'template' => 'string',
            'status' => 'string',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
               (! $this->expiry_date || $this->expiry_date->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiry_date &&
               $this->expiry_date->greaterThan(now()) &&
               $this->expiry_date->lessThanOrEqualTo(now()->addDays(30));
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('card_type', $type);
    }

    public function scopeByTemplate($query, string $template)
    {
        return $query->where('template', $template);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function incrementPrintCount(): void
    {
        $this->increment('print_count');
        $this->update(['last_printed_at' => now()]);
    }

    public function getCardTypeLabelAttribute(): string
    {
        return match ($this->card_type) {
            'standard' => 'Standard',
            'premium' => 'Premium',
            'family' => 'Family',
            'corporate' => 'Corporate',
            default => ucfirst($this->card_type),
        };
    }

    public function getTemplateLabelAttribute(): string
    {
        return match ($this->template) {
            'modern' => 'Modern',
            'classic' => 'Classic',
            'corporate' => 'Corporate',
            'family' => 'Family',
            'minimal' => 'Minimal',
            default => ucfirst($this->template),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'expired' => 'Expired',
            'lost' => 'Lost',
            'damaged' => 'Damaged',
            'reprinted' => 'Reprinted',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'expired' => 'red',
            'lost' => 'orange',
            'damaged' => 'yellow',
            'reprinted' => 'blue',
            default => 'gray',
        };
    }
}

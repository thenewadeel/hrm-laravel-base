<?php

namespace App\Models\Membership;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'plan_type',
        'billing_frequency',
        'amount',
        'family_members_included',
        'additional_family_member_fee',
        'benefits',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'additional_family_member_fee' => 'decimal:2',
            'benefits' => 'array',
            'is_active' => 'boolean',
            'plan_type' => 'string',
            'billing_frequency' => 'string',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class, 'subscription_plan_id');
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('status', 'active');
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedAdditionalFeeAttribute(): string
    {
        return number_format($this->additional_family_member_fee, 2);
    }

    public function calculateTotalCost(int $familyMembers = 0): float
    {
        $additionalMembers = max(0, $familyMembers - $this->family_members_included);
        return $this->amount + ($additionalMembers * $this->additional_family_member_fee);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('plan_type', $type);
    }

    public function scopeByFrequency($query, string $frequency)
    {
        return $query->where('billing_frequency', $frequency);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
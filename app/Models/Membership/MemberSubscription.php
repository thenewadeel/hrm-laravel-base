<?php

namespace App\Models\Membership;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberSubscription extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'member_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'status',
        'total_amount',
        'paid_amount',
        'auto_renew',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'auto_renew' => 'boolean',
            'status' => 'string',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', 'active')
            ->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>', now());
    }

    public function scopeByMember($query, int $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeByPlan($query, int $planId)
    {
        return $query->where('subscription_plan_id', $planId);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}

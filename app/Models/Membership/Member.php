<?php

namespace App\Models\Membership;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'membership_number',
        'title',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'barcode_number',
        'photo_path',
        'status',
        'join_date',
        'expiry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'join_date' => 'date',
            'expiry_date' => 'date',
            'status' => 'string',
            'gender' => 'string',
        ];
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'primary_member_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }

    public function activeSubscription(): HasMany
    {
        return $this->subscriptions()->where('status', 'active');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(MemberFee::class);
    }

    public function unpaidFees(): HasMany
    {
        return $this->fees()->where('status', 'pending');
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->title} {$this->first_name} {$this->last_name}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->expiry_date || $this->expiry_date->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
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

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('membership_number', 'like', "%{$search}%");
        });
    }
}
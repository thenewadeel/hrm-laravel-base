<?php

namespace App\Models\Membership;

use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyMember extends Model
{
    use HasFactory, BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'primary_member_id',
        'relationship',
        'title',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'barcode_number',
        'photo_path',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'status' => 'string',
            'gender' => 'string',
        ];
    }

    public function primaryMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'primary_member_id');
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
               $this->primaryMember?->isActive();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->whereHas('primaryMember', function ($q) {
                        $q->active();
                    });
    }

    public function scopeByRelationship($query, string $relationship)
    {
        return $query->where('relationship', $relationship);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('relationship', 'like', "%{$search}%")
              ->orWhere('barcode_number', 'like', "%{$search}%");
        });
    }
}
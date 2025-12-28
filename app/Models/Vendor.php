<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
        'address',
        'tax_number',
        'vendor_type',
        'payment_terms',
        'performance_rating',
        'reliability_score',
        'average_delivery_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'performance_rating' => 'decimal:2',
            'reliability_score' => 'decimal:2',
            'average_delivery_time' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(\App\Models\Accounting\JournalEntry::class);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return $this->journalEntries()
            ->where('status', 'posted')
            ->where('voucher_type', 'PURCHASE')
            ->sum('total_amount');
    }
}

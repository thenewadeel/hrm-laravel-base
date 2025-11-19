<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
        'address',
        'tax_number',
        'customer_type',
        'credit_limit',
        'status',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function journalEntries(): HasMany
    {
        return $this->hasMany(\App\Models\Accounting\JournalEntry::class);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return $this->journalEntries()
            ->where('status', 'posted')
            ->where('voucher_type', 'SALES')
            ->sum('total_amount');
    }
}

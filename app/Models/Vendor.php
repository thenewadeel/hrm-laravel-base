<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
        'address',
        'tax_number',
        'vendor_type',
        'payment_terms',
        'status',
    ];

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

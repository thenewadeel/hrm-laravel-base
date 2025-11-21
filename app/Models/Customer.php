<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToOrganization, HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'customers';

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_id',
        'customer_type',
        'credit_limit',
        'opening_balance',
        'current_balance',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get organization that owns the customer.
     */
    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get invoices for the customer.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get payments for the customer.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get transactions for the customer.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope to get active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get customers by balance.
     */
    public function scopeWithBalance($query, string $operator = '>', float $amount = 0)
    {
        return $query->where('current_balance', $operator, $amount);
    }

    /**
     * Get customer's full address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get customer's display name with balance.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} (Balance: {$this->current_balance})";
    }

    /**
     * Check if customer has overdue payments.
     */
    public function hasOverduePayments(): bool
    {
        return $this->invoices()
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->exists();
    }

    /**
     * Get total outstanding amount.
     */
    public function getOutstandingAmountAttribute(): float
    {
        return $this->invoices()
            ->where('status', '!=', 'paid')
            ->sum('amount') - $this->payments()
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return $this->journalEntries()
            ->where('status', 'posted')
            ->where('voucher_type', 'SALES')
            ->sum('total_amount');
    }
}

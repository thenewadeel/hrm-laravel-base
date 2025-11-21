<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Vendor;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'customer_id',
        'vendor_id',
        'invoice_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get organization that owns the payment.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get customer for the payment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get vendor for the payment.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get invoice for the payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get user who created the payment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get user who last updated the payment.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get received payments.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope to get pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get payments by date range.
     */
    public function scopeByDateRange($query, string $fromDate, string $toDate)
    {
        return $query->whereBetween('payment_date', [$fromDate, $toDate]);
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'received' => 'Received',
            'processed' => 'Processed',
            'failed' => 'Failed',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get formatted payment amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, '.', ',');
    }

    /**
     * Check if payment is fully applied to invoice.
     */
    public function isFullyApplied(): bool
    {
        $totalInvoiceAmount = $this->invoice?->total_amount ?? 0;
        $totalPayments = $this->invoice->payments()->sum('amount');
        
        return $totalPayments >= $totalInvoiceAmount;
    }
}